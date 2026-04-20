import { beforeEach, describe, expect, it } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { createMemoryHistory, createRouter } from 'vue-router';
import { setupRouterGuards } from '@/router/guards';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import { useUiPreferencesStore } from '@/stores/uiPreferences';

const Dummy = {
  render: () => null,
};

function makeRouter(pinia) {
  const router = createRouter({
    history: createMemoryHistory('/app/'),
    routes: [
      { path: '/', name: 'root', component: Dummy },
      { path: '/login', name: 'login', component: Dummy, meta: { requiresGuest: true } },
      { path: '/forbidden', name: 'forbidden', component: Dummy },
      {
        path: '/customer',
        name: 'customerDashboard',
        component: Dummy,
        meta: { requiresAuth: true, roles: ['customer'] },
      },
      {
        path: '/tailor',
        name: 'tailorDashboard',
        component: Dummy,
        meta: { requiresAuth: true, roles: ['tailor'] },
      },
    ],
  });

  setupRouterGuards(router, pinia);

  return router;
}

describe('router guards', () => {
  beforeEach(() => {
    localStorage.clear();

    const pinia = createPinia();
    setActivePinia(pinia);

    const uiPreferencesStore = useUiPreferencesStore(pinia);
    uiPreferencesStore.setLocale('en');
  });

  it('redirects guests from protected routes to login', async () => {
    const pinia = createPinia();
    setActivePinia(pinia);

    const authStore = useAuthStore(pinia);
    authStore.$patch({ bootstrapComplete: true, token: null, user: null });

    const toastStore = useToastStore(pinia);
    const router = makeRouter(pinia);

    await router.push('/customer');

    expect(router.currentRoute.value.name).toBe('login');
    expect(toastStore.items.at(-1)?.variant).toBe('info');
  });

  it('redirects authenticated users with invalid role to forbidden', async () => {
    const pinia = createPinia();
    setActivePinia(pinia);

    const authStore = useAuthStore(pinia);
    authStore.$patch({
      bootstrapComplete: true,
      token: 'token',
      user: { id: 10, role: 'customer' },
    });

    const toastStore = useToastStore(pinia);
    const router = makeRouter(pinia);

    await router.push('/tailor');

    expect(router.currentRoute.value.name).toBe('forbidden');
    expect(toastStore.items.at(-1)?.variant).toBe('warning');
  });

  it('redirects authenticated users away from guest login', async () => {
    const pinia = createPinia();
    setActivePinia(pinia);

    const authStore = useAuthStore(pinia);
    authStore.$patch({
      bootstrapComplete: true,
      token: 'token',
      user: { id: 11, role: 'customer' },
    });

    const router = makeRouter(pinia);

    await router.push('/login');

    expect(router.currentRoute.value.name).toBe('customerDashboard');
  });
});
