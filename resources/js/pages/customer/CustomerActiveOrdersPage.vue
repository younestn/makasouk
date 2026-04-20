<template>
  <section class="stack">
    <UiSectionHeader
      :title="t('common.active_orders')"
      description="Realtime-enabled list of your currently active customer orders."
    />

    <LoadingState v-if="loading" label="Loading active orders..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="reloadCurrentPage" />
    <EmptyState v-else-if="orders.length === 0" message="No active orders yet.">
      <template #actions>
        <RouterLink class="btn btn-primary" :to="{ name: 'customerCreateOrder' }">{{ t('common.create_order') }}</RouterLink>
      </template>
    </EmptyState>

    <template v-else>
      <div class="stack">
        <OrderCard
          v-for="order in orders"
          :key="order.id"
          :order="order"
          details-route-name="customerOrderDetails"
        />
      </div>

      <UiPagination :pagination="pagination" @page-change="load" />
    </template>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiPagination from '@/components/ui/UiPagination.vue';
import OrderCard from '@/components/orders/OrderCard.vue';
import { fetchActiveOrders } from '@/services/customerOrderService';
import { getErrorMessage } from '@/services/errorMessage';
import { useRealtimeStore } from '@/stores/realtime';
import { defaultPagination, normalizePagination } from '@/utils/pagination';
import { useI18n } from '@/composables/useI18n';

const route = useRoute();
const router = useRouter();
const realtimeStore = useRealtimeStore();
const { t } = useI18n();

const loading = ref(false);
const error = ref('');
const orders = ref([]);
const pagination = reactive(defaultPagination({ perPage: 10 }));

function queryPage() {
  const page = Number(route.query.page || 1);

  if (!Number.isInteger(page) || page < 1) {
    return 1;
  }

  return page;
}

function syncQuery(page) {
  router.replace({
    query: {
      ...route.query,
      page: page > 1 ? String(page) : undefined,
    },
  });
}

async function load(page = pagination.currentPage) {
  loading.value = true;
  error.value = '';

  const safePage = Math.max(1, Number(page || 1));

  try {
    const response = await fetchActiveOrders({
      page: safePage,
      per_page: pagination.perPage,
    });

    orders.value = response.data || [];

    Object.assign(
      pagination,
      normalizePagination(response, {
        ...pagination,
        currentPage: safePage,
      }),
    );

    syncQuery(pagination.currentPage);
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load active orders.');
  } finally {
    loading.value = false;
  }
}

function reloadCurrentPage() {
  load(pagination.currentPage);
}

watch(
  () => realtimeStore.lastEvent,
  (event) => {
    if (!event) {
      return;
    }

    load(pagination.currentPage);
  },
);

onMounted(() => {
  load(queryPage());
});
</script>
