import { createRouter, createWebHistory } from 'vue-router';
import { applyPublicMeta } from '@/public/seo';

const routes = [
  {
    path: '/',
    component: () => import('@/public/layouts/PublicLayout.vue'),
    children: [
      {
        path: '',
        name: 'publicHome',
        component: () => import('@/public/pages/HomePage.vue'),
        meta: {
          titleKey: 'seo.home_title',
          descriptionKey: 'seo.home_description',
        },
      },
      {
        path: 'how-it-works',
        name: 'publicHowItWorks',
        component: () => import('@/public/pages/HowItWorksPage.vue'),
        meta: {
          titleKey: 'seo.how_it_works_title',
          descriptionKey: 'seo.how_it_works_description',
        },
      },
      {
        path: 'for-customers',
        name: 'publicForCustomers',
        component: () => import('@/public/pages/ForCustomersPage.vue'),
        meta: {
          titleKey: 'seo.for_customers_title',
          descriptionKey: 'seo.for_customers_description',
        },
      },
      {
        path: 'for-tailors',
        name: 'publicForTailors',
        component: () => import('@/public/pages/ForTailorsPage.vue'),
        meta: {
          titleKey: 'seo.for_tailors_title',
          descriptionKey: 'seo.for_tailors_description',
        },
      },
      {
        path: 'faq',
        name: 'publicFaq',
        component: () => import('@/public/pages/FaqPage.vue'),
        meta: {
          titleKey: 'seo.faq_title',
          descriptionKey: 'seo.faq_description',
        },
      },
      {
        path: 'contact',
        name: 'publicContact',
        component: () => import('@/public/pages/ContactPage.vue'),
        meta: {
          titleKey: 'seo.contact_title',
          descriptionKey: 'seo.contact_description',
        },
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
  applyPublicMeta(to);
});
