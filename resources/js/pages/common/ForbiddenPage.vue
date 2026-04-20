<template>
  <section class="page">
    <div class="ui-card stack">
      <h1 class="title">Forbidden</h1>
      <p class="subtitle">You are signed in, but this route is not available for your role.</p>
      <div class="actions">
        <RouterLink class="btn" :to="{ name: 'root' }">{{ t('common.back') }}</RouterLink>
        <a class="btn" href="/">{{ t('common.public_site') }}</a>
      </div>
    </div>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
</script>
