import { createApp } from 'vue';
import { createPinia } from 'pinia';
import PublicSiteApp from '@/public/PublicSiteApp.vue';
import { publicRouter } from '@/public/router';
import { useUiPreferencesStore } from '@/stores/uiPreferences';

const app = createApp(PublicSiteApp);
const pinia = createPinia();

app.use(pinia);

const uiPreferencesStore = useUiPreferencesStore(pinia);
uiPreferencesStore.initialize();

app.use(publicRouter);
app.mount('#public-site');
