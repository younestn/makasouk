<template>
  <section class="stack">
    <div class="actions">
      <RouterLink class="btn" :to="{ name: 'customerCatalog' }">Back to Catalog</RouterLink>
    </div>

    <LoadingState v-if="loading" label="Loading product details..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <div v-else-if="product" class="ui-card stack">
      <UiSectionHeader :title="product.name" :description="product.description || 'No description available.'" />

      <div class="grid grid-2">
        <UiStatBlock label="Category" :value="product.category?.name || '-'" />
        <UiStatBlock label="Price" :value="`${product.price} (${product.pricing_type})`" tone="info" />
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
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
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
