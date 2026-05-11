<template>
  <section class="stack">
    <div class="actions">
      <RouterLink class="btn" :to="{ name: 'customerActiveOrders' }">{{ t('orders.back_to_active') }}</RouterLink>
      <RouterLink class="btn" :to="{ name: 'customerOrderHistory' }">{{ t('orders.back_to_history') }}</RouterLink>
      <RouterLink class="btn" :to="{ name: 'customerPurchasedProducts' }">{{ t('customers.purchased_products_nav') }}</RouterLink>
    </div>

    <LoadingState v-if="loading" :label="t('orders.loading_details')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <template v-else-if="order">
      <div class="customer-order-details-grid">
        <article class="ui-card stack">
          <div class="customer-order-details-hero">
            <div class="customer-order-details-hero__media">
              <img
                v-if="order.product?.main_image_url"
                :src="order.product.main_image_url"
                :alt="order.product?.name || ''"
              >
              <div v-else class="product-detail-placeholder">{{ initials }}</div>
            </div>

            <div class="stack" style="gap: 0.75rem;">
              <div class="row" style="justify-content: space-between; align-items: flex-start;">
                <div class="stack" style="gap: 0.25rem;">
                  <UiSectionHeader
                    :title="order.product?.name || t('orders.order_reference', { id: order.id })"
                    :description="t('orders.details_description')"
                  />
                  <p class="small">{{ t('orders.order_reference', { id: order.id }) }}</p>
                </div>

                <div class="stack" style="gap: 0.35rem; align-items: flex-end;">
                  <OrderStatusBadge :status="order.status" />
                  <span class="badge badge-neutral">{{ trackingStageLabel }}</span>
                </div>
              </div>

              <div class="grid grid-2">
                <div class="product-spec-card">
                  <span class="product-spec-label">{{ t('orders.created_at') }}</span>
                  <strong class="product-spec-value">{{ formatDate(order.timestamps?.created_at) }}</strong>
                </div>
                <div class="product-spec-card">
                  <span class="product-spec-label">{{ t('orders.accepted_at') }}</span>
                  <strong class="product-spec-value">{{ formatDate(order.timestamps?.accepted_at) }}</strong>
                </div>
                <div class="product-spec-card">
                  <span class="product-spec-label">{{ t('orders.tailor_label') }}</span>
                  <strong class="product-spec-value">{{ order.tailor?.name || t('customers.awaiting_tailor_assignment') }}</strong>
                </div>
                <div class="product-spec-card">
                  <span class="product-spec-label">{{ t('products.price_label') }}</span>
                  <strong class="product-spec-value">{{ formatMoney(order.financials?.total_amount) }}</strong>
                </div>
              </div>

              <OrderTimeline
                v-if="order.tracking?.timeline?.length"
                :items="order.tracking.timeline"
              />
            </div>
          </div>
        </article>

        <aside class="stack">
          <div class="ui-card stack">
            <strong>{{ t('orders.configuration_title') }}</strong>
            <div class="grid grid-2">
              <UiStatBlock
                :label="t('orders.selected_color_label')"
                :value="order.configuration?.color?.label || t('common.not_available')"
              />
              <UiStatBlock
                :label="t('orders.selected_fabric_label')"
                :value="order.configuration?.fabric?.label || t('common.not_available')"
              />
            </div>
          </div>

          <div class="ui-card stack">
            <strong>{{ t('orders.shipping_details_title') }}</strong>
            <div class="grid grid-2">
              <UiStatBlock :label="t('orders.shipping_company_label')" :value="order.shipping?.company_name || '-'" />
              <UiStatBlock :label="t('orders.delivery_type_label')" :value="deliveryTypeLabel" />
              <UiStatBlock :label="t('orders.delivery_wilaya_label')" :value="order.delivery?.work_wilaya || '-'" />
              <UiStatBlock :label="t('orders.delivery_commune_label')" :value="order.delivery?.commune || '-'" />
              <UiStatBlock :label="t('orders.delivery_neighborhood_label')" :value="order.delivery?.neighborhood || '-'" />
              <UiStatBlock :label="t('orders.delivery_location_label_label')" :value="order.delivery?.label || order.delivery?.preview || '-'" />
            </div>
          </div>
        </aside>
      </div>

      <div class="ui-card stack">
        <strong>{{ t('orders.measurements_required_title') }}</strong>
        <div class="grid grid-2">
          <UiStatBlock
            v-for="(value, key) in order.measurements || {}"
            :key="key"
            :label="String(key)"
            :value="`${value} cm`"
          />
        </div>
      </div>

      <div v-if="hasFabricInfo" class="ui-card stack">
        <strong>{{ t('orders.fabric_section_title') }}</strong>
        <div class="grid grid-2">
          <UiStatBlock :label="t('orders.fabric_type_label')" :value="order.product?.fabric_type || '-'" />
          <UiStatBlock :label="t('orders.fabric_country_label')" :value="order.product?.fabric_country || '-'" />
        </div>

        <p v-if="order.product?.fabric_description" class="small">{{ order.product.fabric_description }}</p>

        <div v-if="order.product?.fabric_image_url" class="stack" style="gap: 0.45rem;">
          <a
            :href="order.product.fabric_image_url"
            class="btn"
            target="_blank"
            rel="noopener noreferrer"
            style="width: fit-content;"
          >
            {{ t('orders.open_fabric_image') }}
          </a>
          <img
            :src="order.product.fabric_image_url"
            :alt="t('orders.fabric_image_alt', { name: order.product?.name || '' })"
            style="max-width: 240px; border-radius: 0.75rem; border: 1px solid rgba(148, 163, 184, 0.25);"
          >
        </div>
      </div>

      <div class="customer-dashboard-grid">
        <div class="ui-card stack" v-if="order.lifecycle">
          <strong>{{ t('orders.lifecycle_hints') }}</strong>
          <p class="small">{{ t('orders.customer_can_cancel', { value: booleanText(order.lifecycle.customer_can_cancel) }) }}</p>
          <p class="small">{{ t('orders.is_terminal', { value: booleanText(order.lifecycle.is_terminal) }) }}</p>
          <p class="small">{{ t('orders.allowed_actions', { value: (order.lifecycle.allowed_actions_for_customer || []).join(', ') || t('orders.none') }) }}</p>
        </div>

        <div class="ui-card stack" v-if="order.lifecycle?.customer_can_cancel">
          <strong>{{ t('orders.cancel_order_title') }}</strong>
          <UiFormField :label="t('orders.cancel_reason_label')" field-id="cancelReason">
            <textarea id="cancelReason" v-model="cancelReason" class="textarea" rows="3"></textarea>
          </UiFormField>
          <button class="btn btn-danger" :disabled="actionLoading" @click="cancelCurrentOrder">
            {{ actionLoading ? t('orders.cancelling') : t('orders.cancel_order_action') }}
          </button>
        </div>
      </div>

      <div v-if="order.review" class="ui-card stack">
        <div class="row" style="justify-content: space-between; align-items: flex-start;">
          <div class="stack" style="gap: 0.2rem;">
            <strong>{{ t('customers.review_submitted_badge') }}</strong>
            <p class="small">{{ formatDate(order.review.created_at) }}</p>
          </div>
          <span class="badge badge-warning">{{ order.review.rating }}/5</span>
        </div>
        <p class="small">{{ order.review.comment || t('customers.review_comment_empty') }}</p>
      </div>

      <div class="ui-card stack" v-else-if="canSubmitReview">
        <strong>{{ t('orders.submit_review_title') }}</strong>
        <p class="small">{{ t('customers.review_restriction_hint') }}</p>

        <UiFormField :label="t('orders.rating_label')" field-id="rating">
          <input id="rating" v-model.number="review.rating" class="input" min="1" max="5" type="number" />
        </UiFormField>

        <UiFormField :label="t('orders.comment_label')" field-id="comment">
          <textarea id="comment" v-model="review.comment" class="textarea" rows="3"></textarea>
        </UiFormField>

        <button class="btn btn-primary" :disabled="actionLoading" @click="submitOrderReview">
          {{ actionLoading ? t('orders.submitting_review') : t('orders.submit_review_action') }}
        </button>
      </div>
    </template>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import OrderStatusBadge from '@/components/orders/OrderStatusBadge.vue';
