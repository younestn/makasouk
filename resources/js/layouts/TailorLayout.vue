<template>
  <div>
    <header class="navbar">
      <div class="container">
        <div class="stack" style="gap: 0.2rem;">
          <p class="app-layout-title">{{ t('layout.tailor_workspace') }}</p>
          <p class="app-layout-subtitle">{{ authStore.user?.name }} · {{ authStore.user?.email }}</p>
        </div>

        <nav class="nav-links" :aria-label="t('tailors.navigation_aria')">
          <RouterLink class="nav-link" :to="{ name: 'tailorDashboard' }">{{ t('common.dashboard') }}</RouterLink>
          <RouterLink class="nav-link" :to="{ name: 'tailorActiveOrders' }">{{ t('common.active_orders') }}</RouterLink>
          <RouterLink class="nav-link" :to="{ name: 'tailorAvailability' }">{{ t('common.availability') }}</RouterLink>
          <RouterLink class="nav-link" :to="{ name: 'tailorProfile' }">{{ t('common.profile') }}</RouterLink>
        </nav>

        <div class="actions">
          <span class="status-chip" :class="realtimeStore.isConnected ? 'status-chip--connected' : 'status-chip--pending'">
            <span class="status-dot"></span>
            {{ realtimeStore.isConnected ? t('layout.realtime_connected') : t('layout.realtime_pending') }}
          </span>
          <UiLocaleSwitcher />
          <a class="btn" href="/">{{ t('common.public_site') }}</a>
          <button class="btn" type="button" @click="logout">{{ t('common.logout') }}</button>
        </div>
      </div>
    </header>

    <main class="container page">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useRealtimeStore } from '@/stores/realtime';
import UiLocaleSwitcher from '@/components/ui/UiLocaleSwitcher.vue';
import { useI18n } from '@/composables/useI18n';
import { useToast } from '@/composables/useToast';

const router = useRouter();
const authStore = useAuthStore();
const realtimeStore = useRealtimeStore();
const { t } = useI18n();
const { infoToast } = useToast();

async function logout() {
  await authStore.logout();
  infoToast(t('notifications.logout_success'));
  router.push({ name: 'login' });
}
</script>
