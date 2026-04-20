import { createApp } from 'vue';
import PublicSiteApp from '@/public/PublicSiteApp.vue';
import { publicRouter } from '@/public/router';

const app = createApp(PublicSiteApp);

app.use(publicRouter);
app.mount('#public-site');
