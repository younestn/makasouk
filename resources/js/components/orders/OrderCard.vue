<template>
  <article class="card stack">
    <div class="row" style="justify-content: space-between;">
      <h3 class="title" style="font-size: 1rem; margin: 0;">Order #{{ order.id }}</h3>
      <OrderStatusBadge :status="order.status" />
    </div>

    <p class="small" v-if="order.product">
      Product: {{ order.product.name }}
    </p>

    <p class="small" v-if="order.tailor && order.tailor.name">
      Tailor: {{ order.tailor.name }}
    </p>

    <p class="small" v-if="order.customer && order.customer.name">
      Customer: {{ order.customer.name }}
    </p>

    <div class="actions">
      <RouterLink class="btn" :to="{ name: detailsRouteName, params: { id: order.id } }">
        Open Details
      </RouterLink>
      <slot name="actions" :order="order" />
    </div>
  </article>
</template>

<script setup>
import { RouterLink } from 'vue-router';
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
</script>
