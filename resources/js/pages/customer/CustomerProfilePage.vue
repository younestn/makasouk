<template>
  <section class="stack">
    <UiSectionHeader
      :title="t('customers.profile_title')"
      :description="t('customers.profile_description')"
    />

    <LoadingState v-if="loading" :label="t('customers.loading_profile')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <form v-else class="customer-profile-form ui-card stack" @submit.prevent="submit">
      <div class="customer-profile-form__hero">
        <div class="customer-profile-form__avatar">
          <img v-if="profile.avatar_url" :src="profile.avatar_url" :alt="profile.name || ''">
          <span v-else>{{ initials }}</span>
        </div>
        <div class="stack" style="gap: 0.2rem;">
          <strong>{{ profile.name }}</strong>
          <p class="small">{{ profile.email }}</p>
          <p class="small">{{ profile.phone || t('common.not_available') }}</p>
        </div>
      </div>

      <div class="grid grid-2">
        <UiFormField :label="t('auth.full_name')" field-id="profile-name">
          <input id="profile-name" v-model="form.name" class="input" type="text" required>
          <p v-if="fieldError('name')" class="field-error">{{ fieldError('name') }}</p>
        </UiFormField>

        <UiFormField :label="t('auth.phone_number')" field-id="profile-phone">
          <input id="profile-phone" v-model="form.phone" class="input" type="tel">
          <p v-if="fieldError('phone')" class="field-error">{{ fieldError('phone') }}</p>
        </UiFormField>

        <UiFormField :label="t('auth.email')" field-id="profile-email">
          <input id="profile-email" :value="profile.email" class="input" type="email" disabled>
        </UiFormField>

        <UiFormField :label="t('customers.avatar_label')" field-id="profile-avatar">
          <input id="profile-avatar" class="input" type="file" accept=".jpg,.jpeg,.png,.webp" @change="onAvatarChange">
          <p class="small">{{ t('customers.avatar_hint') }}</p>
          <p v-if="fieldError('avatar')" class="field-error">{{ fieldError('avatar') }}</p>
        </UiFormField>
      </div>

      <div class="actions">
        <button class="btn btn-primary" type="submit" :disabled="submitting">
          {{ submitting ? t('common.saving') : t('customers.save_profile_action') }}
        </button>
      </div>
    </form>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import { fetchCustomerProfile, updateCustomerProfile } from '@/services/customerProfileService';
import { getErrorMessage } from '@/services/errorMessage';
import { useI18n } from '@/composables/useI18n';
import { useToast } from '@/composables/useToast';

const authStore = useAuthStore();
const { t } = useI18n();
const { successToast, errorToast } = useToast();

const loading = ref(false);
const submitting = ref(false);
const error = ref('');
const validationErrors = ref({});
const profile = reactive({
  name: '',
  email: '',
  phone: '',
  avatar_url: '',
});
const form = reactive({
  name: '',
  phone: '',
  avatar: null,
});

const initials = computed(() => profile.name
  ?.split(' ')
  .filter(Boolean)
  .slice(0, 2)
  .map((segment) => segment.charAt(0).toUpperCase())
  .join('') || 'MK');

function fieldError(field) {
  return validationErrors.value?.[field]?.[0] || '';
}

function onAvatarChange(event) {
  form.avatar = event.target.files?.[0] || null;
}

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchCustomerProfile();
    Object.assign(profile, response.data || {});
    form.name = response.data?.name || '';
    form.phone = response.data?.phone || '';
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.customer_profile_load_failed'));
  } finally {
    loading.value = false;
  }
}

async function submit() {
  submitting.value = true;
  validationErrors.value = {};

  try {
    const payload = new FormData();
    payload.append('name', form.name);
    payload.append('phone', form.phone || '');
    if (form.avatar) {
      payload.append('avatar', form.avatar);
    }

    const response = await updateCustomerProfile(payload);
    Object.assign(profile, response.data || {});
    authStore.user = response.data;
    form.avatar = null;
    successToast(response.message || t('notifications.profile_updated'));
  } catch (err) {
    validationErrors.value = err?.errors || {};
    errorToast(getErrorMessage(err, t('messages.customer_profile_update_failed')));
  } finally {
    submitting.value = false;
  }
}

onMounted(load);
</script>
