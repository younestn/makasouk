<template>
  <footer class="public-footer">
    <div class="container public-footer-inner">
      <div class="stack" style="gap: 0.35rem;">
        <strong>{{ t('public.brand') }}</strong>
        <p class="small">{{ t('public.footer_tagline') }}</p>
      </div>

      <div class="row" style="justify-content: flex-end;">
        <a
          v-for="page in footerPages"
          :key="page.url"
          class="public-footer-link"
          :href="page.url"
        >
          {{ page.title }}
        </a>
        <a class="public-footer-link" href="/app/login">{{ t('public.client_app') }}</a>
        <a class="public-footer-link" href="/admin-panel">{{ t('public.admin_panel') }}</a>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { apiClient } from '@/services/http';
import { useI18n } from '@/composables/useI18n';

const { locale, t } = useI18n();
const footerPages = ref([]);

onMounted(loadFooterPages);

watch(locale, () => {
  loadFooterPages();
});

async function loadFooterPages() {
  try {
    const { data } = await apiClient.get('/public/footer-pages');
    footerPages.value = data.data || [];
  } catch {
    footerPages.value = [];
  }
}
</script>
