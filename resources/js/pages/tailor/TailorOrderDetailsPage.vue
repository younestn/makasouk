<template>
  <section class="stack">
    <div class="actions">
      <RouterLink class="btn" :to="{ name: 'tailorActiveOrders' }">{{ t('tailors.back_to_active_orders') }}</RouterLink>
      <RouterLink class="btn" :to="{ name: 'tailorDashboard' }">{{ t('common.dashboard') }}</RouterLink>
    </div>

    <LoadingState v-if="loading" :label="t('tailors.loading_order_details')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <template v-else-if="order">
      <div class="ui-card stack">
        <div class="row" style="justify-content: space-between; align-items: flex-start;">
          <UiSectionHeader :title="t('orders.order_reference', { id: order.id })" :description="t('tailors.order_details_description')" />
          <div class="stack" style="gap: 0.35rem; align-items: flex-end;">
            <OrderStatusBadge :status="order.status" />
            <span class="badge badge-neutral">{{ trackingStageLabel }}</span>
          </div>
        </div>

        <div class="grid grid-2">
          <UiStatBlock :label="t('orders.product_label')" :value="order.product?.name || '-'" />
          <UiStatBlock :label="t('orders.customer_label')" :value="order.customer?.name || '-'" />
          <UiStatBlock :label="t('orders.created_at')" :value="formatDate(order.timestamps?.created_at)" />
          <UiStatBlock :label="t('orders.accepted_at')" :value="formatDate(order.timestamps?.accepted_at)" />
        </div>

        <OrderTimeline
          v-if="order.tracking?.timeline?.length"
          :items="order.tracking.timeline"
        />

        <div class="ui-card stack" style="border: 1px solid rgba(184, 135, 45, 0.35); background: rgba(184, 135, 45, 0.08);">
          <h2 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.financial_breakdown_title') }}</h2>
          <div class="grid grid-4">
            <UiStatBlock :label="t('orders.total_amount')" :value="formatMoney(order.financials?.total_amount)" />
            <UiStatBlock :label="t('orders.shipping_amount')" :value="formatMoney(order.financials?.shipping_amount)" />
            <UiStatBlock :label="t('orders.platform_commission')" :value="formatMoney(order.financials?.platform_commission_amount)" />
            <UiStatBlock :label="t('orders.net_earnings')" :value="formatMoney(order.financials?.tailor_net_amount)" tone="success" />
          </div>
        </div>

        <div class="ui-card stack" style="border: 1px solid rgba(148, 163, 184, 0.25);">
          <h2 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.shipping_details_title') }}</h2>
          <p v-if="order.delivery?.is_limited" class="small">{{ t('orders.shipping_limited_notice') }}</p>
          <div class="grid grid-2">
            <UiStatBlock :label="t('orders.delivery_wilaya_label')" :value="order.delivery?.work_wilaya || '-'" />
            <UiStatBlock :label="t('orders.delivery_location_label_label')" :value="order.delivery?.label || order.delivery?.preview || '-'" />
            <UiStatBlock :label="t('orders.delivery_latitude_label')" :value="order.delivery?.latitude ?? '-'" />
            <UiStatBlock :label="t('orders.delivery_longitude_label')" :value="order.delivery?.longitude ?? '-'" />
          </div>
        </div>

        <div class="ui-card stack" style="border: 1px solid rgba(148, 163, 184, 0.25);">
          <h2 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.measurements_required_title') }}</h2>
          <div class="grid grid-2">
            <UiStatBlock
              v-for="(value, key) in order.measurements || {}"
              :key="key"
              :label="String(key)"
              :value="`${value} cm`"
            />
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

        <div class="ui-card stack" style="border: 1px solid rgba(148, 163, 184, 0.25);">
          <h2 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.pattern_section_title') }}</h2>
          <p v-if="order.fulfillment?.pattern_locked" class="small">{{ t('orders.pattern_locked_notice') }}</p>
          <div v-else-if="availablePatternFiles.length" class="stack" style="gap: 0.55rem;">
            <a
              v-for="(patternUrl, index) in availablePatternFiles"
              :key="`${patternUrl}-${index}`"
              :href="patternUrl"
              class="btn"
              target="_blank"
              rel="noopener noreferrer"
              style="width: fit-content;"
            >
              {{ availablePatternFiles.length > 1 ? `${t('orders.open_pattern_file')} ${index + 1}` : t('orders.open_pattern_file') }}
            </a>
          </div>
          <p v-else class="small">{{ t('orders.pattern_not_available') }}</p>
        </div>
      </div>

      <div class="ui-card stack" v-if="canAccept">
        <h2 class="title" style="font-size: 1rem;">{{ t('tailors.accept_order_title') }}</h2>
        <p class="small">{{ t('tailors.accept_order_description') }}</p>
        <button class="btn btn-primary" :disabled="actionLoading" @click="acceptCurrentOrder">
          {{ actionLoading ? t('common.submitting') : t('tailors.accept_order_action') }}
        </button>
      </div>

      <div class="ui-card stack" v-if="canAccept">
        <h2 class="title" style="font-size: 1rem;">{{ t('tailors.decision_title') }}</h2>
        <UiFormField :label="t('tailors.reject_reason_label')" field-id="decline-reason">
          <select id="decline-reason" v-model="declineReason" class="select">
            <option value="unavailable">{{ t('tailors.reason_unavailable') }}</option>
            <option value="workload_full">{{ t('tailors.reason_workload_full') }}</option>
            <option value="measurements_unclear">{{ t('tailors.reason_measurements_unclear') }}</option>
            <option value="pricing_not_suitable">{{ t('tailors.reason_pricing_not_suitable') }}</option>
            <option value="shipping_too_far">{{ t('tailors.reason_shipping_too_far') }}</option>
            <option value="other">{{ t('tailors.reason_other') }}</option>
          </select>
        </UiFormField>
        <UiFormField :label="t('tailors.reject_note_label')" field-id="decline-note">
          <textarea id="decline-note" v-model="declineNote" class="textarea" rows="3"></textarea>
        </UiFormField>
        <div class="actions">
          <button class="btn btn-danger" :disabled="actionLoading" @click="declineCurrentOrder">
            {{ t('tailors.reject_offer_action') }}
          </button>
          <button class="btn" :disabled="actionLoading" @click="notMySpecialtyCurrentOrder">
            {{ t('tailors.not_my_specialty_action') }}
          </button>
        </div>
      </div>

      <div class="customer-dashboard-grid" v-if="nextStatusOptions.length > 0 || trackingStageOptions.length > 0">
        <div class="ui-card stack" v-if="nextStatusOptions.length > 0">
          <h2 class="title" style="font-size: 1rem;">{{ t('tailors.update_status_title') }}</h2>
          <select v-model="nextStatus" class="select">
            <option v-for="status in nextStatusOptions" :key="status" :value="status">{{ status }}</option>
          </select>
          <button class="btn btn-primary" :disabled="actionLoading || !nextStatus" @click="updateCurrentStatus">
            {{ actionLoading ? t('tailors.updating_status') : t('tailors.update_status_action') }}
          </button>
        </div>

        <div class="ui-card stack" v-if="trackingStageOptions.length > 0">
          <h2 class="title" style="font-size: 1rem;">{{ t('tailors.update_tracking_title') }}</h2>
          <p class="small">{{ t('tailors.update_tracking_description') }}</p>
          <select v-model="trackingStage" class="select">
            <option v-for="stage in trackingStageOptions" :key="stage" :value="stage">
              {{ t(`orders.timeline_labels.${stage}`) }}
            </option>
          </select>
          <UiFormField :label="t('tailors.update_tracking_note_label')" field-id="tracking-description">
            <textarea id="tracking-description" v-model="trackingDescription" class="textarea" rows="3"></textarea>
          </UiFormField>
          <button class="btn btn-primary" :disabled="actionLoading || !trackingStage" @click="updateCurrentTrackingStage">
            {{ actionLoading ? t('tailors.updating_status') : t('tailors.update_tracking_action') }}
          </button>
        </div>
      </div>

      <div class="ui-card stack" v-if="order.lifecycle?.tailor_can_cancel">
        <h2 class="title" style="font-size: 1rem;">{{ t('tailors.cancel_order_title') }}</h2>
        <textarea v-model="cancelReason" class="textarea" rows="3"></textarea>
        <button class="btn btn-danger" :disabled="actionLoading" @click="cancelCurrentOrder">
          {{ actionLoading ? t('orders.cancelling') : t('tailors.cancel_order_action') }}
        </button>
      </div>
    </template>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import OrderStatusBadge from '@/components/orders/OrderStatusBadge.vue';
