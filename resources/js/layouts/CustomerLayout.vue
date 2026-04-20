<template>
  <div>
    <header class="navbar">
      <div class="container">
        <div class="stack" style="gap: 0.2rem;">
          <p class="app-layout-title">Makasouk Customer Workspace</p>
          <p class="app-layout-subtitle">{{ authStore.user?.name }} · {{ authStore.user?.email }}</p>
        </div>

        <nav class="nav-links" aria-label="Customer navigation">
          <RouterLink class="nav-link" :to="{ name: 'customerDashboard' }">Dashboard</RouterLink>
          <RouterLink class="nav-link" :to="{ name: 'customerCatalog' }">Catalog</RouterLink>
          <RouterLink class="nav-link" :to="{ name: 'customerCreateOrder' }">Create Order</RouterLink>
          <RouterLink class="nav-link" :to="{ name: 'customerActiveOrders' }">Active</RouterLink>
          <RouterLink class="nav-link" :to="{ name: 'customerOrderHistory' }">History</RouterLink>
        </nav>

        <div class="actions">
          <span class="status-chip" :class="realtimeStore.isConnected ? 'status-chip--connected' : 'status-chip--pending'">
            <span class="status-dot"></span>
            {{ realtimeStore.isConnected ? 'Realtime Connected' : 'Realtime Pending' }}
          </span>
          <a class="btn" href="/">Public Site</a>
          <button class="btn" type="button" @click="logout">Logout</button>
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

const router = useRouter();
const authStore = useAuthStore();
const realtimeStore = useRealtimeStore();

async function logout() {
  await authStore.logout();
  router.push({ name: 'login' });
}
</script>
