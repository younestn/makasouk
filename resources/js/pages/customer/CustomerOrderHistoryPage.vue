<template>
  <section class="stack">
    <UiSectionHeader
      :eyebrow="t('customers.dashboard_badge')"
      :title="t('customers.history_page_title')"
      :description="t('orders.history_description')"
    />

    <LoadingState v-if="loading" :label="t('orders.loading_history')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="reloadCurrentPage" />

    <template v-else>
      <div class="ui-card stack">
        <div class="row" style="justify-content: space-between; align-items: center;">
          <div class="stack" style="gap: 0.2rem;">
            <strong>{{ t('customers.history_standard_orders_title') }}</strong>
            <p class="small">{{ t('customers.history_standard_orders_description') }}</p>
          </div>
          <RouterLink class="btn" :to="{ name: 'customerPurchasedProducts' }">{{ t('customers.purchased_products_nav') }}</RouterLink>
        </div>

        <EmptyState v-if="orders.length === 0" :message="t('orders.empty_history')" />

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
            <strong>{{ t('customers.history_custom_orders_title') }}</strong>
            <p class="small">{{ t('customers.history_custom_orders_description') }}</p>
          </div>
          <RouterLink class="btn btn-primary" :to="{ name: 'customerCustomOrders', query: { scope: 'history' } }">
            {{ t('customers.custom_orders_nav') }}
          </RouterLink>
        </div>

        <EmptyState v-if="customOrders.length === 0" :message="t('custom_orders.empty_history')" />

        <div v-else class="stack">
          <article v-for="item in customOrders" :key="item.id" class="customer-custom-order-card">
            <div class="row" style="justify-content: space-between; align-items: flex-start;">
              <div class="stack" style="gap: 0.2rem;">
                <strong>{{ item.title }}</strong>
                <p class="small">{{ formatDate(item.timestamps?.created_at) }}</p>
              </div>
              <CustomOrderStatusBadge :status="item.status" />
            </div>

            <div class="grid grid-2">
              <div class="product-spec-card">
                <span class="product-spec-label">{{ t('custom_orders.specialty_label') }}</span>
                <strong class="product-spec-value">{{ item.tailor_specialty }}</strong>
              </div>
              <div class="product-spec-card">
                <span class="product-spec-label">{{ t('orders.tailor_label') }}</span>
                <strong class="product-spec-value">{{ item.tailor?.name || t('customers.awaiting_tailor_assignment') }}</strong>
              </div>
            </div>

            <p v-if="item.quote?.rejection_note" class="small">
              {{ t('custom_orders.rejection_note_label') }}: {{ item.quote.rejection_note }}
            </p>
            <p v-else-if="item.quote?.note" class="small">{{ item.quote.note }}</p>
            <p v-else-if="item.notes" class="small">{{ item.notes }}</p>

            <OrderTimeline
              v-if="item.timeline?.length"
              :items="item.timeline"
              namespace="custom_orders"
            />
          </article>
        </div>
      </div>
    </template>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiPagination from '@/components/ui/UiPagination.vue';
import OrderCard from '@/components/orders/OrderCard.vue';
import OrderTimeline from '@/components/orders/OrderTimeline.vue';
import CustomOrderStatusBadge from '@/components/orders/CustomOrderStatusBadge.vue';
import { fetchOrderHistory } from '@/services/customerOrderService';
import { fetchCustomerCustomOrders } from '@/services/customerCustomOrderService';
import { getErrorMessage } from '@/services/errorMessage';
import { defaultPagination, normalizePagination } from '@/utils/pagination';
import { useI18n } from '@/composables/useI18n';

const route = useRoute();
const router = useRouter();
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
      fetchOrderHistory({
        page: safePage,
        per_page: pagination.perPage,
      }),
      fetchCustomerCustomOrders({
        scope: 'history',
        per_page: 50,
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
    error.value = getErrorMessage(err, t('messages.orders_history_load_failed'));
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

onMounted(() => {
  load(queryPage());
});
</script>
