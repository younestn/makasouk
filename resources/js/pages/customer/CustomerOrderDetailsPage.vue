<template>
  <section class="stack">
    <div class="actions">
      <RouterLink class="btn" :to="{ name: 'customerActiveOrders' }">Back to Active</RouterLink>
      <RouterLink class="btn" :to="{ name: 'customerOrderHistory' }">Back to History</RouterLink>
    </div>

    <LoadingState v-if="loading" label="Loading order details..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <template v-else-if="order">
      <div class="ui-card stack">
        <div class="row" style="justify-content: space-between; align-items: flex-start;">
          <UiSectionHeader :title="`Order #${order.id}`" description="Operational details and lifecycle guidance." />
          <OrderStatusBadge :status="order.status" />
        </div>

        <div class="grid grid-2">
          <UiStatBlock label="Created At" :value="formatDate(order.timestamps?.created_at)" />
          <UiStatBlock label="Accepted At" :value="formatDate(order.timestamps?.accepted_at)" />
          <UiStatBlock label="Product" :value="order.product?.name || '-'" />
          <UiStatBlock label="Tailor" :value="order.tailor?.name || '-'" />
        </div>

        <div class="ui-card" v-if="order.lifecycle">
          <p class="label">Lifecycle hints</p>
          <p class="small">Customer can cancel: {{ order.lifecycle.customer_can_cancel ? 'Yes' : 'No' }}</p>
          <p class="small">Terminal state: {{ order.lifecycle.is_terminal ? 'Yes' : 'No' }}</p>
          <p class="small">Allowed customer actions: {{ (order.lifecycle.allowed_actions_for_customer || []).join(', ') || 'none' }}</p>
        </div>
      </div>

      <div v-if="actionError" class="alert alert-danger">{{ actionError }}</div>
      <div v-if="actionMessage" class="alert alert-info">{{ actionMessage }}</div>

      <div class="ui-card stack" v-if="order.lifecycle?.customer_can_cancel">
        <h2 class="title" style="font-size: 1rem;">Cancel Order</h2>
        <div>
          <label class="label" for="cancelReason">Reason</label>
          <textarea id="cancelReason" v-model="cancelReason" class="textarea" rows="3"></textarea>
        </div>
        <button class="btn btn-danger" :disabled="actionLoading" @click="cancelCurrentOrder">
          {{ actionLoading ? 'Cancelling...' : 'Cancel Order' }}
        </button>
      </div>

      <div class="ui-card stack" v-if="canSubmitReview">
        <h2 class="title" style="font-size: 1rem;">Submit Review</h2>

        <div>
          <label class="label" for="rating">Rating (1-5)</label>
          <input id="rating" v-model.number="review.rating" class="input" min="1" max="5" type="number" />
        </div>

        <div>
          <label class="label" for="comment">Comment (optional)</label>
          <textarea id="comment" v-model="review.comment" class="textarea" rows="3"></textarea>
        </div>

        <button class="btn btn-primary" :disabled="actionLoading" @click="submitOrderReview">
          {{ actionLoading ? 'Submitting...' : 'Submit Review' }}
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

const route = useRoute();
const realtimeStore = useRealtimeStore();

const loading = ref(false);
const error = ref('');
const order = ref(null);

const actionLoading = ref(false);
const actionMessage = ref('');
const actionError = ref('');
const cancelReason = ref('Changed plan');

const review = reactive({
  rating: 5,
  comment: '',
});

const canSubmitReview = computed(() => {
  return order.value?.status === 'completed' && !order.value?.review;
});

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
    error.value = getErrorMessage(err, 'Failed to load order details.');
  } finally {
    loading.value = false;
  }
}

async function cancelCurrentOrder() {
  actionLoading.value = true;
  actionError.value = '';
  actionMessage.value = '';

  try {
    const response = await cancelOrder(route.params.id, cancelReason.value || 'Cancelled from web client');
    order.value = response.data;
    actionMessage.value = response.message || 'Order cancelled.';
  } catch (err) {
    actionError.value = getErrorMessage(err, 'Failed to cancel order.');
  } finally {
    actionLoading.value = false;
  }
}

async function submitOrderReview() {
  actionLoading.value = true;
  actionError.value = '';
  actionMessage.value = '';

  try {
    const response = await submitReview(route.params.id, {
      rating: Number(review.rating),
      comment: review.comment || null,
    });

    actionMessage.value = response.message || 'Review submitted.';
    await load();
  } catch (err) {
    actionError.value = getErrorMessage(err, 'Failed to submit review.');
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
