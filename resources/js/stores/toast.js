import { defineStore } from 'pinia';

let seed = 0;
const timers = new Map();

function makeToast(payload) {
  seed += 1;

  return {
    id: seed,
    title: payload.title || '',
    message: payload.message || '',
    variant: payload.variant || 'info',
    duration: Number(payload.duration ?? 4500),
    persistent: Boolean(payload.persistent),
    createdAt: Date.now(),
  };
}

export const useToastStore = defineStore('toast', {
  state: () => ({
    items: [],
  }),

  actions: {
    push(payload) {
      const toast = makeToast(payload || {});

      if (!toast.message) {
        return null;
      }

      this.items.push(toast);

      if (!toast.persistent && toast.duration > 0) {
        const timeout = setTimeout(() => {
          this.remove(toast.id);
        }, toast.duration);

        timers.set(toast.id, timeout);
      }

      return toast.id;
    },

    success(message, options = {}) {
      return this.push({ ...options, message, variant: 'success' });
    },

    error(message, options = {}) {
      return this.push({ ...options, message, variant: 'danger', duration: options.duration ?? 6500 });
    },

    warning(message, options = {}) {
      return this.push({ ...options, message, variant: 'warning' });
    },

    info(message, options = {}) {
      return this.push({ ...options, message, variant: 'info' });
    },

    remove(id) {
      const numericId = Number(id);
      this.items = this.items.filter((item) => item.id !== numericId);

      const timer = timers.get(numericId);

      if (timer) {
        clearTimeout(timer);
        timers.delete(numericId);
      }
    },

    clear() {
      this.items.forEach((item) => {
        const timer = timers.get(item.id);

        if (timer) {
          clearTimeout(timer);
          timers.delete(item.id);
        }
      });

      this.items = [];
    },
  },
});
