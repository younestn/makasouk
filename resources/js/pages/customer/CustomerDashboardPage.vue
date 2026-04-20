<template>
  <section class="stack">
    <UiSectionHeader
      title="Customer Dashboard"
      description="Quick operational snapshot for your account and order activity."
    />

    <LoadingState v-if="loading" label="Loading customer dashboard..." hint="Fetching active and history counts." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <div v-else class="grid grid-2">
      <UiStatBlock label="Active Orders" :value="stats.active" hint="Realtime-enabled" tone="info" />
      <UiStatBlock label="History Orders" :value="stats.history" hint="Completed and cancelled" />
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
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
