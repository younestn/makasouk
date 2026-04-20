<template>
  <section class="stack">
    <UiSectionHeader
      title="Availability"
      description="Control whether you can receive nearby order offers."
    />

    <div class="ui-card stack">
      <div class="grid grid-2">
        <UiStatBlock label="Current Status" :value="availability.status || '-'" tone="info" />
        <UiStatBlock label="Active Orders" :value="availability.active_orders_count ?? 0" />
      </div>

      <div v-if="error" class="alert alert-danger">{{ error }}</div>
      <div v-if="message" class="alert alert-info">{{ message }}</div>

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

const loading = ref(false);
const error = ref('');
const message = ref('');

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
  message.value = '';

  try {
    const response = await toggleAvailability();
    availability.status = response.data?.status || availability.status;
    message.value = response.message || 'Availability updated.';
    await load();
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to toggle availability.');
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
