import { onBeforeUnmount, watch } from 'vue';
import { connectRealtime, disconnectRealtime } from '@/services/realtimeService';

export function useRealtimeBootstrap(authStore, realtimeStore) {
  let cleanup = null;

  const stopWatcher = watch(
    () => ({ user: authStore.user, token: authStore.token }),
    ({ user, token }) => {
      if (cleanup) {
        cleanup();
        cleanup = null;
      }

      disconnectRealtime();
      realtimeStore.clear();

      if (!user || !token || user.role === 'admin') {
        return;
      }

      cleanup = connectRealtime(user, token, {
        onConnected: () => realtimeStore.setConnected(true),
        onDisconnected: () => realtimeStore.setConnected(false),
        onError: (message) => realtimeStore.setError(message),
        onOrderEvent: (payload) => realtimeStore.ingestOrderEvent(payload, user.role),
      });
    },
    { immediate: true, deep: true },
  );

  onBeforeUnmount(() => {
    stopWatcher();

    if (cleanup) {
      cleanup();
    }

    disconnectRealtime();
  });
}
