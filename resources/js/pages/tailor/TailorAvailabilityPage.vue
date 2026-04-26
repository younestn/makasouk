<template>
  <section class="stack">
    <UiSectionHeader
      :title="t('common.availability')"
      :description="t('tailors.availability_description')"
    />

    <div class="ui-card stack">
      <div class="grid grid-2">
        <UiStatBlock :label="t('tailors.current_status_label')" :value="tailorStatusLabel(availability.status)" tone="info" />
        <UiStatBlock :label="t('tailors.active_orders_label')" :value="availability.active_orders_count ?? 0" />
      </div>

      <div v-if="error" class="alert alert-danger">{{ error }}</div>

      <button class="btn btn-primary" :disabled="loading" @click="toggle">
        {{ loading ? t('tailors.updating_availability') : t('tailors.toggle_availability_action') }}
      </button>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
import { fetchAvailability, toggleAvailability } from '@/services/tailorService';
import { getErrorMessage } from '@/services/errorMessage';
import { useToast } from '@/composables/useToast';
import { useI18n } from '@/composables/useI18n';

const { successToast, errorToast } = useToast();
const { t } = useI18n();

const loading = ref(false);
const error = ref('');

const availability = reactive({
  status: '',
  active_orders_count: 0,
});

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchAvailability();
    Object.assign(availability, response.data || {});
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.tailor_availability_load_failed'));
  } finally {
    loading.value = false;
  }
}

async function toggle() {
  loading.value = true;
  error.value = '';

  try {
    const response = await toggleAvailability();
    availability.status = response.data?.status || availability.status;
    successToast(response.message || t('notifications.availability_updated'));
    await load();
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.tailor_availability_toggle_failed'));
    errorToast(error.value);
  } finally {
    loading.value = false;
  }
}

function tailorStatusLabel(status) {
  if (!status) {
    return t('tailors.status_unknown');
  }

  const key = `tailors.status_${status}`;
  const label = t(key);

  return label === key ? status : label;
}

onMounted(load);
</script>
