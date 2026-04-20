import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from '@/App.vue';
import { router } from '@/router';
import { setupRouterGuards } from '@/router/guards';
import { useAuthStore } from '@/stores/auth';
import { useUiPreferencesStore } from '@/stores/uiPreferences';

async function bootstrap() {
  const app = createApp(App);
  const pinia = createPinia();

  app.use(pinia);

  const uiPreferencesStore = useUiPreferencesStore(pinia);
  uiPreferencesStore.initialize();

  const authStore = useAuthStore(pinia);
  await authStore.bootstrap();

  setupRouterGuards(router, pinia);
  app.use(router);

  await router.isReady();
  app.mount('#app');
}

bootstrap();
