import { computed } from 'vue';
import { useUiPreferencesStore } from '@/stores/uiPreferences';
import { translate } from '@/i18n';

export function useI18n() {
  const uiPreferencesStore = useUiPreferencesStore();

  const t = (key, params = {}) => translate(uiPreferencesStore.locale, key, params);

  return {
    t,
    locale: computed(() => uiPreferencesStore.locale),
    direction: computed(() => uiPreferencesStore.direction),
    isRtl: computed(() => uiPreferencesStore.isRtl),
    setLocale: uiPreferencesStore.setLocale,
    toggleLocale: uiPreferencesStore.toggleLocale,
  };
}
