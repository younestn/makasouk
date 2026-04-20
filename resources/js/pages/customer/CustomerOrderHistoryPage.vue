<template>
  <section class="stack">
    <UiSectionHeader
      title="Order History"
      description="Completed and cancelled customer orders."
    />

    <LoadingState v-if="loading" label="Loading order history..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />
    <EmptyState v-else-if="orders.length === 0" message="No history orders yet." />

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
import { onMounted, ref } from 'vue';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import OrderCard from '@/components/orders/OrderCard.vue';
import { fetchOrderHistory } from '@/services/customerOrderService';
import { getErrorMessage } from '@/services/errorMessage';

const loading = ref(false);
const error = ref('');
const orders = ref([]);

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchOrderHistory();
    orders.value = response.data || [];
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load order history.');
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
