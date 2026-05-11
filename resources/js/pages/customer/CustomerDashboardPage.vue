<template>
  <section class="stack">
    <UiSectionHeader
      :eyebrow="t('customers.dashboard_badge')"
      :title="t('customers.dashboard_title')"
      :description="t('customers.dashboard_description')"
    />

    <LoadingState v-if="loading" :label="t('customers.dashboard_loading_label')" :hint="t('customers.dashboard_loading_hint')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <template v-else>
      <div class="grid grid-4">
        <UiStatBlock :label="t('customers.dashboard_active_orders_label')" :value="stats.activeOrders" :hint="t('customers.dashboard_active_orders_hint')" tone="info" />
        <UiStatBlock :label="t('customers.dashboard_history_orders_label')" :value="stats.historyOrders" :hint="t('customers.dashboard_history_orders_hint')" />
        <UiStatBlock :label="t('customers.dashboard_custom_orders_label')" :value="stats.customOrders" :hint="t('customers.dashboard_custom_orders_hint')" tone="warning" />
        <UiStatBlock :label="t('customers.dashboard_reviews_label')" :value="stats.reviews" :hint="t('customers.dashboard_reviews_hint')" tone="success" />
      </div>

      <div class="customer-dashboard-grid">
        <div class="ui-card stack">
          <div class="row" style="justify-content: space-between; align-items: center;">
            <div class="stack" style="gap: 0.2rem;">
              <strong>{{ t('customers.dashboard_tracking_title') }}</strong>
              <p class="small">{{ t('customers.dashboard_tracking_description') }}</p>
            </div>
            <RouterLink class="btn" :to="{ name: 'customerActiveOrders' }">{{ t('customers.tracking_nav') }}</RouterLink>
          </div>

          <EmptyState v-if="activeOrders.length === 0" :message="t('customers.empty_active_tracking')" />
          <div v-else class="stack">
            <OrderCard
              v-for="order in activeOrders"
              :key="order.id"
              :order="order"
              details-route-name="customerOrderDetails"
              :timeline-limit="3"
            />
          </div>
        </div>

        <div class="ui-card stack">
          <div class="row" style="justify-content: space-between; align-items: center;">
            <div class="stack" style="gap: 0.2rem;">
              <strong>{{ t('customers.dashboard_quotes_title') }}</strong>
              <p class="small">{{ t('customers.dashboard_quotes_description') }}</p>
            </div>
            <RouterLink class="btn" :to="{ name: 'customerCustomOrders' }">{{ t('customers.custom_orders_nav') }}</RouterLink>
          </div>

          <EmptyState v-if="customOrders.length === 0" :message="t('customers.empty_custom_orders')" />
          <div v-else class="stack">
            <article v-for="item in customOrders" :key="item.id" class="customer-compact-card">
              <div class="row" style="justify-content: space-between; align-items: flex-start;">
                <div class="stack" style="gap: 0.2rem;">
                  <strong>{{ item.title }}</strong>
                  <p class="small">{{ formatDate(item.timestamps?.created_at) }}</p>
                </div>
                <CustomOrderStatusBadge :status="item.status" />
              </div>
              <p class="small">{{ item.notes || t('customers.custom_order_note_empty') }}</p>
              <div class="row">
                <span class="badge badge-neutral">{{ item.tailor_specialty || t('common.not_available') }}</span>
                <span v-if="item.quote?.amount" class="badge badge-warning">{{ formatMoney(item.quote.amount) }}</span>
              </div>
            </article>
          </div>
        </div>
      </div>

      <div class="customer-dashboard-grid">
        <div class="ui-card stack">
          <div class="row" style="justify-content: space-between; align-items: center;">
            <div class="stack" style="gap: 0.2rem;">
              <strong>{{ t('customers.dashboard_purchases_title') }}</strong>
              <p class="small">{{ t('customers.dashboard_purchases_description') }}</p>
            </div>
            <RouterLink class="btn" :to="{ name: 'customerPurchasedProducts' }">{{ t('customers.purchased_products_nav') }}</RouterLink>
          </div>
          <div class="stack">
            <article v-for="order in purchasedOrders" :key="order.id" class="customer-compact-card">
              <div class="row" style="justify-content: space-between; align-items: flex-start;">
                <div class="stack" style="gap: 0.2rem;">
                  <strong>{{ order.product?.name || '-' }}</strong>
                  <p class="small">{{ t('orders.order_reference', { id: order.id }) }}</p>
                </div>
                <OrderStatusBadge :status="order.status" />
              </div>
              <p class="small">{{ formatMoney(order.financials?.total_amount) }}</p>
            </article>
          </div>
        </div>

        <div class="ui-card stack">
          <div class="row" style="justify-content: space-between; align-items: center;">
            <div class="stack" style="gap: 0.2rem;">
              <strong>{{ t('customers.dashboard_reviews_title') }}</strong>
              <p class="small">{{ t('customers.dashboard_reviews_description') }}</p>
            </div>
            <RouterLink class="btn" :to="{ name: 'customerReviews' }">{{ t('customers.reviews_nav') }}</RouterLink>
          </div>

          <EmptyState v-if="recentReviews.length === 0" :message="t('customers.empty_reviews')" />
          <div v-else class="stack">
            <article v-for="review in recentReviews" :key="review.id" class="customer-compact-card">
              <div class="row" style="justify-content: space-between; align-items: flex-start;">
                <div class="stack" style="gap: 0.2rem;">
                  <strong>{{ review.order?.product?.name || t('customers.review_product_fallback') }}</strong>
                  <p class="small">{{ review.tailor?.name || '-' }}</p>
                </div>
                <span class="badge badge-warning">{{ review.rating }}/5</span>
              </div>
              <p class="small">{{ review.comment || t('customers.review_comment_empty') }}</p>
            </article>
          </div>
        </div>
      </div>
    </template>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
