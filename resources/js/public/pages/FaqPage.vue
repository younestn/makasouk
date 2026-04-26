<template>
  <section class="page-section">
    <div class="container stack">
      <UiSectionHeader
        :title="t('public.faq_title')"
        :description="t('public.faq_description')"
      />

      <div class="stack">
        <UiCard class="stack">
          <h3 class="title">{{ t('public.faq_q1') }}</h3>
          <p class="subtitle">{{ t('public.faq_a1') }}</p>
        </UiCard>

        <UiCard class="stack">
          <h3 class="title">{{ t('public.faq_q2') }}</h3>
          <p class="subtitle">{{ t('public.faq_a2') }}</p>
        </UiCard>

        <UiCard class="stack">
          <h3 class="title">{{ t('public.faq_q3') }}</h3>
          <p class="subtitle">{{ t('public.faq_a3') }}</p>
        </UiCard>

        <UiCard class="stack">
          <h3 class="title">{{ t('public.faq_q4') }}</h3>
          <p class="subtitle">{{ t('public.faq_a4') }}</p>
        </UiCard>
      </div>

      <div class="actions">
        <UiButton as="a" href="/app/login" variant="primary">{{ t('public.faq_cta_open_app') }}</UiButton>
        <UiButton :to="{ name: 'publicContact' }" variant="ghost">{{ t('public.faq_cta_contact') }}</UiButton>
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
