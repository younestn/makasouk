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
    },

    removeOffer(orderId) {
      this.tailorOffers = this.tailorOffers.filter((item) => item.order.id !== Number(orderId));
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
        this.tailorOffers.unshift(normalized);
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