import OrderCard from '@/components/orders/OrderCard.vue';
import OrderStatusBadge from '@/components/orders/OrderStatusBadge.vue';
import CustomOrderStatusBadge from '@/components/orders/CustomOrderStatusBadge.vue';
import { fetchActiveOrders, fetchCustomerReviews, fetchOrderHistory, fetchPurchasedOrders } from '@/services/customerOrderService';
import { fetchCustomerCustomOrders } from '@/services/customerCustomOrderService';
import { getErrorMessage } from '@/services/errorMessage';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
const loading = ref(false);
const error = ref('');
const stats = reactive({
  activeOrders: 0,
  historyOrders: 0,
  customOrders: 0,
  reviews: 0,
});
const activeOrders = ref([]);
const purchasedOrders = ref([]);
const customOrders = ref([]);
const recentReviews = ref([]);

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const [activeResponse, historyResponse, purchasedResponse, customResponse, reviewsResponse] = await Promise.all([
      fetchActiveOrders({ per_page: 2 }),
      fetchOrderHistory({ per_page: 1 }),
      fetchPurchasedOrders({ per_page: 3 }),
      fetchCustomerCustomOrders({ scope: 'active', per_page: 3 }),
      fetchCustomerReviews({ per_page: 3 }),
    ]);

    stats.activeOrders = activeResponse.meta?.total || 0;
    stats.historyOrders = historyResponse.meta?.total || 0;
    stats.customOrders = customResponse.meta?.total || 0;
    stats.reviews = reviewsResponse.meta?.total || 0;

    activeOrders.value = activeResponse.data || [];
    purchasedOrders.value = purchasedResponse.data || [];
    customOrders.value = customResponse.data || [];
    recentReviews.value = reviewsResponse.data || [];
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.customer_dashboard_load_failed'));
  } finally {
    loading.value = false;
  }
}

function formatDate(value) {
  return value ? new Date(value).toLocaleString() : '-';
}

function formatMoney(value) {
  return `${new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 }).format(Number(value || 0))} MAD`;
}

onMounted(load);
</script>
