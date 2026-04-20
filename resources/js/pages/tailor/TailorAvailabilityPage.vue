<template>
  <section class="stack">
    <div class="card stack">
      <h1 class="title">Availability</h1>
      <p class="subtitle">Control whether you can receive nearby orders.</p>

      <div class="row" style="justify-content: space-between;">
        <div>
          <p class="label">Current Status</p>
          <h2 class="title">{{ availability.status || '-' }}</h2>
        </div>
        <div>
          <p class="label">Active Orders</p>
          <h2 class="title">{{ availability.active_orders_count ?? 0 }}</h2>
        </div>
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
