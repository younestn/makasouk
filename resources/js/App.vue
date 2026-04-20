<template>
  <div>
    <div v-if="realtimeStore.lastError" class="container" style="padding-top: 0.75rem;">
      <div class="alert alert-warning">
        {{ t('layout.realtime_warning', { message: realtimeStore.lastError }) }}
      </div>
    </div>

    <UiToast />
    <RouterView />
  </div>
</template>

<script setup>
import { RouterView } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useRealtimeStore } from '@/stores/realtime';
import { useRealtimeBootstrap } from '@/composables/useRealtimeBootstrap';
import { useI18n } from '@/composables/useI18n';
import UiToast from '@/components/ui/UiToast.vue';

const authStore = useAuthStore();
const realtimeStore = useRealtimeStore();
const { t } = useI18n();

useRealtimeBootstrap(authStore, realtimeStore);
</script>
