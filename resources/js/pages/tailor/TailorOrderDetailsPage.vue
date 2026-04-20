<template>
  <section class="stack">
    <div class="actions">
      <RouterLink class="btn" :to="{ name: 'tailorActiveOrders' }">Back to Active Orders</RouterLink>
      <RouterLink class="btn" :to="{ name: 'tailorDashboard' }">{{ t('common.dashboard') }}</RouterLink>
    </div>

    <LoadingState v-if="loading" label="Loading tailor order details..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <template v-else-if="order">
      <div class="ui-card stack">
        <div class="row" style="justify-content: space-between; align-items: flex-start;">
          <UiSectionHeader :title="`Order #${order.id}`" description="Tailor-side lifecycle actions" />
          <OrderStatusBadge :status="order.status" />
        </div>

        <div class="grid grid-2">
          <UiStatBlock label="Product" :value="order.product?.name || '-'" />
          <UiStatBlock label="Customer" :value="order.customer?.name || '-'" />
          <UiStatBlock label="Created" :value="formatDate(order.timestamps?.created_at)" />
          <UiStatBlock label="Accepted" :value="formatDate(order.timestamps?.accepted_at)" />
        </div>
      </div>

      <div class="ui-card stack" v-if="canAccept">
        <h2 class="title" style="font-size: 1rem;">Accept Order</h2>
        <p class="small">This order came from realtime nearby notifications.</p>
        <button class="btn btn-primary" :disabled="actionLoading" @click="acceptCurrentOrder">
          {{ actionLoading ? 'Submitting...' : 'Accept Order' }}
        </button>
      </div>

      <div class="ui-card stack" v-if="nextStatusOptions.length > 0">
        <h2 class="title" style="font-size: 1rem;">Update Status</h2>
        <select v-model="nextStatus" class="select">
          <option v-for="status in nextStatusOptions" :key="status" :value="status">{{ status }}</option>
        </select>
        <button class="btn btn-primary" :disabled="actionLoading || !nextStatus" @click="updateCurrentStatus">
          {{ actionLoading ? 'Updating...' : 'Update Status' }}
        </button>
      </div>

      <div class="ui-card stack" v-if="order.lifecycle?.tailor_can_cancel">
        <h2 class="title" style="font-size: 1rem;">Cancel Order</h2>
        <textarea v-model="cancelReason" class="textarea" rows="3"></textarea>
        <button class="btn btn-danger" :disabled="actionLoading" @click="cancelCurrentOrder">
          {{ actionLoading ? 'Cancelling...' : 'Cancel as Tailor' }}
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
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
import {
  acceptOrder,
  cancelTailorOrder,
  fetchTailorOrder,
  updateOrderStatus,
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
const cancelReason = ref('Unable to continue this order');

const nextStatusOptions = computed(() => order.value?.lifecycle?.allowed_next_statuses_for_tailor || []);

const canAccept = computed(() => {
  if (!order.value) {
    return false;
  }

  return !order.value.tailor_id && source.value === 'offer';
});

function formatDate(value) {
  if (!value) {
    return '-';
  }

  return new Date(value).toLocaleString();
}

function findOrderFromOffers(orderId) {
  return realtimeStore.tailorOffers.find((item) => Number(item.order.id) === Number(orderId));
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
      nextStatus.value = '';
      return;
    }

    const response = await fetchTailorOrder(orderId);

    order.value = response.data;
    source.value = 'api';
    nextStatus.value = nextStatusOptions.value[0] || '';
  } catch (err) {
    error.value = getErrorMessage(err, 'Unable to load tailor order details.');
  } finally {
    loading.value = false;
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
    nextStatus.value = nextStatusOptions.value[0] || '';
    successToast(response.message || t('notifications.tailor_order_accepted'));
  } catch (err) {
    errorToast(getErrorMessage(err, 'Failed to accept order.'));
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
    nextStatus.value = nextStatusOptions.value[0] || '';
    successToast(response.message || t('notifications.tailor_status_updated'));
  } catch (err) {
    errorToast(getErrorMessage(err, 'Failed to update order status.'));
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
    const response = await cancelTailorOrder(order.value.id, cancelReason.value || 'Cancelled from web client');
    order.value = response.data;
    successToast(response.message || t('notifications.tailor_order_cancelled'));
  } catch (err) {
    errorToast(getErrorMessage(err, 'Failed to cancel order.'));
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
