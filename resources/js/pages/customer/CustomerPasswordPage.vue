<template>
  <section class="stack">
    <UiSectionHeader
      :title="t('customers.security_title')"
      :description="t('customers.security_description')"
    />

    <form class="ui-card stack" @submit.prevent="submit">
      <UiFormField :label="t('customers.current_password_label')" field-id="current-password">
        <input id="current-password" v-model="form.current_password" class="input" type="password" required>
        <p v-if="fieldError('current_password')" class="field-error">{{ fieldError('current_password') }}</p>
      </UiFormField>

      <UiFormField :label="t('auth.password')" field-id="new-password">
        <input id="new-password" v-model="form.password" class="input" type="password" required>
        <p v-if="fieldError('password')" class="field-error">{{ fieldError('password') }}</p>
      </UiFormField>

      <UiFormField :label="t('auth.confirm_password')" field-id="confirm-password">
        <input id="confirm-password" v-model="form.password_confirmation" class="input" type="password" required>
      </UiFormField>

      <div class="actions">
        <button class="btn btn-primary" type="submit" :disabled="submitting">
          {{ submitting ? t('common.saving') : t('customers.change_password_action') }}
        </button>
      </div>
    </form>
  </section>
</template>

<script setup>
import { reactive, ref } from 'vue';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import { updateCustomerPassword } from '@/services/customerProfileService';
import { getErrorMessage } from '@/services/errorMessage';
import { useI18n } from '@/composables/useI18n';
import { useToast } from '@/composables/useToast';

const { t } = useI18n();
const { successToast, errorToast } = useToast();
const submitting = ref(false);
const validationErrors = ref({});
const form = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});

function fieldError(field) {
  return validationErrors.value?.[field]?.[0] || '';
}

async function submit() {
  submitting.value = true;
  validationErrors.value = {};

  try {
    const response = await updateCustomerPassword({ ...form });
    successToast(response.message || t('notifications.password_updated'));
    form.current_password = '';
    form.password = '';
    form.password_confirmation = '';
  } catch (err) {
    validationErrors.value = err?.errors || {};
    errorToast(getErrorMessage(err, t('messages.customer_password_update_failed')));
  } finally {
    submitting.value = false;
  }
}
</script>
