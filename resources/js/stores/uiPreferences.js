import { defineStore } from 'pinia';
import { getDirection, normalizeLocale, readStoredLocale, writeStoredLocale } from '@/i18n';

export const useUiPreferencesStore = defineStore('uiPreferences', {
  state: () => ({
    locale: readStoredLocale(),
  }),

  getters: {
    direction: (state) => getDirection(state.locale),
    isRtl: (state) => getDirection(state.locale) === 'rtl',
  },

  actions: {
    initialize() {
      this.locale = normalizeLocale(this.locale);
      this.applyDocumentPreferences();
    },

    setLocale(locale) {
      this.locale = normalizeLocale(locale);
      writeStoredLocale(this.locale);
      this.applyDocumentPreferences();
    },

    toggleLocale() {
      this.setLocale(this.locale === 'en' ? 'ar' : 'en');
    },

    applyDocumentPreferences() {
      if (typeof document === 'undefined') {
        return;
      }

      document.documentElement.lang = this.locale;
      document.documentElement.dir = this.direction;
    },
  },
});
