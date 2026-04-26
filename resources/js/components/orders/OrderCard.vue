<template>
  <article class="ui-card stack">
    <div class="row" style="justify-content: space-between; align-items: flex-start;">
      <div class="stack" style="gap: 0.2rem;">
        <h3 class="title" style="font-size: 1.05rem; margin: 0;">{{ t('orders.order_reference', { id: order.id }) }}</h3>
        <p class="small" v-if="order.timestamps?.created_at">{{ t('orders.created_at_short', { date: formatDate(order.timestamps.created_at) }) }}</p>
      </div>
      <OrderStatusBadge :status="order.status" />
    </div>

    <div class="grid grid-2" style="gap: 0.65rem;">
      <p class="small" v-if="order.product">
        <strong>{{ t('orders.product_label') }}:</strong> {{ order.product.name }}
      </p>

      <p class="small" v-if="order.tailor && order.tailor.name">
        <strong>{{ t('orders.tailor_label') }}:</strong> {{ order.tailor.name }}
      </p>

      <p class="small" v-if="order.customer && order.customer.name">
        <strong>{{ t('orders.customer_label') }}:</strong> {{ order.customer.name }}
      </p>

      <p class="small" v-if="order.delivery?.latitude && order.delivery?.longitude">
        <strong>{{ t('orders.delivery_label') }}:</strong> {{ order.delivery.latitude }}, {{ order.delivery.longitude }}
      </p>
    </div>

    <div class="actions">
      <RouterLink class="btn" :to="{ name: detailsRouteName, params: { id: order.id } }">
        {{ t('orders.open_details_action') }}
      </RouterLink>
      <slot name="actions" :order="order" />
    </div>
  </article>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { useI18n } from '@/composables/useI18n';
import OrderStatusBadge from '@/components/orders/OrderStatusBadge.vue';

defineProps({
  order: {
    type: Object,
    required: true,
  },
  detailsRouteName: {
    type: String,
    required: true,
  },
});

const { t } = useI18n();

function formatDate(value) {
  if (!value) {
    return '-';
  }

  return new Date(value).toLocaleString();
}
</script>
