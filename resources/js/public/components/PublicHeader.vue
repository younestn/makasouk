<template>
  <header class="public-header">
    <div class="container public-header-inner">
      <RouterLink class="brand" :to="{ name: 'publicHome' }">
        <span class="brand-mark">M</span>
        <span>{{ t('public.brand') }}</span>
      </RouterLink>

      <nav class="public-nav" :aria-label="t('public.navigation_aria')">
        <RouterLink class="public-nav-link" :to="{ name: 'publicHome' }">{{ t('public.nav_home') }}</RouterLink>
        <a class="public-nav-link public-nav-link--shop" href="/shop" aria-label="Shop">
          <span aria-hidden="true">✦</span>
          <span>{{ t('public.nav_shop') }}</span>
        </a>
        <RouterLink class="public-nav-link" :to="{ name: 'publicHowItWorks' }">{{ t('public.nav_how_it_works') }}</RouterLink>
        <RouterLink class="public-nav-link" :to="{ name: 'publicForCustomers' }">{{ t('public.nav_for_customers') }}</RouterLink>
        <RouterLink class="public-nav-link" :to="{ name: 'publicForTailors' }">{{ t('public.nav_for_tailors') }}</RouterLink>
        <RouterLink class="public-nav-link" :to="{ name: 'publicFaq' }">{{ t('public.nav_faq') }}</RouterLink>
        <RouterLink class="public-nav-link" :to="{ name: 'publicContact' }">{{ t('public.nav_contact') }}</RouterLink>
      </nav>

      <div class="actions">
        <UiLocaleSwitcher />
        <UiButton as="a" href="/app/login" variant="ghost">{{ t('common.open_app') }}</UiButton>
        <UiButton as="a" href="/app/login" variant="primary">{{ t('common.sign_in') }}</UiButton>
      </div>
    </div>
  </header>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import UiButton from '@/components/ui/UiButton.vue';
import UiLocaleSwitcher from '@/components/ui/UiLocaleSwitcher.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
</script>
