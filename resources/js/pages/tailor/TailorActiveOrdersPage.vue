<template>
  <section class="stack">
    <div class="card stack">
      <h1 class="title">Tailor Active Orders</h1>
      <p class="subtitle">Orders currently assigned to you.</p>
    </div>

    <LoadingState v-if="loading" label="Loading active tailor orders..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />
    <EmptyState v-else-if="orders.length === 0" message="No active orders assigned to you." />

    <div v-else class="stack">
      <OrderCard
        v-for="order in orders"
        :key="order.id"
        :order="order"
        details-route-name="tailorOrderDetails"
      />
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import OrderCard from '@/components/orders/OrderCard.vue';
import { fetchTailorActiveOrders } from '@/services/tailorService';
import { getErrorMessage } from '@/services/errorMessage';
import { useRealtimeStore } from '@/stores/realtime';

const realtimeStore = useRealtimeStore();

const loading = ref(false);
const error = ref('');
const orders = ref([]);

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchTailorActiveOrders();
    orders.value = response.data || [];
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load tailor active orders.');
  } finally {
    loading.value = false;
  }
}

watch(
  () => realtimeStore.lastEvent,
  (event) => {
    if (!event) {
      return;
    }

    load();
  },
);

onMounted(load);
</script>
