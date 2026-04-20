<template>
  <div>
    <header class="navbar">
      <div class="container">
        <div class="stack" style="gap: 0.2rem;">
          <strong>Makasouk Tailor</strong>
          <span class="small" style="color: #e5e7eb;">{{ authStore.user?.name }} ({{ authStore.user?.email }})</span>
        </div>

        <nav class="nav-links">
          <RouterLink class="nav-link" :to="{ name: 'tailorDashboard' }">Dashboard</RouterLink>
          <RouterLink class="nav-link" :to="{ name: 'tailorActiveOrders' }">Active Orders</RouterLink>
          <RouterLink class="nav-link" :to="{ name: 'tailorAvailability' }">Availability</RouterLink>
          <RouterLink class="nav-link" :to="{ name: 'tailorProfile' }">Profile</RouterLink>
        </nav>

        <div class="actions">
          <span class="badge" :class="realtimeStore.isConnected ? 'badge-success' : 'badge-warning'">
            {{ realtimeStore.isConnected ? 'Realtime Connected' : 'Realtime Pending' }}
          </span>
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
