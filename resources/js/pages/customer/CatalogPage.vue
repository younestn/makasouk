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
          <label class="label">{{ t('common.search') }}</label>
          <input
            v-model="filters.query"
            class="input"
            type="text"
            placeholder="Search by product name or description"
            @keyup.enter="applyFilters"
          />
        </div>
      </div>

      <div class="actions">
        <button class="btn btn-primary" type="button" :disabled="loading" @click="applyFilters">{{ t('common.apply') }}</button>
        <button class="btn" type="button" :disabled="loading" @click="resetFilters">{{ t('common.reset') }}</button>
      </div>
    </div>

    <LoadingState v-if="loading" label="Loading products..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="reloadCurrentPage" />
    <EmptyState v-else-if="products.length === 0" message="No products matched your filters.">
      <template #actions>
        <button class="btn" type="button" @click="resetFilters">Clear filters</button>
      </template>
    </EmptyState>

    <template v-else>
      <div class="grid grid-2">
        <ProductCard v-for="product in products" :key="product.id" :product="product" />
      </div>

      <UiPagination :pagination="pagination" @page-change="loadProducts" />
    </template>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiPagination from '@/components/ui/UiPagination.vue';
import ProductCard from '@/components/catalog/ProductCard.vue';
import { fetchCategories, fetchProducts } from '@/services/catalogService';
import { getErrorMessage } from '@/services/errorMessage';
import { defaultPagination, normalizePagination } from '@/utils/pagination';
import { useI18n } from '@/composables/useI18n';

const route = useRoute();
const router = useRouter();
const { t } = useI18n();

const loading = ref(false);
const error = ref('');
const categories = ref([]);
const products = ref([]);

const filters = reactive({
  categoryId: String(route.query.category || ''),
  query: String(route.query.q || ''),
});

const pagination = reactive(defaultPagination({ perPage: 12 }));

function queryPage() {
  const page = Number(route.query.page || 1);

  if (!Number.isInteger(page) || page < 1) {
    return 1;
  }

  return page;
}

function syncQuery(page) {
  router.replace({
    query: {
      ...route.query,
      category: filters.categoryId || undefined,
      q: filters.query || undefined,
      page: page > 1 ? String(page) : undefined,
    },
  });
}

async function loadCategories() {
  const response = await fetchCategories({ per_page: 100 });
  categories.value = response.data || [];
}

async function loadProducts(page = pagination.currentPage) {
  loading.value = true;
  error.value = '';

  const safePage = Math.max(1, Number(page || 1));

  try {
    const response = await fetchProducts({
      category_id: filters.categoryId || undefined,
      q: filters.query || undefined,
      per_page: pagination.perPage,
      page: safePage,
    });

    products.value = response.data || [];

    Object.assign(
      pagination,
      normalizePagination(response, {
        ...pagination,
        currentPage: safePage,
      }),
    );

    syncQuery(pagination.currentPage);
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load products.');
  } finally {
    loading.value = false;
  }
}

function reloadCurrentPage() {
  loadProducts(pagination.currentPage);
}

function applyFilters() {
  loadProducts(1);
}

function resetFilters() {
  filters.categoryId = '';
  filters.query = '';
  loadProducts(1);
}

onMounted(async () => {
  try {
    await loadCategories();
    await loadProducts(queryPage());
  } catch (err) {
    error.value = getErrorMessage(err, 'Unable to initialize catalog.');
  }
});
</script>
