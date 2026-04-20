<template>
  <section class="stack">
    <UiSectionHeader
      :title="t('common.availability')"
      description="Control whether you can receive nearby order offers."
    />

    <div class="ui-card stack">
      <div class="grid grid-2">
        <UiStatBlock label="Current Status" :value="availability.status || '-'" tone="info" />
        <UiStatBlock label="Active Orders" :value="availability.active_orders_count ?? 0" />
      </div>

      <div v-if="error" class="alert alert-danger">{{ error }}</div>

      <button class="btn btn-primary" :disabled="loading" @click="toggle">
        {{ loading ? 'Updating...' : 'Toggle Availability' }}
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
    error.value = getErrorMessage(err, 'Failed to load availability.');
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
    error.value = getErrorMessage(err, 'Failed to toggle availability.');
    errorToast(error.value);
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
