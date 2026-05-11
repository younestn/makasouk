<template>
  <section class="stack">
    <UiSectionHeader
      :eyebrow="t('customers.dashboard_badge')"
      :title="t('customers.tracking_page_title')"
      :description="t('orders.active_description')"
    />

    <LoadingState v-if="loading" :label="t('orders.loading_active')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="reloadCurrentPage" />

    <template v-else>
      <div class="customer-dashboard-grid">
        <div class="ui-card stack">
          <div class="row" style="justify-content: space-between; align-items: center;">
            <div class="stack" style="gap: 0.2rem;">
              <strong>{{ t('customers.dashboard_tracking_title') }}</strong>
              <p class="small">{{ t('customers.dashboard_tracking_description') }}</p>
            </div>
            <RouterLink class="btn" :to="{ name: 'customerPurchasedProducts' }">{{ t('customers.purchased_products_nav') }}</RouterLink>
          </div>

          <EmptyState v-if="orders.length === 0" :message="t('orders.empty_active')">
            <template #actions>
              <RouterLink class="btn btn-primary" :to="{ name: 'customerCreateOrder' }">{{ t('common.create_order') }}</RouterLink>
            </template>
          </EmptyState>

          <div v-else class="stack">
            <OrderCard
              v-for="order in orders"
              :key="order.id"
              :order="order"
              details-route-name="customerOrderDetails"
            />

            <UiPagination :pagination="pagination" @page-change="load" />
          </div>
        </div>

        <div class="ui-card stack">
          <div class="row" style="justify-content: space-between; align-items: center;">
            <div class="stack" style="gap: 0.2rem;">
              <strong>{{ t('customers.dashboard_quotes_title') }}</strong>
              <p class="small">{{ t('customers.dashboard_quotes_description') }}</p>
            </div>
            <RouterLink class="btn btn-primary" :to="{ name: 'customerCustomOrders' }">{{ t('customers.custom_orders_nav') }}</RouterLink>
          </div>

          <EmptyState v-if="customOrders.length === 0" :message="t('customers.empty_custom_orders')" />

          <div v-else class="stack">
            <article v-for="item in customOrders" :key="item.id" class="customer-custom-order-card customer-custom-order-card--compact">
              <div class="row" style="justify-content: space-between; align-items: flex-start;">
                <div class="stack" style="gap: 0.2rem;">
                  <strong>{{ item.title }}</strong>
                  <p class="small">{{ formatDate(item.timestamps?.created_at) }}</p>
                </div>
                <CustomOrderStatusBadge :status="item.status" />
              </div>

              <p class="small">{{ item.quote?.note || item.notes || t('customers.custom_order_note_empty') }}</p>

              <div class="row">
                <span class="badge badge-neutral">{{ item.tailor_specialty }}</span>
                <span v-if="item.quote?.amount" class="badge badge-warning">{{ formatMoney(item.quote.amount) }}</span>
              </div>

              <OrderTimeline
                v-if="item.timeline?.length"
                :items="item.timeline"
                namespace="custom_orders"
                compact
                :limit="4"
              />
            </article>
          </div>
        </div>
      </div>
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
import OrderTimeline from '@/components/orders/OrderTimeline.vue';
import CustomOrderStatusBadge from '@/components/orders/CustomOrderStatusBadge.vue';
import { fetchActiveOrders } from '@/services/customerOrderService';
import { fetchCustomerCustomOrders } from '@/services/customerCustomOrderService';
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
const customOrders = ref([]);
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
    const [ordersResponse, customOrdersResponse] = await Promise.all([
      fetchActiveOrders({
        page: safePage,
        per_page: pagination.perPage,
      }),
      fetchCustomerCustomOrders({
        scope: 'active',
        per_page: 10,
      }),
    ]);

    orders.value = ordersResponse.data || [];
    customOrders.value = customOrdersResponse.data || [];

    Object.assign(
      pagination,
      normalizePagination(ordersResponse, {
        ...pagination,
        currentPage: safePage,
      }),
    );

    syncQuery(pagination.currentPage);
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.orders_active_load_failed'));
  } finally {
    loading.value = false;
  }
}

function reloadCurrentPage() {
  load(pagination.currentPage);
}

function formatDate(value) {
  return value ? new Date(value).toLocaleString() : '-';
}

function formatMoney(value) {
  return `${new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 }).format(Number(value || 0))} MAD`;
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
