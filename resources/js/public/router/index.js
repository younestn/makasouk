import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  {
    path: '/',
    component: () => import('@/public/layouts/PublicLayout.vue'),
    children: [
      {
        path: '',
        name: 'publicHome',
        component: () => import('@/public/pages/HomePage.vue'),
        meta: { title: 'Makasouk | Tailoring On Demand' },
      },
      {
        path: 'how-it-works',
        name: 'publicHowItWorks',
        component: () => import('@/public/pages/HowItWorksPage.vue'),
        meta: { title: 'How It Works | Makasouk' },
      },
      {
        path: 'for-customers',
        name: 'publicForCustomers',
        component: () => import('@/public/pages/ForCustomersPage.vue'),
        meta: { title: 'For Customers | Makasouk' },
      },
      {
        path: 'for-tailors',
        name: 'publicForTailors',
        component: () => import('@/public/pages/ForTailorsPage.vue'),
        meta: { title: 'For Tailors | Makasouk' },
      },
      {
        path: 'faq',
        name: 'publicFaq',
        component: () => import('@/public/pages/FaqPage.vue'),
        meta: { title: 'FAQ | Makasouk' },
      },
      {
        path: 'contact',
        name: 'publicContact',
        component: () => import('@/public/pages/ContactPage.vue'),
        meta: { title: 'Contact | Makasouk' },
      },
    ],
  },
];

export const publicRouter = createRouter({
  history: createWebHistory('/'),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

publicRouter.afterEach((to) => {
  if (to.meta?.title) {
    document.title = String(to.meta.title);
  }
});
