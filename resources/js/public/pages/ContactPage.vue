<template>
  <section class="page-section">
    <div class="container stack" style="max-width: 860px;">
      <UiSectionHeader
        :title="t('public.contact_title')"
        :description="t('public.contact_description')"
      />

      <UiCard class="stack">
        <p class="subtitle">
          {{ t('public.contact_intro') }}
        </p>

        <div class="grid grid-2">
          <div>
            <p class="label">{{ t('public.contact_support_email_label') }}</p>
            <p>support@makasouk.local</p>
          </div>
          <div>
            <p class="label">{{ t('public.contact_business_hours_label') }}</p>
            <p>{{ t('public.contact_business_hours_value') }}</p>
          </div>
        </div>
      </UiCard>

      <UiCard class="stack">
        <UiFormField :label="t('public.contact_form_name_label')" field-id="supportName">
          <input id="supportName" type="text" class="input" :placeholder="t('public.contact_form_name_placeholder')" disabled />
        </UiFormField>

        <UiFormField :label="t('public.contact_form_message_label')" field-id="supportMessage">
          <textarea
            id="supportMessage"
            class="textarea"
            rows="5"
            :placeholder="t('public.contact_form_message_placeholder')"
            disabled
          ></textarea>
        </UiFormField>

        <UiAlert :title="t('public.contact_next_step_title')" variant="neutral">
          {{ t('public.contact_next_step_description') }}
        </UiAlert>
      </UiCard>

      <div class="actions">
        <UiButton as="a" href="/app/login" variant="primary">{{ t('public.contact_back_to_login') }}</UiButton>
      </div>
    </div>
  </section>
</template>

<script setup>
import UiAlert from '@/components/ui/UiAlert.vue';
import UiButton from '@/components/ui/UiButton.vue';
import UiCard from '@/components/ui/UiCard.vue';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
</script>
