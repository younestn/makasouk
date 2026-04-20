import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import { useUiPreferencesStore } from '@/stores/uiPreferences';
import { translate } from '@/i18n';
import { homeRouteForRole } from '@/utils/roleRoutes';

export function setupRouterGuards(router, pinia) {
  router.beforeEach(async (to) => {
    const authStore = useAuthStore(pinia);
    const toastStore = useToastStore(pinia);
    const uiPreferencesStore = useUiPreferencesStore(pinia);
    const locale = uiPreferencesStore.locale;

    if (!authStore.bootstrapComplete) {
      await authStore.bootstrap();
    }

    if (authStore.user?.role === 'admin') {
      toastStore.info(translate(locale, 'notifications.admin_redirect'), { duration: 3200 });
      window.location.assign('/admin-panel');
      return false;
    }

    if (to.meta.requiresGuest && authStore.isAuthenticated) {
      return homeRouteForRole(authStore.user?.role);
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      toastStore.info(translate(locale, 'notifications.auth_required'));
      return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.roles && authStore.isAuthenticated) {
      if (!to.meta.roles.includes(authStore.user?.role)) {
        toastStore.warning(translate(locale, 'notifications.forbidden_route'));
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