import OrderTimeline from '@/components/orders/OrderTimeline.vue';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
import { cancelOrder, fetchOrder, submitReview } from '@/services/customerOrderService';
import { getErrorMessage } from '@/services/errorMessage';
import { useRealtimeStore } from '@/stores/realtime';
import { useToast } from '@/composables/useToast';
import { useI18n } from '@/composables/useI18n';

const route = useRoute();
const realtimeStore = useRealtimeStore();
const { successToast, errorToast, warningToast } = useToast();
const { t } = useI18n();

const loading = ref(false);
const error = ref('');
const order = ref(null);

const actionLoading = ref(false);
const cancelReason = ref('');

const review = reactive({
  rating: 5,
  comment: '',
});

const trackingStageLabel = computed(() => (
  order.value?.tracking?.current_stage
    ? t(`orders.timeline_labels.${order.value.tracking.current_stage}`)
    : t('common.not_available')
));

const canSubmitReview = computed(() => Boolean(order.value?.permissions?.can_review && !order.value?.review));
const initials = computed(() => order.value?.product?.name
  ?.split(' ')
  .filter(Boolean)
  .slice(0, 2)
  .map((segment) => segment.charAt(0).toUpperCase())
  .join('') || 'MK');
const hasFabricInfo = computed(() => {
  const fabric = order.value?.product;

  return Boolean(
    fabric?.fabric_type
      || fabric?.fabric_country
      || fabric?.fabric_description
      || fabric?.fabric_image_url,
  );
});
const deliveryTypeLabel = computed(() => {
  if (order.value?.shipping?.delivery_type === 'office_pickup') {
    return t('orders.delivery_type_office_pickup');
  }

  return order.value?.shipping?.delivery_type || '-';
});

