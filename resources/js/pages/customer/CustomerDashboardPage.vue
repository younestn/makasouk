<template>
  <section class="stack">
    <div class="card stack">
      <h1 class="title">Customer Dashboard</h1>
      <p class="subtitle">Quick operational summary for your account.</p>

      <div class="grid grid-2">
        <div class="card">
          <p class="label">Active Orders</p>
          <h2 class="title">{{ stats.active }}</h2>
        </div>
        <div class="card">
          <p class="label">History Orders</p>
          <h2 class="title">{{ stats.history }}</h2>
        </div>
      </div>
    </div>

    <LoadingState v-if="loading" label="Loading customer dashboard..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import { fetchActiveOrders, fetchOrderHistory } from '@/services/customerOrderService';
import { getErrorMessage } from '@/services/errorMessage';

const loading = ref(false);
const error = ref('');
const stats = reactive({ active: 0, history: 0 });

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const [activeResponse, historyResponse] = await Promise.all([
      fetchActiveOrders({ per_page: 1 }),
      fetchOrderHistory({ per_page: 1 }),
    ]);

    stats.active = activeResponse.meta?.total || 0;
    stats.history = historyResponse.meta?.total || 0;
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load dashboard data.');
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
