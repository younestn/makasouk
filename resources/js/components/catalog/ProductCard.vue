<template>
  <article class="card stack">
    <div class="row" style="justify-content: space-between;">
      <h3 class="title" style="font-size: 1rem; margin: 0;">{{ product.name }}</h3>
      <span class="badge badge-neutral">{{ product.pricing_type }}</span>
    </div>

    <p class="small">{{ product.description || 'No description.' }}</p>

    <p class="small">
      Category: {{ product.category?.name || '-' }}
    </p>

    <p><strong>{{ product.price }}</strong></p>

    <div class="actions">
      <RouterLink class="btn" :to="{ name: 'customerProductDetails', params: { id: product.id } }">
        Details
      </RouterLink>
      <RouterLink class="btn btn-primary" :to="{ name: 'customerCreateOrder', query: { productId: product.id } }">
        Order
      </RouterLink>
    </div>
  </article>
</template>

<script setup>
import { RouterLink } from 'vue-router';

defineProps({
  product: {
    type: Object,
    required: true,
  },
});
</script>
