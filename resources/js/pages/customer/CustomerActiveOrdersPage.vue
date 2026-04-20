<template>
  <section class="stack">
    <UiSectionHeader
      title="Active Orders"
      description="Realtime-enabled list of your currently active customer orders."
    />

    <LoadingState v-if="loading" label="Loading active orders..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />
    <EmptyState v-else-if="orders.length === 0" message="No active orders yet.">
      <template #actions>
        <RouterLink class="btn btn-primary" :to="{ name: 'customerCreateOrder' }">Create your first order</RouterLink>
      </template>
    </EmptyState>

    <div v-else class="stack">
      <OrderCard
        v-for="order in orders"
        :key="order.id"
        :order="order"
        details-route-name="customerOrderDetails"
      />
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import OrderCard from '@/components/orders/OrderCard.vue';
import { fetchActiveOrders } from '@/services/customerOrderService';
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
    const response = await fetchActiveOrders();
    orders.value = response.data || [];
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load active orders.');
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
