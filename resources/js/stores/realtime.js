import { defineStore } from 'pinia';

function normalizeIncomingOrder(payload) {
  if (!payload || !payload.order || !payload.order.id) {
    return null;
  }

  return {
    event: payload.event,
    occurred_at: payload.occurred_at,
    order: payload.order,
    meta: payload.meta || {},
  };
}

export const useRealtimeStore = defineStore('realtime', {
  state: () => ({
    isConnected: false,
    lastError: null,
    lastEvent: null,
    tailorOffers: [],
    unreadTailorOfferCount: 0,
    soundMuted: typeof window !== 'undefined'
      ? window.localStorage.getItem('makasouk_tailor_sound_muted') === '1'
      : false,
  }),

  actions: {
    setConnected(isConnected) {
      this.isConnected = isConnected;

      if (isConnected) {
        this.lastError = null;
      }
    },

    setError(message) {
      this.lastError = message;
    },

    clear() {
      this.isConnected = false;
      this.lastError = null;
      this.lastEvent = null;
      this.tailorOffers = [];
      this.unreadTailorOfferCount = 0;
    },

    removeOffer(orderId) {
      this.tailorOffers = this.tailorOffers.filter((item) => item.order.id !== Number(orderId));
      this.syncUnreadTailorOfferCount();
    },

    setOffers(offers, unreadCount = null) {
      this.tailorOffers = [...offers].sort((a, b) => new Date(b.order?.created_at || b.order?.timestamps?.created_at || b.occurred_at || 0) - new Date(a.order?.created_at || a.order?.timestamps?.created_at || a.occurred_at || 0));
      this.unreadTailorOfferCount = unreadCount ?? this.tailorOffers.filter((item) => item.order?.tailor_offer?.is_unread || item.is_unread).length;
    },

    markOfferRead(orderId) {
      this.tailorOffers = this.tailorOffers.map((item) => {
        if (Number(item.order.id) !== Number(orderId)) {
          return item;
        }

        return {
          ...item,
          is_unread: false,
          order: {
            ...item.order,
            tailor_offer: item.order.tailor_offer
              ? { ...item.order.tailor_offer, is_unread: false }
              : item.order.tailor_offer,
          },
        };
      });
      this.syncUnreadTailorOfferCount();
    },

    syncUnreadTailorOfferCount() {
      this.unreadTailorOfferCount = this.tailorOffers.filter((item) => item.order?.tailor_offer?.is_unread || item.is_unread).length;
    },

    toggleSoundMuted() {
      this.soundMuted = !this.soundMuted;

      if (typeof window !== 'undefined') {
        window.localStorage.setItem('makasouk_tailor_sound_muted', this.soundMuted ? '1' : '0');
      }
    },

    playIncomingOfferSound() {
      if (this.soundMuted || typeof window === 'undefined') {
        return;
      }

      const AudioContext = window.AudioContext || window.webkitAudioContext;

      if (!AudioContext) {
        return;
      }

      try {
        const context = new AudioContext();
        const oscillator = context.createOscillator();
        const gain = context.createGain();

        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(660, context.currentTime);
        gain.gain.setValueAtTime(0.0001, context.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.04, context.currentTime + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.28);
        oscillator.connect(gain);
        gain.connect(context.destination);
        oscillator.start();
        oscillator.stop(context.currentTime + 0.3);
      } catch {
        // Browser audio policies may block sounds until user interaction.
      }
    },

    ingestOrderEvent(payload, role) {
      const normalized = normalizeIncomingOrder(payload);

      if (!normalized) {
        return;
      }

      this.lastEvent = normalized;

      if (role !== 'tailor') {
        return;
      }

      if (normalized.event === 'order.created') {
        this.removeOffer(normalized.order.id);
        this.tailorOffers.unshift({
          ...normalized,
          is_unread: true,
        });
        this.syncUnreadTailorOfferCount();
        this.playIncomingOfferSound();
        return;
      }

      if (
        normalized.event === 'order.accepted' ||
        normalized.event === 'order.cancelled_by_customer' ||
        normalized.event === 'order.cancelled_by_tailor'
      ) {
        this.removeOffer(normalized.order.id);
      }
    },
  },
});
