<template>
  <section class="stack">
    <UiSectionHeader
      :title="t('tailors.dashboard_title')"
      :description="t('tailors.dashboard_description')"
    />

    <LoadingState v-if="loading" :label="t('tailors.loading_dashboard')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <div v-else class="grid grid-2">
      <UiStatBlock :label="t('tailors.current_availability_label')" :value="tailorStatusLabel(profile.status)" tone="info" />
      <UiStatBlock :label="t('tailors.active_orders_label')" :value="profile.active_orders_count ?? 0" />
    </div>

    <div class="ui-card stack">
      <div class="row" style="justify-content: space-between;">
        <h2 class="title" style="font-size: 1rem;">{{ t('tailors.incoming_offers_title') }}</h2>
        <div class="row" style="gap: 0.5rem;">
          <button class="btn" type="button" @click="realtimeStore.toggleSoundMuted()">
            {{ realtimeStore.soundMuted ? t('tailors.sound_muted') : t('tailors.sound_enabled') }}
          </button>
          <span class="badge badge-info">{{ t('tailors.unread_offers_count', { count: realtimeStore.unreadTailorOfferCount }) }}</span>
          <span class="badge badge-neutral">{{ t('tailors.offers_count', { count: realtimeStore.tailorOffers.length }) }}</span>
        </div>
      </div>

      <EmptyState v-if="realtimeStore.tailorOffers.length === 0" :message="t('tailors.no_offers')" />

      <div v-else class="stack">
        <article
          v-for="offer in realtimeStore.tailorOffers"
          :key="offer.order.id"
          class="ui-card stack"
          :class="{ 'tailor-offer-unread': offer.is_unread || offer.order?.tailor_offer?.is_unread }"
        >
          <div class="row" style="justify-content: space-between;">
            <h3 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.order_reference', { id: offer.order.id }) }}</h3>
            <span v-if="offer.is_unread || offer.order?.tailor_offer?.is_unread" class="badge badge-info">{{ t('tailors.new_offer_badge') }}</span>
            <span v-else class="badge badge-neutral">{{ t('tailors.read_badge') }}</span>
          </div>

          <p class="small">{{ t('orders.product_label') }}: {{ offer.order.product?.name || '-' }}</p>
          <p class="small">{{ t('orders.delivery_label') }}: {{ offer.order.delivery?.preview || offer.order.delivery?.work_wilaya || '-' }}</p>

          <div class="grid grid-2">
            <UiStatBlock :label="t('orders.total_amount')" :value="formatMoney(offer.order.financials?.total_amount)" />
            <UiStatBlock :label="t('orders.net_earnings')" :value="formatMoney(offer.order.financials?.tailor_net_amount)" tone="success" />
          </div>

          <div class="actions">
            <button class="btn btn-primary" :disabled="actionLoading" @click="acceptOffer(offer)">
              {{ actionLoading ? t('common.submitting') : t('tailors.accept_offer_action') }}
            </button>
            <RouterLink class="btn" :to="{ name: 'tailorOrderDetails', params: { id: offer.order.id } }">
              {{ t('common.open') }}
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
import { acceptOrder, fetchTailorOrderOffers, fetchTailorProfile } from '@/services/tailorService';
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
    await loadOffers();
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.tailor_dashboard_load_failed'));
  } finally {
    loading.value = false;
  }
}

async function loadOffers() {
  const response = await fetchTailorOrderOffers({ per_page: 20 });
  const offers = (response.data || []).map((order) => ({
    event: 'order.created',
    occurred_at: order.timestamps?.created_at,
    order,
    meta: {},
    is_unread: order.tailor_offer?.is_unread,
  }));

  realtimeStore.setOffers(offers, response.meta?.unread_count ?? null);
}

async function acceptOffer(offer) {
  actionLoading.value = true;

  try {
    const response = await acceptOrder(offer.order.id, offer.meta?.notified_tailor_ids || []);
    successToast(response.message || t('notifications.tailor_order_accepted'));
    realtimeStore.removeOffer(offer.order.id);
    await load();
  } catch (err) {
    errorToast(getErrorMessage(err, t('messages.tailor_accept_order_failed')));

    if (err.status === 409) {
      realtimeStore.removeOffer(offer.order.id);
    }
  } finally {
    actionLoading.value = false;
  }
}

function formatMoney(value) {
  return `${new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 }).format(Number(value || 0))} MAD`;
}

function tailorStatusLabel(status) {
  if (!status) {
    return t('tailors.status_unknown');
  }

  const key = `tailors.status_${status}`;
  const label = t(key);

  return label === key ? status : label;
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
