<template>
  <article class="ui-card stack">
    <div class="row" style="justify-content: space-between; align-items: flex-start;">
      <h3 class="title" style="font-size: 1.05rem; margin: 0;">{{ product.name }}</h3>
      <span class="badge badge-neutral">{{ product.pricing_type }}</span>
    </div>

    <p class="small">{{ product.description || 'No description available for this product yet.' }}</p>

    <div class="row" style="justify-content: space-between;">
      <p class="small"><strong>Category:</strong> {{ product.category?.name || '-' }}</p>
      <p class="title" style="font-size: 1rem;">{{ product.price }}</p>
    </div>

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
