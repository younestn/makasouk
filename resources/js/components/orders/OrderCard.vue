<template>
  <article class="dashboard-order-card ui-card">
    <div class="dashboard-order-card__media">
      <img
        v-if="order.product?.main_image_url"
        :src="order.product.main_image_url"
        :alt="order.product?.name || ''"
      >
      <div v-else class="product-detail-placeholder">{{ initials }}</div>
    </div>

    <div class="dashboard-order-card__body stack">
      <div class="row" style="justify-content: space-between; align-items: flex-start;">
        <div class="stack" style="gap: 0.2rem;">
          <h3 class="title" style="font-size: 1.05rem; margin: 0;">{{ order.product?.name || t('orders.order_reference', { id: order.id }) }}</h3>
          <p class="small">{{ t('orders.order_reference', { id: order.id }) }}</p>
          <p class="small" v-if="order.timestamps?.created_at">{{ t('orders.created_at_short', { date: formatDate(order.timestamps.created_at) }) }}</p>
        </div>
        <div class="stack" style="gap: 0.35rem; align-items: flex-end;">
          <OrderStatusBadge :status="order.status" />
          <span v-if="order.tracking?.current_stage" class="badge badge-neutral">{{ stageLabel }}</span>
        </div>
      </div>

      <div class="grid grid-2" style="gap: 0.65rem;">
        <p class="small" v-if="order.tailor && order.tailor.name">
          <strong>{{ t('orders.tailor_label') }}:</strong> {{ order.tailor.name }}
        </p>
        <p class="small" v-if="order.customer && order.customer.name">
          <strong>{{ t('orders.customer_label') }}:</strong> {{ order.customer.name }}
        </p>
        <p class="small" v-if="order.financials?.total_amount">
          <strong>{{ t('orders.total_amount') }}:</strong> {{ formatMoney(order.financials.total_amount) }}
        </p>
        <p class="small" v-if="order.shipping?.company_name">
          <strong>{{ t('orders.shipping_company_label') }}:</strong> {{ order.shipping.company_name }}
        </p>
      </div>

      <OrderTimeline
        v-if="showTimeline && order.tracking?.timeline?.length"
        :items="order.tracking.timeline"
        compact
        :limit="timelineLimit"
      />

      <div class="actions">
        <RouterLink class="btn" :to="{ name: detailsRouteName, params: { id: order.id } }">
          {{ t('orders.open_details_action') }}
        </RouterLink>
        <slot name="actions" :order="order" />
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { useI18n } from '@/composables/useI18n';
import OrderStatusBadge from '@/components/orders/OrderStatusBadge.vue';
import OrderTimeline from '@/components/orders/OrderTimeline.vue';

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  detailsRouteName: {
    type: String,
    required: true,
  },
  showTimeline: {
    type: Boolean,
    default: true,
  },
  timelineLimit: {
    type: Number,
    default: 4,
  },
});

const { t } = useI18n();

const initials = computed(() => props.order.product?.name
  ?.split(' ')
  .filter(Boolean)
  .slice(0, 2)
  .map((segment) => segment.charAt(0).toUpperCase())
  .join('') || 'MK');

const stageLabel = computed(() => (
  props.order.tracking?.current_stage
    ? t(`orders.timeline_labels.${props.order.tracking.current_stage}`)
    : t('common.not_available')
));

function formatDate(value) {
  if (!value) {
    return '-';
  }

  return new Date(value).toLocaleString();
}

function formatMoney(value) {
  return `${new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 }).format(Number(value || 0))} MAD`;
}
</script>
