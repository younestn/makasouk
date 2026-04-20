<template>
  <section class="stack">
    <UiSectionHeader
      title="Catalog"
      description="Browse active categories and products, then create an order directly."
    />

    <div class="ui-card stack">
      <div class="grid grid-2">
        <div>
          <label class="label">Category</label>
          <select v-model="filters.categoryId" class="select">
            <option value="">All categories</option>
            <option v-for="category in categories" :key="category.id" :value="String(category.id)">
              {{ category.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="label">Search</label>
          <input
            v-model="filters.query"
            class="input"
            type="text"
            placeholder="Search by product name or description"
            @keyup.enter="loadProducts"
          />
        </div>
      </div>

      <div class="actions">
        <button class="btn btn-primary" type="button" :disabled="loading" @click="loadProducts">Apply</button>
        <button class="btn" type="button" :disabled="loading" @click="resetFilters">Reset</button>
      </div>
    </div>

    <LoadingState v-if="loading" label="Loading products..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="loadProducts" />
    <EmptyState v-else-if="products.length === 0" message="No products matched your filters.">
      <template #actions>
        <button class="btn" type="button" @click="resetFilters">Clear filters</button>
      </template>
    </EmptyState>

    <div v-else class="grid grid-2">
      <ProductCard v-for="product in products" :key="product.id" :product="product" />
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import ProductCard from '@/components/catalog/ProductCard.vue';
import { fetchCategories, fetchProducts } from '@/services/catalogService';
import { getErrorMessage } from '@/services/errorMessage';

const loading = ref(false);
const error = ref('');
const categories = ref([]);
const products = ref([]);

const filters = reactive({
  categoryId: '',
  query: '',
});

async function loadCategories() {
  const response = await fetchCategories({ per_page: 100 });
  categories.value = response.data || [];
}

async function loadProducts() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchProducts({
      category_id: filters.categoryId || undefined,
      q: filters.query || undefined,
      per_page: 40,
    });

    products.value = response.data || [];
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load products.');
  } finally {
    loading.value = false;
  }
}

function resetFilters() {
  filters.categoryId = '';
  filters.query = '';
  loadProducts();
}

onMounted(async () => {
  try {
    await loadCategories();
    await loadProducts();
  } catch (err) {
    error.value = getErrorMessage(err, 'Unable to initialize catalog.');
  }
});
</script>
