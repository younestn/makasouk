<template>
  <section class="stack">
    <UiSectionHeader
      :title="t('customers.purchased_products_title')"
      :description="t('customers.purchased_products_description')"
    />

    <LoadingState v-if="loading" :label="t('customers.loading_purchased_products')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />
    <EmptyState v-else-if="orders.length === 0" :message="t('customers.empty_purchased_products')" />

    <template v-else>
      <div class="customer-order-grid">
        <article v-for="order in orders" :key="order.id" class="customer-order-grid__card ui-card stack">
          <div class="customer-order-grid__media">
            <img
              v-if="order.product?.main_image_url"
              :src="order.product.main_image_url"
              :alt="order.product?.name || ''"
            >
            <div v-else class="product-detail-placeholder">
              {{ initials(order.product?.name) }}
            </div>
          </div>

          <div class="stack" style="gap: 0.6rem;">
            <div class="row" style="justify-content: space-between; align-items: flex-start;">
              <div class="stack" style="gap: 0.2rem;">
                <strong>{{ order.product?.name || '-' }}</strong>
                <p class="small">{{ t('orders.order_reference', { id: order.id }) }}</p>
              </div>
              <OrderStatusBadge :status="order.status" />
            </div>

            <div class="row">
              <span class="badge badge-neutral">{{ stageLabel(order.tracking?.current_stage) }}</span>
              <span v-if="order.review" class="badge badge-success">{{ t('customers.review_submitted_badge') }}</span>
            </div>

            <div class="grid grid-2">
              <p class="small"><strong>{{ t('orders.created_at') }}:</strong> {{ formatDate(order.timestamps?.created_at) }}</p>
              <p class="small"><strong>{{ t('products.price_label') }}:</strong> {{ formatMoney(order.financials?.total_amount) }}</p>
              <p class="small"><strong>{{ t('orders.tailor_label') }}:</strong> {{ order.tailor?.name || t('customers.awaiting_tailor_assignment') }}</p>
              <p class="small"><strong>{{ t('orders.shipping_company_label') }}:</strong> {{ order.shipping?.company_name || '-' }}</p>
            </div>

            <OrderTimeline
              v-if="order.tracking?.timeline?.length"
              :items="order.tracking.timeline"
              compact
              :limit="4"
            />

            <div class="actions">
              <RouterLink class="btn" :to="{ name: 'customerOrderDetails', params: { id: order.id } }">
                {{ t('orders.open_details_action') }}
              </RouterLink>
              <RouterLink
                v-if="order.permissions?.can_review && !order.review"
                class="btn btn-primary"
                :to="{ name: 'customerOrderDetails', params: { id: order.id } }"
              >
                {{ t('customers.review_order_action') }}
              </RouterLink>
            </div>
          </div>
        </article>
      </div>

      <UiPagination :pagination="pagination" @page-change="load" />
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
import UiPagination from '@/components/ui/UiPagination.vue';
import OrderStatusBadge from '@/components/orders/OrderStatusBadge.vue';
import OrderTimeline from '@/components/orders/OrderTimeline.vue';
import { fetchPurchasedOrders } from '@/services/customerOrderService';
import { getErrorMessage } from '@/services/errorMessage';
import { defaultPagination, normalizePagination } from '@/utils/pagination';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
const loading = ref(false);
const error = ref('');
const orders = ref([]);
const pagination = reactive(defaultPagination({ perPage: 8 }));

async function load(page = 1) {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchPurchasedOrders({ page, per_page: pagination.perPage });
    orders.value = response.data || [];
    Object.assign(pagination, normalizePagination(response, { ...pagination, currentPage: page }));
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.orders_history_load_failed'));
  } finally {
    loading.value = false;
  }
}

function stageLabel(stage) {
  if (!stage) {
    return t('common.not_available');
  }

  return t(`orders.timeline_labels.${stage}`);
}

function formatDate(value) {
  return value ? new Date(value).toLocaleString() : '-';
}

function formatMoney(value) {
  return `${new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 }).format(Number(value || 0))} MAD`;
}

function initials(name) {
  return name
    ?.split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((segment) => segment.charAt(0).toUpperCase())
    .join('') || 'MK';
}

onMounted(() => {
  load();
});
</script>
