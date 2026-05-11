<template>
  <article class="storefront-product-card ui-card">
    <component
      :is="detailLinkComponent"
      class="storefront-product-card__media"
      v-bind="detailLinkProps"
    >
      <img
        v-if="product.main_image_url"
        :src="product.main_image_url"
        :alt="product.name"
      />
      <div v-else class="storefront-product-card__placeholder">
        {{ initials }}
      </div>

      <div class="storefront-product-card__badges">
        <span v-if="product.is_featured" class="badge badge-info text-bg-info">{{ t('products.featured_badge') }}</span>
        <span v-if="product.is_best_seller" class="badge badge-success text-bg-success">{{ t('products.best_seller_badge') }}</span>
      </div>
    </component>

    <div class="storefront-product-card__body stack">
      <p class="small">{{ categoryLabel }}</p>
      <component
        :is="detailLinkComponent"
        class="storefront-product-card__title"
        v-bind="detailLinkProps"
      >
        {{ product.name }}
      </component>

      <p class="small storefront-product-card__excerpt">
        {{ product.details || product.description || t('products.no_description_available') }}
      </p>

      <div class="storefront-product-card__meta">
        <div class="storefront-product-card__price">
          <strong>{{ primaryPrice }}</strong>
          <span v-if="secondaryPrice" class="storefront-product-card__price-old">{{ secondaryPrice }}</span>
        </div>

        <p class="small storefront-product-card__rating">
          <template v-if="hasReviews">
            {{ t('products.rating_label', { rating: ratingDisplay }) }}
          </template>
          <template v-else>
            {{ t('products.rating_fallback') }}
          </template>
        </p>
      </div>

      <div class="actions">
        <component
          :is="detailLinkComponent"
          class="btn"
          v-bind="detailLinkProps"
        >
          {{ detailActionLabel }}
        </component>
        <RouterLink v-if="showOrderAction" class="btn btn-primary" :to="{ name: 'customerCreateOrder', query: { productId: product.id } }">
          {{ orderActionLabel }}
        </RouterLink>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
  detailHref: {
    type: String,
    default: '',
  },
  detailLabel: {
    type: String,
    default: '',
  },
  showOrderAction: {
    type: Boolean,
    default: true,
  },
  orderLabel: {
    type: String,
    default: '',
  },
});

const { t } = useI18n();
const detailLinkComponent = computed(() => (props.detailHref ? 'a' : RouterLink));
const detailLinkProps = computed(() => (
  props.detailHref
    ? { href: props.detailHref }
    : { to: { name: 'customerProductDetails', params: { id: props.product.id } } }
));
const detailActionLabel = computed(() => props.detailLabel || t('products.details_action'));
const orderActionLabel = computed(() => props.orderLabel || t('products.order_action'));
const categoryLabel = computed(() => props.product.category?.name || props.product.category || '-');

const initials = computed(() => props.product.name
  ?.split(' ')
  .filter(Boolean)
  .slice(0, 2)
  .map((segment) => segment.charAt(0).toUpperCase())
  .join('') || 'MK');

const hasReviews = computed(() => Number(props.product.reviews_count || 0) > 0 && Number(props.product.rating_average || 0) > 0);
const ratingDisplay = computed(() => Number(props.product.rating_average || 0).toFixed(1));
const primaryPrice = computed(() => formatMoney(props.product.sale_price ?? props.product.price));
const secondaryPrice = computed(() => (
  props.product.sale_price ? formatMoney(props.product.price) : ''
));

function formatMoney(value) {
  return `${new Intl.NumberFormat(undefined, {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(Number(value || 0))} MAD`;
}
</script>
