<template>
  <section class="page-section">
    <div class="container stack">
      <UiSectionHeader
        :title="t('public.how_it_works_title')"
        :description="t('public.how_it_works_description')"
      />

      <div class="grid grid-3">
        <UiCard class="stack">
          <span class="badge badge-info">{{ t('public.how_it_works_step_1_badge') }}</span>
          <h3 class="title">{{ t('public.how_it_works_step_1_title') }}</h3>
          <p class="subtitle">{{ t('public.how_it_works_step_1_description') }}</p>
        </UiCard>

        <UiCard class="stack">
          <span class="badge badge-warning">{{ t('public.how_it_works_step_2_badge') }}</span>
          <h3 class="title">{{ t('public.how_it_works_step_2_title') }}</h3>
          <p class="subtitle">{{ t('public.how_it_works_step_2_description') }}</p>
        </UiCard>

        <UiCard class="stack">
          <span class="badge badge-success">{{ t('public.how_it_works_step_3_badge') }}</span>
          <h3 class="title">{{ t('public.how_it_works_step_3_title') }}</h3>
          <p class="subtitle">{{ t('public.how_it_works_step_3_description') }}</p>
        </UiCard>
      </div>

      <UiAlert :title="t('public.how_it_works_realtime_title')" variant="info">
        {{ t('public.how_it_works_realtime_description') }}
      </UiAlert>

      <div class="actions">
        <UiButton :to="{ name: 'publicForCustomers' }" variant="primary">{{ t('public.how_it_works_cta_customers') }}</UiButton>
        <UiButton :to="{ name: 'publicForTailors' }" variant="secondary">{{ t('public.how_it_works_cta_tailors') }}</UiButton>
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