import OrderTimeline from '@/components/orders/OrderTimeline.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
import {
  acceptOrder,
  cancelTailorOrder,
  declineOrderOffer,
  fetchTailorOrder,
  markOrderNotMySpecialty,
  updateOrderStatus,
  updateOrderTrackingStage,
} from '@/services/tailorService';
import { getErrorMessage } from '@/services/errorMessage';
import { useRealtimeStore } from '@/stores/realtime';
import { useToast } from '@/composables/useToast';
import { useI18n } from '@/composables/useI18n';

const route = useRoute();
const realtimeStore = useRealtimeStore();
const { successToast, errorToast } = useToast();
const { t } = useI18n();

const loading = ref(false);
const error = ref('');
const order = ref(null);
const source = ref('');

const actionLoading = ref(false);
const nextStatus = ref('');
const trackingStage = ref('');
const trackingDescription = ref('');
const cancelReason = ref('');
const declineReason = ref('unavailable');
const declineNote = ref('');

const nextStatusOptions = computed(() => order.value?.lifecycle?.allowed_next_statuses_for_tailor || []);
const trackingStageOptions = computed(() => order.value?.tracking?.available_stage_updates_for_tailor || []);
const trackingStageLabel = computed(() => (
  order.value?.tracking?.current_stage
    ? t(`orders.timeline_labels.${order.value.tracking.current_stage}`)
    : t('common.not_available')
));
const availablePatternFiles = computed(() => {
  const files = order.value?.fulfillment?.pattern_file_urls;

  if (Array.isArray(files) && files.length) {
    return files;
  }

  return order.value?.fulfillment?.pattern_file_url ? [order.value.fulfillment.pattern_file_url] : [];
});
const hasFabricInfo = computed(() => {
  const fabric = order.value?.product;

  return Boolean(
    fabric?.fabric_type
      || fabric?.fabric_country
      || fabric?.fabric_description
      || fabric?.fabric_image_url,
  );
});

