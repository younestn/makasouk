<template>
  <div class="public-shell">
    <PublicHeader />

    <main>
      <RouterView />
    </main>

    <PublicFooter />
  </div>
</template>

<script setup>
import { RouterView, useRoute } from 'vue-router';
import { watch } from 'vue';
import PublicHeader from '@/public/components/PublicHeader.vue';
import PublicFooter from '@/public/components/PublicFooter.vue';
import { useUiPreferencesStore } from '@/stores/uiPreferences';
import { applyPublicMeta } from '@/public/seo';

const route = useRoute();
const uiPreferencesStore = useUiPreferencesStore();

watch(
  () => [route.fullPath, uiPreferencesStore.locale],
  () => {
    applyPublicMeta(route, uiPreferencesStore.locale);
  },
  { immediate: true },
);
</script>
