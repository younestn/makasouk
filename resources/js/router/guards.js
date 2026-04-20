import { useAuthStore } from '@/stores/auth';
import { homeRouteForRole } from '@/utils/roleRoutes';

export function setupRouterGuards(router, pinia) {
  router.beforeEach(async (to) => {
    const authStore = useAuthStore(pinia);

    if (!authStore.bootstrapComplete) {
      await authStore.bootstrap();
    }

    if (authStore.user?.role === 'admin') {
      window.location.assign('/admin-panel');
      return false;
    }

    if (to.meta.requiresGuest && authStore.isAuthenticated) {
      return homeRouteForRole(authStore.user?.role);
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.roles && authStore.isAuthenticated) {
      if (!to.meta.roles.includes(authStore.user?.role)) {
        return { name: 'forbidden' };
      }
    }

    if (to.name === 'root') {
      if (!authStore.isAuthenticated) {
        return { name: 'login' };
      }

      return homeRouteForRole(authStore.user?.role);
    }

    return true;
  });
}
