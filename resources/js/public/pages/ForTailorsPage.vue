<template>
  <section class="page-section">
    <div class="container stack">
      <UiSectionHeader
        :title="t('public.for_tailors_title')"
        :description="t('public.for_tailors_description')"
      />

      <div class="grid grid-2">
        <UiCard class="stack">
          <h3 class="title">{{ t('public.for_tailors_flow_title') }}</h3>
          <ul class="clean-list">
            <li>{{ t('public.for_tailors_flow_1') }}</li>
            <li>{{ t('public.for_tailors_flow_2') }}</li>
            <li>{{ t('public.for_tailors_flow_3') }}</li>
            <li>{{ t('public.for_tailors_flow_4') }}</li>
          </ul>
        </UiCard>

        <UiCard class="stack">
          <h3 class="title">{{ t('public.for_tailors_reliability_title') }}</h3>
          <ul class="clean-list">
            <li>{{ t('public.for_tailors_reliability_1') }}</li>
            <li>{{ t('public.for_tailors_reliability_2') }}</li>
            <li>{{ t('public.for_tailors_reliability_3') }}</li>
          </ul>
        </UiCard>
      </div>

      <UiAlert :title="t('public.for_tailors_approval_title')" variant="warning">
        {{ t('public.for_tailors_approval_description') }}
      </UiAlert>

      <div class="actions">
        <UiButton as="a" href="/app/login" variant="primary">{{ t('public.for_tailors_cta_login') }}</UiButton>
        <UiButton :to="{ name: 'publicHowItWorks' }" variant="secondary">{{ t('public.for_tailors_cta_lifecycle') }}</UiButton>
      </div>
    </div>
  </section>
</template>

<script setup>
import UiAlert from '@/components/ui/UiAlert.vue';
import UiButton from '@/components/ui/UiButton.vue';
import UiCard from '@/components/ui/UiCard.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
</script>
