<template>
  <article class="ui-card stack">
    <div class="row" style="justify-content: space-between; align-items: flex-start;">
      <h3 class="title" style="font-size: 1.05rem; margin: 0;">{{ product.name }}</h3>
      <span class="badge badge-neutral">{{ product.pricing_type }}</span>
    </div>

    <p class="small">{{ product.description || t('products.no_description_available') }}</p>

    <div class="row" style="justify-content: space-between;">
      <p class="small"><strong>{{ t('products.category_label') }}:</strong> {{ product.category?.name || '-' }}</p>
      <p class="title" style="font-size: 1rem;">{{ product.price }}</p>
    </div>

    <div class="actions">
      <RouterLink class="btn" :to="{ name: 'customerProductDetails', params: { id: product.id } }">
        {{ t('products.details_action') }}
      </RouterLink>
      <RouterLink class="btn btn-primary" :to="{ name: 'customerCreateOrder', query: { productId: product.id } }">
        {{ t('products.order_action') }}
      </RouterLink>
    </div>
  </article>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { useI18n } from '@/composables/useI18n';

defineProps({
  product: {
    type: Object,
    required: true,
  },
});

const { t } = useI18n();
</script>
