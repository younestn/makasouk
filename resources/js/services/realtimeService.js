import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

let echoInstance = null;
let privateOrderChannel = null;

const ORDER_EVENTS = [
  'order.created',
  'order.accepted',
  'order.status_updated',
  'order.cancelled_by_customer',
  'order.cancelled_by_tailor',
];

function getChannelKeyForUser(user) {
  if (user.role === 'customer') {
    return `customer.${user.id}`;
  }

  if (user.role === 'tailor') {
    return `tailor.${user.id}`;
  }

  return null;
}

export function disconnectRealtime() {
  if (privateOrderChannel) {
    privateOrderChannel.stopListening();
    privateOrderChannel = null;
  }

  if (echoInstance) {
    echoInstance.disconnect();
    echoInstance = null;
  }
}

export function connectRealtime(user, token, handlers = {}) {
  if (!user || !token || user.role === 'admin') {
    return () => {};
  }

  disconnectRealtime();

  window.Pusher = Pusher;

  const wsScheme = import.meta.env.VITE_REVERB_SCHEME || 'http';
  const wsPort = Number(import.meta.env.VITE_REVERB_PORT || 8080);
  const wsHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
  const authEndpoint = import.meta.env.VITE_BROADCAST_AUTH_ENDPOINT || '/broadcasting/auth';
  const key = import.meta.env.VITE_REVERB_KEY;

  if (!key) {
    handlers.onError?.('VITE_REVERB_KEY is missing, realtime is disabled.');
    return () => {};
  }

  echoInstance = new Echo({
    broadcaster: 'reverb',
    key,
    wsHost,
    wsPort,
    wssPort: wsPort,
    forceTLS: wsScheme === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint,
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    },
  });

  const channelKey = getChannelKeyForUser(user);

  if (!channelKey) {
    return () => disconnectRealtime();
  }

  privateOrderChannel = echoInstance.private(channelKey);

  ORDER_EVENTS.forEach((eventName) => {
    privateOrderChannel.listen(`.${eventName}`, (payload) => {
      handlers.onOrderEvent?.(payload);
    });
  });

  const connection = echoInstance.connector?.pusher?.connection;

  connection?.bind('connected', () => {
    handlers.onConnected?.();
  });

  connection?.bind('disconnected', () => {
    handlers.onDisconnected?.();
  });

  connection?.bind('error', () => {
    handlers.onError?.('Unable to connect to realtime server.');
  });

  return () => disconnectRealtime();
}