function booleanText(value) {
  return value ? t('common.yes') : t('common.no');
}

function formatDate(value) {
  if (!value) {
    return '-';
  }

  return new Date(value).toLocaleString();
}

function formatMoney(value) {
  return `${new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 }).format(Number(value || 0))} MAD`;
}

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchOrder(route.params.id);
    order.value = response.data;
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.order_details_load_failed'));
  } finally {
    loading.value = false;
  }
}

async function cancelCurrentOrder() {
  actionLoading.value = true;

  try {
    const reason = cancelReason.value || t('orders.default_cancel_reason');
    const response = await cancelOrder(route.params.id, reason);
    order.value = response.data;
    successToast(response.message || t('notifications.order_cancelled'));

    if (response?.meta?.warning) {
      warningToast(response.meta.warning, { duration: 7000 });
    }
  } catch (err) {
    errorToast(getErrorMessage(err, t('messages.order_cancel_failed')));
  } finally {
    actionLoading.value = false;
  }
}

async function submitOrderReview() {
  actionLoading.value = true;

  try {
    const response = await submitReview(route.params.id, {
      rating: Number(review.rating),
      comment: review.comment || null,
    });

    successToast(response.message || t('notifications.review_submitted'));
    await load();
  } catch (err) {
    errorToast(getErrorMessage(err, t('messages.review_submit_failed')));
  } finally {
    actionLoading.value = false;
  }
}

watch(
  () => realtimeStore.lastEvent,
  (event) => {
    if (!event || !order.value) {
      return;
    }

    if (Number(event.order?.id) === Number(order.value.id)) {
      load();
    }
  },
);

onMounted(load);
</script>
