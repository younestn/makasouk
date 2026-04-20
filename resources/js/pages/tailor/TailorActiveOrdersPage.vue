<template>
  <section class="stack">
    <UiSectionHeader :title="t('common.active_orders')" description="Orders currently assigned to you." />

    <LoadingState v-if="loading" label="Loading active tailor orders..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="reloadCurrentPage" />
    <EmptyState v-else-if="orders.length === 0" message="No active orders assigned to you." />

    <template v-else>
      <div class="stack">
        <OrderCard
          v-for="order in orders"
          :key="order.id"
          :order="order"
          details-route-name="tailorOrderDetails"
        />
      </div>

      <UiPagination :pagination="pagination" @page-change="load" />
    </template>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiPagination from '@/components/ui/UiPagination.vue';
import OrderCard from '@/components/orders/OrderCard.vue';
import { fetchTailorActiveOrders } from '@/services/tailorService';
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
    const response = await fetchTailorActiveOrders({
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
    error.value = getErrorMessage(err, 'Failed to load tailor active orders.');
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
