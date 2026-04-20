<template>
  <div>
    <div v-if="realtimeStore.lastError" class="container" style="padding-top: 0.75rem;">
      <div class="alert alert-warning">
        Realtime warning: {{ realtimeStore.lastError }}
      </div>
    </div>

    <RouterView />
  </div>
</template>

<script setup>
import { RouterView } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useRealtimeStore } from '@/stores/realtime';
import { useRealtimeBootstrap } from '@/composables/useRealtimeBootstrap';

const authStore = useAuthStore();
const realtimeStore = useRealtimeStore();

useRealtimeBootstrap(authStore, realtimeStore);
</script>
