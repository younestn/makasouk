<template>
  <div class="customer-shell" :class="{ 'is-sidebar-open': sidebarOpen }">
    <aside class="customer-shell__sidebar">
      <div class="customer-shell__brand">
        <div class="customer-shell__brand-mark">MK</div>
        <div class="stack" style="gap: 0.15rem;">
          <p class="app-layout-title">{{ t('layout.customer_workspace') }}</p>
          <p class="app-layout-subtitle">{{ t('customers.workspace_subtitle') }}</p>
        </div>
      </div>

      <div class="customer-shell__profile-card">
        <div class="customer-shell__avatar">
          <img v-if="authStore.user?.avatar_url" :src="authStore.user.avatar_url" :alt="authStore.user?.name || 'Customer avatar'">
          <span v-else>{{ userInitials }}</span>
        </div>
        <div class="stack" style="gap: 0.15rem;">
          <strong>{{ authStore.user?.name }}</strong>
          <p class="small">{{ authStore.user?.email }}</p>
          <p class="small">{{ authStore.user?.phone || t('common.not_available') }}</p>
        </div>
      </div>

      <nav class="customer-shell__nav" :aria-label="t('customers.navigation_aria')">
        <RouterLink
          v-for="item in navItems"
          :key="item.name"
          class="customer-shell__nav-link"
          :to="{ name: item.name }"
        >
          <span class="customer-shell__nav-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path :d="item.icon" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
          <span>{{ item.label }}</span>
        </RouterLink>
      </nav>

      <div class="customer-shell__sidebar-footer">
        <span class="status-chip" :class="realtimeStore.isConnected ? 'status-chip--connected' : 'status-chip--pending'">
          <span class="status-dot"></span>
          {{ realtimeStore.isConnected ? t('layout.realtime_connected') : t('layout.realtime_pending') }}
        </span>
        <UiLocaleSwitcher />
        <div class="actions">
          <a class="btn" href="/">{{ t('common.public_site') }}</a>
          <button class="btn" type="button" @click="logout">{{ t('common.logout') }}</button>
        </div>
      </div>
    </aside>

    <div class="customer-shell__content">
      <header class="customer-shell__header">
        <button class="customer-shell__toggle" type="button" @click="sidebarOpen = !sidebarOpen">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
          </svg>
        </button>

        <div class="stack" style="gap: 0.2rem;">
          <strong>{{ currentPageTitle }}</strong>
          <p class="small">{{ t('customers.layout_hint') }}</p>
        </div>

        <div class="row">
          <RouterLink class="btn" :to="{ name: 'customerCreateOrder' }">{{ t('common.create_order') }}</RouterLink>
          <RouterLink class="btn btn-primary" :to="{ name: 'customerCustomOrders' }">{{ t('customers.custom_orders_nav') }}</RouterLink>
        </div>
      </header>

      <main class="customer-shell__main">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useRealtimeStore } from '@/stores/realtime';
import UiLocaleSwitcher from '@/components/ui/UiLocaleSwitcher.vue';
import { useI18n } from '@/composables/useI18n';
import { useToast } from '@/composables/useToast';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const realtimeStore = useRealtimeStore();
const { t } = useI18n();
const { infoToast } = useToast();

const sidebarOpen = ref(false);

const navItems = computed(() => [
  { name: 'customerDashboard', label: t('common.dashboard'), icon: 'M4 13h6V4H4zm10 7h6V4h-6zm-10 0h6v-5H4z' },
  { name: 'customerPurchasedProducts', label: t('customers.purchased_products_nav'), icon: 'M6 6h15l-1.3 6.2a2 2 0 0 1-2 1.6H9.2a2 2 0 0 1-2-1.6L5 3H3' },
  { name: 'customerActiveOrders', label: t('customers.tracking_nav'), icon: 'M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z' },
  { name: 'customerOrderHistory', label: t('customers.history_nav'), icon: 'M7 4h10m-9 4h8m-9 4h10m-10 4h6M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z' },
  { name: 'customerCustomOrders', label: t('customers.custom_orders_nav'), icon: 'M12 3 4 7v6c0 5 3.4 9.8 8 11 4.6-1.2 8-6 8-11V7z' },
  { name: 'customerReviews', label: t('customers.reviews_nav'), icon: 'm12 17.3-5.2 3 1-5.9L3 9.2l6-0.9L12 3l3 5.3 6 .9-4.8 5.2 1 5.9z' },
  { name: 'customerProfile', label: t('common.profile'), icon: 'M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 8a7 7 0 0 1 14 0' },
  { name: 'customerSecurity', label: t('customers.security_nav'), icon: 'M12 3 5 6v5c0 4.5 2.9 8.7 7 10 4.1-1.3 7-5.5 7-10V6z' },
  { name: 'customerCatalog', label: t('common.catalog'), icon: 'M4 6.5 12 3l8 3.5v11L12 21l-8-3.5z' },
]);

const currentPageTitle = computed(() => {
  const activeItem = navItems.value.find((item) => item.name === route.name);
  return activeItem?.label || t('layout.customer_workspace');
});

const userInitials = computed(() => authStore.user?.name
  ?.split(' ')
  .filter(Boolean)
  .slice(0, 2)
  .map((segment) => segment.charAt(0).toUpperCase())
  .join('') || 'MK');

watch(
  () => route.fullPath,
  () => {
    sidebarOpen.value = false;
  },
);

async function logout() {
  await authStore.logout();
  infoToast(t('notifications.logout_success'));
  router.push({ name: 'login' });
}
</script>
