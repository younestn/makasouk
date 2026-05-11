<template>
  <section class="stack">
    <div class="actions">
      <RouterLink class="btn" :to="{ name: 'customerActiveOrders' }">{{ t('orders.back_to_active') }}</RouterLink>
      <RouterLink class="btn" :to="{ name: 'customerOrderHistory' }">{{ t('orders.back_to_history') }}</RouterLink>
    </div>

    <LoadingState v-if="loading" :label="t('orders.loading_details')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <template v-else-if="order">
      <div class="ui-card stack">
        <div class="row" style="justify-content: space-between; align-items: flex-start;">
          <UiSectionHeader :title="t('orders.order_reference', { id: order.id })" :description="t('orders.details_description')" />
          <OrderStatusBadge :status="order.status" />
        </div>

        <div class="grid grid-2">
          <UiStatBlock :label="t('orders.created_at')" :value="formatDate(order.timestamps?.created_at)" />
          <UiStatBlock :label="t('orders.accepted_at')" :value="formatDate(order.timestamps?.accepted_at)" />
          <UiStatBlock :label="t('orders.product_label')" :value="order.product?.name || '-'" />
          <UiStatBlock :label="t('orders.tailor_label')" :value="order.tailor?.name || '-'" />
        </div>

        <div class="ui-card stack">
          <h2 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.configuration_title') }}</h2>
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
          <h2 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.shipping_details_title') }}</h2>
          <div class="grid grid-2">
            <UiStatBlock :label="t('orders.shipping_company_label')" :value="order.shipping?.company_name || '-'" />
            <UiStatBlock :label="t('orders.delivery_type_label')" :value="deliveryTypeLabel" />
            <UiStatBlock :label="t('orders.delivery_wilaya_label')" :value="order.delivery?.work_wilaya || '-'" />
            <UiStatBlock :label="t('orders.delivery_commune_label')" :value="order.delivery?.commune || '-'" />
            <UiStatBlock :label="t('orders.delivery_neighborhood_label')" :value="order.delivery?.neighborhood || '-'" />
            <UiStatBlock :label="t('orders.delivery_location_label_label')" :value="order.delivery?.label || order.delivery?.preview || '-'" />
            <UiStatBlock :label="t('orders.delivery_phone_label')" :value="order.shipping?.phone || '-'" />
            <UiStatBlock :label="t('orders.delivery_email_label')" :value="order.shipping?.email || '-'" />
          </div>
        </div>

        <div v-if="hasFabricInfo" class="ui-card stack" style="border: 1px solid rgba(148, 163, 184, 0.25);">
          <h2 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.fabric_section_title') }}</h2>

          <div class="grid grid-2">
            <UiStatBlock :label="t('orders.fabric_type_label')" :value="order.product?.fabric_type || '-'" />
            <UiStatBlock :label="t('orders.fabric_country_label')" :value="order.product?.fabric_country || '-'" />
          </div>

          <p v-if="order.product?.fabric_description" class="small" style="margin: 0;">
            {{ order.product.fabric_description }}
          </p>

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
              style="max-width: 240px; border-radius: 0.6rem; border: 1px solid rgba(148, 163, 184, 0.25);"
            />
          </div>
        </div>

        <div class="ui-card stack" v-if="order.lifecycle">
          <p class="label">{{ t('orders.lifecycle_hints') }}</p>
          <p class="small">{{ t('orders.customer_can_cancel', { value: booleanText(order.lifecycle.customer_can_cancel) }) }}</p>
          <p class="small">{{ t('orders.is_terminal', { value: booleanText(order.lifecycle.is_terminal) }) }}</p>
          <p class="small">{{ t('orders.allowed_actions', { value: (order.lifecycle.allowed_actions_for_customer || []).join(', ') || t('orders.none') }) }}</p>
        </div>
      </div>

      <div class="ui-card stack" v-if="order.lifecycle?.customer_can_cancel">
        <h2 class="title" style="font-size: 1rem;">{{ t('orders.cancel_order_title') }}</h2>
        <div>
          <label class="label" for="cancelReason">{{ t('orders.cancel_reason_label') }}</label>
          <textarea id="cancelReason" v-model="cancelReason" class="textarea" rows="3"></textarea>
        </div>
        <button class="btn btn-danger" :disabled="actionLoading" @click="cancelCurrentOrder">
          {{ actionLoading ? t('orders.cancelling') : t('orders.cancel_order_action') }}
        </button>
      </div>

      <div class="ui-card stack" v-if="canSubmitReview">
        <h2 class="title" style="font-size: 1rem;">{{ t('orders.submit_review_title') }}</h2>

        <div>
          <label class="label" for="rating">{{ t('orders.rating_label') }}</label>
          <input id="rating" v-model.number="review.rating" class="input" min="1" max="5" type="number" />
        </div>

        <div>
          <label class="label" for="comment">{{ t('orders.comment_label') }}</label>
          <textarea id="comment" v-model="review.comment" class="textarea" rows="3"></textarea>
        </div>

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

const canSubmitReview = computed(() => order.value?.status === 'completed' && !order.value?.review);
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
