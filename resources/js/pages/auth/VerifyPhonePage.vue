<template>
  <AuthShell
    tone="tailor"
    :badge="t('auth.verify_phone_badge')"
    :brand-title="t('auth.verify_phone_brand_title')"
    :brand-subtitle="t('auth.verify_phone_brand_subtitle')"
    :points="[
      t('auth.verify_phone_point_one'),
      t('auth.verify_phone_point_two'),
      t('auth.verify_phone_point_three'),
    ]"
    :trust-note="t('auth.trust_note')"
    :form-title="t('auth.verify_phone_title')"
    :form-subtitle="t('auth.verify_phone_subtitle')"
  >
    <div class="alert alert-info">
      {{ t('auth.verify_phone_notice', { phone: authStore.user?.phone || '-' }) }}
    </div>

    <div v-if="error" class="alert alert-danger" style="margin-top: 0.9rem;">{{ error }}</div>

    <form class="stack" style="margin-top: 0.9rem;" @submit.prevent="submit">
      <div>
        <label class="label" for="verify-code">{{ t('auth.verification_code') }}</label>
        <input
          id="verify-code"
          v-model="form.code"
          type="text"
          class="input"
          inputmode="numeric"
          autocomplete="one-time-code"
          maxlength="6"
          required
        />
        <p v-if="fieldError('code')" class="field-error">{{ fieldError('code') }}</p>
      </div>

      <button class="btn btn-primary" type="submit" :disabled="loading">
        {{ loading ? t('auth.verifying_phone') : t('auth.verify_phone_button') }}
      </button>
    </form>

    <div class="row" style="justify-content: space-between; margin-top: 0.65rem;">
      <p class="small" style="margin: 0;">
        {{ resendHint }}
      </p>
      <button class="btn" type="button" :disabled="resending" @click="resendCode">
        {{ resending ? t('auth.resending_code') : t('auth.resend_code') }}
      </button>
    </div>
  </AuthShell>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import AuthShell from '@/components/auth/AuthShell.vue';
import { useAuthStore } from '@/stores/auth';
import { useI18n } from '@/composables/useI18n';
import { useToast } from '@/composables/useToast';
import { getErrorMessage } from '@/services/errorMessage';

const router = useRouter();
const authStore = useAuthStore();
const { t } = useI18n();
const { successToast, errorToast } = useToast();

const loading = ref(false);
const resending = ref(false);
const error = ref('');
const validationErrors = ref({});

const form = reactive({
  code: '',
});

const resendHint = computed(() => {
  const seconds = Number(authStore.authMeta?.phone_verification?.retry_after_seconds || 0);

  if (!Number.isFinite(seconds) || seconds <= 0) {
    return t('auth.verify_phone_resend_hint_default');
  }

  return t('auth.verify_phone_resend_hint_wait', { seconds });
});

function fieldError(field) {
  if (!validationErrors.value || !validationErrors.value[field]) {
    return '';
  }

  return String(validationErrors.value[field][0] || '');
}

async function submit() {
  loading.value = true;
  error.value = '';
  validationErrors.value = {};

  try {
    await authStore.verifyPhoneCode({ code: form.code });
    successToast(t('auth.phone_verified_success'));
    router.push({ name: 'tailorDashboard' });
  } catch (err) {
    validationErrors.value = err?.errors || {};
    error.value = getErrorMessage(err, t('auth.verify_phone_failed'));
    errorToast(error.value);
  } finally {
    loading.value = false;
  }
}

async function resendCode() {
  resending.value = true;
  error.value = '';
  validationErrors.value = {};

  try {
    await authStore.sendPhoneVerificationCode();
    successToast(t('auth.phone_code_resent'));
  } catch (err) {
    validationErrors.value = err?.errors || {};
    error.value = getErrorMessage(err, t('auth.resend_code_failed'));
    errorToast(error.value);
  } finally {
    resending.value = false;
  }
}
</script>
