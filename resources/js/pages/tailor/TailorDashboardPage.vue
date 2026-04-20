<template>
  <section class="stack">
    <UiSectionHeader
      title="Tailor Dashboard"
      description="Realtime offer feed and profile summary for current operations."
    />

    <LoadingState v-if="loading" label="Loading tailor dashboard..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <div v-else class="grid grid-2">
      <UiStatBlock label="Current Availability" :value="profile.status || '-'" tone="info" />
      <UiStatBlock label="Active Orders" :value="profile.active_orders_count ?? 0" />
    </div>

    <div class="ui-card stack">
      <div class="row" style="justify-content: space-between;">
        <h2 class="title" style="font-size: 1rem;">Incoming Nearby Orders (Realtime)</h2>
        <span class="badge badge-neutral">{{ realtimeStore.tailorOffers.length }} offers</span>
      </div>

      <EmptyState v-if="realtimeStore.tailorOffers.length === 0" message="No realtime offers right now." />

      <div v-else class="stack">
        <article v-for="offer in realtimeStore.tailorOffers" :key="offer.order.id" class="ui-card stack">
          <div class="row" style="justify-content: space-between;">
            <h3 class="title" style="font-size: 1rem; margin: 0;">Order #{{ offer.order.id }}</h3>
            <span class="badge badge-info">{{ offer.event }}</span>
          </div>

          <p class="small">Product: {{ offer.order.product?.name || '-' }}</p>
          <p class="small">Delivery: {{ offer.order.delivery?.latitude }}, {{ offer.order.delivery?.longitude }}</p>

          <div class="actions">
            <button class="btn btn-primary" :disabled="actionLoading" @click="acceptOffer(offer)">
              {{ actionLoading ? 'Submitting...' : 'Accept' }}
            </button>
            <RouterLink class="btn" :to="{ name: 'tailorOrderDetails', params: { id: offer.order.id } }">
              Open
            </RouterLink>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
import { acceptOrder, fetchTailorProfile } from '@/services/tailorService';
import { getErrorMessage } from '@/services/errorMessage';
import { useRealtimeStore } from '@/stores/realtime';
import { useToast } from '@/composables/useToast';
import { useI18n } from '@/composables/useI18n';

const realtimeStore = useRealtimeStore();
const { successToast, errorToast } = useToast();
const { t } = useI18n();

const loading = ref(false);
const error = ref('');
const actionLoading = ref(false);

const profile = reactive({
  status: '-',
  active_orders_count: 0,
});

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchTailorProfile();
    Object.assign(profile, response.data || {});
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load tailor dashboard data.');
  } finally {
    loading.value = false;
  }
}

async function acceptOffer(offer) {
  actionLoading.value = true;

  try {
    const response = await acceptOrder(offer.order.id, offer.meta?.notified_tailor_ids || []);
    successToast(response.message || t('notifications.tailor_order_accepted'));
    realtimeStore.removeOffer(offer.order.id);
    await load();
  } catch (err) {
    errorToast(getErrorMessage(err, 'Unable to accept this order.'));

    if (err.status === 409) {
      realtimeStore.removeOffer(offer.order.id);
    }
  } finally {
    actionLoading.value = false;
  }
}

watch(
  () => realtimeStore.lastEvent,
  (event) => {
    if (!event) {
      return;
    }

    if (event.event === 'order.accepted' || event.event === 'order.cancelled_by_customer') {
      load();
    }
  },
);

onMounted(load);
</script>