const canAccept = computed(() => {
  if (!order.value) {
    return false;
  }

  return !order.value.tailor_id
    && ['unread', 'read'].includes(order.value.tailor_offer?.status || (source.value === 'offer' ? 'read' : ''));
});

function formatDate(value) {
  if (!value) {
    return '-';
  }

  return new Date(value).toLocaleString();
}

function formatMoney(value) {
  return `${new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 }).format(Number(value || 0))} MAD`;
}

function findOrderFromOffers(orderId) {
  return realtimeStore.tailorOffers.find((item) => Number(item.order.id) === Number(orderId));
}

function syncActionDefaults() {
  nextStatus.value = nextStatusOptions.value[0] || '';
  trackingStage.value = trackingStageOptions.value[0] || '';
  trackingDescription.value = '';
}

async function load() {
  loading.value = true;
  error.value = '';

  const orderId = Number(route.params.id);

  try {
    const offer = findOrderFromOffers(orderId);

    if (offer) {
      order.value = offer.order;
      source.value = 'offer';
      syncActionDefaults();
      return;
    }

    const response = await fetchTailorOrder(orderId);

    order.value = response.data;
    source.value = 'api';
    realtimeStore.markOfferRead(orderId);
    syncActionDefaults();
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.tailor_order_details_load_failed'));
  } finally {
    loading.value = false;
  }
}

async function declineCurrentOrder() {
  if (!order.value) {
    return;
  }

  actionLoading.value = true;

  try {
    const response = await declineOrderOffer(order.value.id, {
      reason: declineReason.value,
      note: declineNote.value || null,
    });
    order.value = response.data;
    realtimeStore.removeOffer(order.value.id);
    successToast(response.message || t('notifications.tailor_offer_rejected'));
  } catch (err) {
    errorToast(getErrorMessage(err, t('messages.tailor_decline_order_failed')));
  } finally {
    actionLoading.value = false;
  }
}

async function notMySpecialtyCurrentOrder() {
  if (!order.value) {
    return;
  }

  actionLoading.value = true;

  try {
    const response = await markOrderNotMySpecialty(order.value.id, {
      note: declineNote.value || null,
    });
    order.value = response.data;
    realtimeStore.removeOffer(order.value.id);
    successToast(response.message || t('notifications.tailor_offer_not_my_specialty'));
  } catch (err) {
    errorToast(getErrorMessage(err, t('messages.tailor_not_my_specialty_failed')));
  } finally {
    actionLoading.value = false;
  }
}

async function acceptCurrentOrder() {
  if (!order.value) {
    return;
  }

  actionLoading.value = true;

  try {
    const offer = findOrderFromOffers(order.value.id);
    const response = await acceptOrder(order.value.id, offer?.meta?.notified_tailor_ids || []);

    order.value = response.data;
    source.value = 'api';
    realtimeStore.removeOffer(order.value.id);
    syncActionDefaults();
    successToast(response.message || t('notifications.tailor_order_accepted'));
  } catch (err) {
    errorToast(getErrorMessage(err, t('messages.tailor_accept_order_failed')));
  } finally {
    actionLoading.value = false;
  }
}

async function updateCurrentStatus() {
  if (!order.value || !nextStatus.value) {
    return;
  }

  actionLoading.value = true;

  try {
    const response = await updateOrderStatus(order.value.id, nextStatus.value);
    order.value = response.data;
    syncActionDefaults();
    successToast(response.message || t('notifications.tailor_status_updated'));
  } catch (err) {
    errorToast(getErrorMessage(err, t('messages.tailor_update_status_failed')));
  } finally {
    actionLoading.value = false;
  }
}

async function updateCurrentTrackingStage() {
  if (!order.value || !trackingStage.value) {
    return;
  }

  actionLoading.value = true;

  try {
    const response = await updateOrderTrackingStage(order.value.id, {
      stage: trackingStage.value,
      description: trackingDescription.value || null,
    });

    order.value = response.data;
    syncActionDefaults();
    successToast(response.message || t('notifications.tailor_status_updated'));
  } catch (err) {
    errorToast(getErrorMessage(err, t('messages.tailor_update_status_failed')));
  } finally {
    actionLoading.value = false;
  }
}

async function cancelCurrentOrder() {
  if (!order.value) {
    return;
  }

  actionLoading.value = true;

  try {
    const reason = cancelReason.value || t('orders.default_cancel_reason');
    const response = await cancelTailorOrder(order.value.id, reason);
    order.value = response.data;
    successToast(response.message || t('notifications.tailor_order_cancelled'));
  } catch (err) {
    errorToast(getErrorMessage(err, t('messages.tailor_cancel_order_failed')));
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
