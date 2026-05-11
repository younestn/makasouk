<template>
  <section class="stack">
    <UiSectionHeader
      :title="t('customers.reviews_title')"
      :description="t('customers.reviews_description')"
    />

    <LoadingState v-if="loading" :label="t('customers.loading_reviews')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />
    <EmptyState v-else-if="reviews.length === 0" :message="t('customers.empty_reviews')" />

    <template v-else>
      <div class="customer-review-grid">
        <article v-for="review in reviews" :key="review.id" class="ui-card stack">
          <div class="row" style="justify-content: space-between; align-items: flex-start;">
            <div class="row" style="align-items: flex-start;">
              <div class="customer-review-grid__media">
                <img v-if="review.order?.product?.main_image_url" :src="review.order.product.main_image_url" :alt="review.order?.product?.name || ''">
                <div v-else class="product-detail-placeholder">{{ initials(review.order?.product?.name) }}</div>
              </div>
              <div class="stack" style="gap: 0.2rem;">
                <strong>{{ review.order?.product?.name || t('customers.review_product_fallback') }}</strong>
                <p class="small">{{ t('orders.order_reference', { id: review.order_id }) }}</p>
                <p class="small">{{ t('orders.tailor_label') }}: {{ review.tailor?.name || '-' }}</p>
              </div>
            </div>
            <span class="badge badge-warning">{{ review.rating }}/5</span>
          </div>

          <p class="small">{{ review.comment || t('customers.review_comment_empty') }}</p>
          <p class="small">{{ formatDate(review.created_at) }}</p>
        </article>
      </div>

      <UiPagination :pagination="pagination" @page-change="load" />
    </template>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import UiPagination from '@/components/ui/UiPagination.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import { fetchCustomerReviews } from '@/services/customerOrderService';
import { getErrorMessage } from '@/services/errorMessage';
import { defaultPagination, normalizePagination } from '@/utils/pagination';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
const loading = ref(false);
const error = ref('');
const reviews = ref([]);
const pagination = reactive(defaultPagination({ perPage: 8 }));

async function load(page = 1) {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchCustomerReviews({ page, per_page: pagination.perPage });
    reviews.value = response.data || [];
    Object.assign(pagination, normalizePagination(response, { ...pagination, currentPage: page }));
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.customer_reviews_load_failed'));
  } finally {
    loading.value = false;
  }
}

function formatDate(value) {
  return value ? new Date(value).toLocaleString() : '-';
}

function initials(name) {
  return name
    ?.split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((segment) => segment.charAt(0).toUpperCase())
    .join('') || 'MK';
}

onMounted(() => {
  load();
});
</script>
