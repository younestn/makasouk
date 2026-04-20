<template>
  <section class="stack">
    <div class="actions">
      <RouterLink class="btn" :to="{ name: 'customerCatalog' }">Back to Catalog</RouterLink>
    </div>

    <LoadingState v-if="loading" label="Loading product details..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <div v-else-if="product" class="card stack">
      <h1 class="title">{{ product.name }}</h1>
      <p class="subtitle">{{ product.description || 'No description.' }}</p>

      <div class="grid grid-2">
        <div>
          <p class="label">Category</p>
          <p>{{ product.category?.name || '-' }}</p>
        </div>
        <div>
          <p class="label">Price</p>
          <p>{{ product.price }} ({{ product.pricing_type }})</p>
        </div>
      </div>

      <div class="actions">
        <RouterLink class="btn btn-primary" :to="{ name: 'customerCreateOrder', query: { productId: product.id } }">
          Create Order
        </RouterLink>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import { fetchProduct } from '@/services/catalogService';
import { getErrorMessage } from '@/services/errorMessage';

const route = useRoute();
const loading = ref(false);
const error = ref('');
const product = ref(null);

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchProduct(route.params.id);
    product.value = response.data;
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load product details.');
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
