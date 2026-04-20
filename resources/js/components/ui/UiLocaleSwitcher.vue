<template>
  <div class="locale-switcher" role="group" :aria-label="t('locale.switcher')">
    <button
      class="locale-switcher-btn"
      :class="{ 'locale-switcher-btn--active': locale === 'en' }"
      type="button"
      @click="setLocale('en')"
    >
      EN
    </button>
    <button
      class="locale-switcher-btn"
      :class="{ 'locale-switcher-btn--active': locale === 'ar' }"
      type="button"
      @click="setLocale('ar')"
    >
      عربي
    </button>
  </div>
</template>

<script setup>
import { useI18n } from '@/composables/useI18n';

const { t, locale, setLocale } = useI18n();
</script>
