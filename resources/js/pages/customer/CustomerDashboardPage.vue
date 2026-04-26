<template>
  <section class="stack">
    <UiSectionHeader
      :title="t('customers.dashboard_title')"
      :description="t('customers.dashboard_description')"
    />

    <LoadingState v-if="loading" :label="t('customers.dashboard_loading_label')" :hint="t('customers.dashboard_loading_hint')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <div v-else class="grid grid-2">
      <UiStatBlock :label="t('customers.dashboard_active_orders_label')" :value="stats.active" :hint="t('customers.dashboard_active_orders_hint')" tone="info" />
      <UiStatBlock :label="t('customers.dashboard_history_orders_label')" :value="stats.history" :hint="t('customers.dashboard_history_orders_hint')" />
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
import { fetchActiveOrders, fetchOrderHistory } from '@/services/customerOrderService';
import { getErrorMessage } from '@/services/errorMessage';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
const loading = ref(false);
const error = ref('');
const stats = reactive({ active: 0, history: 0 });

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const [activeResponse, historyResponse] = await Promise.all([
      fetchActiveOrders({ per_page: 1 }),
      fetchOrderHistory({ per_page: 1 }),
    ]);

    stats.active = activeResponse.meta?.total || 0;
    stats.history = historyResponse.meta?.total || 0;
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.customer_dashboard_load_failed'));
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
