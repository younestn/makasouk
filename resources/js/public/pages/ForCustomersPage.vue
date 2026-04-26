<template>
  <section class="page-section">
    <div class="container stack">
      <UiSectionHeader
        :title="t('public.for_customers_title')"
        :description="t('public.for_customers_description')"
      />

      <div class="grid grid-2">
        <UiCard class="stack">
          <h3 class="title">{{ t('public.for_customers_capabilities_title') }}</h3>
          <ul class="clean-list">
            <li>{{ t('public.for_customers_capabilities_1') }}</li>
            <li>{{ t('public.for_customers_capabilities_2') }}</li>
            <li>{{ t('public.for_customers_capabilities_3') }}</li>
            <li>{{ t('public.for_customers_capabilities_4') }}</li>
          </ul>
        </UiCard>

        <UiCard class="stack">
          <h3 class="title">{{ t('public.for_customers_why_title') }}</h3>
          <ul class="clean-list">
            <li>{{ t('public.for_customers_why_1') }}</li>
            <li>{{ t('public.for_customers_why_2') }}</li>
            <li>{{ t('public.for_customers_why_3') }}</li>
          </ul>
        </UiCard>
      </div>

      <div class="actions">
        <UiButton as="a" href="/app/login" variant="primary">{{ t('public.for_customers_cta_login') }}</UiButton>
        <UiButton :to="{ name: 'publicFaq' }" variant="ghost">{{ t('public.for_customers_cta_faq') }}</UiButton>
      </div>
    </div>
  </section>
</template>

<script setup>
import UiButton from '@/components/ui/UiButton.vue';
import UiCard from '@/components/ui/UiCard.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
</script>
