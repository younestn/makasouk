<template>
  <footer class="public-footer">
    <div class="container public-footer-inner">
      <div class="stack" style="gap: 0.35rem;">
        <strong>{{ t('public.brand') }}</strong>
        <p class="small">{{ t('public.footer_tagline') }}</p>
      </div>

      <div class="row" style="justify-content: flex-end;">
        <a class="public-footer-link" href="/app/login">{{ t('public.client_app') }}</a>
        <a class="public-footer-link" href="/admin-panel">{{ t('public.admin_panel') }}</a>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
</script>
