<template>
  <section class="stack">
    <div class="actions">
      <RouterLink class="btn" :to="{ name: 'customerCatalog' }">{{ t('products.back_to_catalog') }}</RouterLink>
    </div>

    <LoadingState v-if="loading" :label="t('products.loading_details')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <template v-else-if="product">
      <div class="product-detail-shell stack">
        <div class="product-detail-layout ui-card">
          <div class="product-detail-media">
            <div class="product-detail-main-frame">
              <img
                v-if="activeImage"
                :src="activeImage"
                :alt="product.name"
                class="product-detail-main-image"
              />
              <div v-else class="product-detail-placeholder">
                {{ initials }}
              </div>
            </div>

            <div
              v-if="productImages.length > 1"
              class="product-detail-thumbnails"
              :aria-label="t('products.gallery_title')"
            >
              <button
                v-for="(imageUrl, index) in productImages"
                :key="`${imageUrl}-${index}`"
                type="button"
                class="product-detail-thumb"
                :class="{ 'is-active': imageUrl === activeImage }"
                @click="setActiveImage(imageUrl)"
              >
                <img :src="imageUrl" :alt="`${product.name} ${index + 1}`" />
              </button>
            </div>
          </div>

          <div class="product-detail-summary stack">
            <p class="small">
              {{ t('products.category_label') }}:
              <span class="product-detail-category">{{ product.category?.name || '-' }}</span>
            </p>

            <h1 class="product-detail-title">{{ product.name }}</h1>

            <div class="product-detail-price-wrap">
              <div class="product-detail-price-row">
                <strong class="product-detail-price">{{ primaryPrice }}</strong>
                <span v-if="secondaryPrice" class="product-detail-price-old">{{ secondaryPrice }}</span>
              </div>
              <span class="badge badge-neutral">{{ pricingTypeLabel }}</span>
            </div>

            <div class="product-detail-rating">
              <div class="product-rating-stars" aria-hidden="true">
                <span
                  v-for="star in 5"
                  :key="star"
                  :class="{ 'is-filled': star <= filledStars }"
                >&#9733;</span>
              </div>
              <p class="small">
                <template v-if="hasReviews">
                  {{ t('products.rating_label', { rating: ratingDisplay }) }}
                  <span aria-hidden="true">&middot;</span>
                  {{ t('products.rating_count', { count: product.reviews_count || 0 }) }}
                </template>
                <template v-else>
                  {{ t('products.rating_fallback') }}
                </template>
              </p>
            </div>

            <div v-if="product.attributes?.length" class="product-detail-spec-grid">
              <div
                v-for="attribute in product.attributes"
                :key="attribute.key"
                class="product-spec-card"
              >
                <span class="product-spec-label">{{ attribute.label }}</span>
                <strong class="product-spec-value">{{ attribute.value }}</strong>
              </div>
            </div>

            <div v-if="hasFabricInfo" class="product-detail-fabric ui-card stack">
              <div class="row" style="justify-content: space-between; align-items: center;">
                <h2 class="title product-detail-section-title">{{ t('products.fabric_section_title') }}</h2>
                <a
                  v-if="product.fabric_image_url"
                  class="btn"
                  :href="product.fabric_image_url"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  {{ t('products.view_fabric_image') }}
                </a>
              </div>

              <div class="grid grid-2">
                <div class="product-spec-card">
                  <span class="product-spec-label">{{ t('products.fabric_type_label') }}</span>
                  <strong class="product-spec-value">{{ product.fabric_type || '-' }}</strong>
                </div>
                <div class="product-spec-card">
                  <span class="product-spec-label">{{ t('products.fabric_country_label') }}</span>
                  <strong class="product-spec-value">{{ product.fabric_country || '-' }}</strong>
                </div>
              </div>

              <p v-if="product.fabric_description" class="small">{{ product.fabric_description }}</p>
            </div>

            <div class="product-detail-copy stack">
              <div class="product-detail-section">
                <h2 class="title product-detail-section-title">{{ t('products.details_title') }}</h2>
                <p class="small product-detail-text">{{ product.details || t('products.no_details_available') }}</p>
              </div>

              <div class="product-detail-section">
                <h2 class="title product-detail-section-title">{{ t('products.description_title') }}</h2>
                <p class="small product-detail-text">{{ product.description || t('products.no_description_available') }}</p>
              </div>
            </div>

            <div class="actions">
              <RouterLink class="btn btn-primary" :to="orderRoute">
                {{ t('products.order_now_action') }}
              </RouterLink>
            </div>
          </div>
        </div>

        <section class="stack">
          <UiSectionHeader
            :title="t('products.similar_products_title')"
            :description="t('products.similar_products_description')"
          />

          <div v-if="similarProducts.length" class="storefront-product-grid">
            <ProductCard
              v-for="similarProduct in similarProducts"
              :key="similarProduct.id"
              :product="similarProduct"
            />
          </div>
          <div v-else class="ui-card">
            <p class="small">{{ t('products.no_similar_products') }}</p>
          </div>
        </section>
      </div>
    </template>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import ProductCard from '@/components/catalog/ProductCard.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import { fetchProduct } from '@/services/catalogService';
import { getErrorMessage } from '@/services/errorMessage';
import { useI18n } from '@/composables/useI18n';

const route = useRoute();
const { t } = useI18n();

const loading = ref(false);
const error = ref('');
const product = ref(null);
const similarProducts = ref([]);
const activeImage = ref('');

const productImages = computed(() => {
  const gallery = product.value?.gallery_image_urls || [];
  const mainImage = product.value?.main_image_url ? [product.value.main_image_url] : [];

  return Array.from(new Set([...mainImage, ...gallery].filter(Boolean)));
});

const initials = computed(() => {
  if (!product.value?.name) {
    return 'MK';
  }

  return product.value.name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((segment) => segment.charAt(0).toUpperCase())
    .join('');
});

const hasReviews = computed(() => Number(product.value?.reviews_count || 0) > 0 && Number(product.value?.rating_average || 0) > 0);
const ratingDisplay = computed(() => Number(product.value?.rating_average || 0).toFixed(1));
const filledStars = computed(() => Math.max(0, Math.min(5, Math.round(Number(product.value?.rating_average || 0)))));
const pricingTypeLabel = computed(() => t(`products.pricing_types.${product.value?.pricing_type || 'unknown'}`));
const hasFabricInfo = computed(() => Boolean(
  product.value?.fabric_type
  || product.value?.fabric_country
  || product.value?.fabric_description
  || product.value?.fabric_image_url,
));

const primaryPrice = computed(() => formatMoney(product.value?.sale_price ?? product.value?.price));
const secondaryPrice = computed(() => (
  product.value?.sale_price ? formatMoney(product.value?.price) : ''
));

const orderRoute = computed(() => ({
  name: 'customerCreateOrder',
  query: { productId: product.value?.id },
}));

function formatMoney(value) {
  if (value === null || value === undefined || value === '') {
    return '-';
  }

  return `${new Intl.NumberFormat(undefined, {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(Number(value))} MAD`;
}

function setActiveImage(imageUrl) {
  activeImage.value = imageUrl;
}

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchProduct(route.params.id);
    product.value = response.data || null;
    similarProducts.value = response.meta?.similar_products || [];
    activeImage.value = productImages.value[0] || '';
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.product_details_load_failed'));
  } finally {
    loading.value = false;
  }
}

onMounted(load);

watch(
  () => route.params.id,
  () => {
    load();
  },
);
</script>
