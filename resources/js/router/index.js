import { createRouter, createWebHistory } from 'vue-router';

const spaBase = import.meta.env.VITE_SPA_BASE || '/app/';

export const router = createRouter({
  history: createWebHistory(spaBase),
  routes: [
    {
      path: '/',
      name: 'root',
      redirect: { name: 'login' },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/auth/LoginPage.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/login/customer',
      redirect: { name: 'login', query: { role: 'customer' } },
    },
    {
      path: '/login/tailor',
      redirect: { name: 'login', query: { role: 'tailor' } },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/pages/auth/RegisterPage.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/register/customer',
      redirect: { name: 'register', query: { role: 'customer' } },
    },
    {
      path: '/register/tailor',
      redirect: { name: 'register', query: { role: 'tailor' } },
    },
    {
      path: '/verify-phone',
      name: 'verifyPhone',
      component: () => import('@/pages/auth/VerifyPhonePage.vue'),
      meta: { requiresAuth: true, roles: ['tailor'] },
    },
    {
      path: '/forbidden',
      name: 'forbidden',
      component: () => import('@/pages/common/ForbiddenPage.vue'),
    },
    {
      path: '/customer',
      component: () => import('@/layouts/CustomerLayout.vue'),
      meta: { requiresAuth: true, roles: ['customer'] },
      children: [
        {
          path: '',
          name: 'customerDashboard',
          component: () => import('@/pages/customer/CustomerDashboardPage.vue'),
        },
        {
          path: 'catalog',
          name: 'customerCatalog',
          component: () => import('@/pages/customer/CatalogPage.vue'),
        },
        {
          path: 'products/:id',
          name: 'customerProductDetails',
          component: () => import('@/pages/customer/ProductDetailsPage.vue'),
        },
        {
          path: 'orders/create',
          name: 'customerCreateOrder',
          component: () => import('@/pages/customer/CreateOrderPage.vue'),
        },
        {
          path: 'purchased-products',
          name: 'customerPurchasedProducts',
          component: () => import('@/pages/customer/CustomerPurchasedProductsPage.vue'),
        },
        {
          path: 'orders/active',
          name: 'customerActiveOrders',
          component: () => import('@/pages/customer/CustomerActiveOrdersPage.vue'),
        },
        {
          path: 'orders/history',
          name: 'customerOrderHistory',
          component: () => import('@/pages/customer/CustomerOrderHistoryPage.vue'),
        },
        {
          path: 'orders/:id',
          name: 'customerOrderDetails',
          component: () => import('@/pages/customer/CustomerOrderDetailsPage.vue'),
        },
        {
          path: 'custom-orders',
          name: 'customerCustomOrders',
          component: () => import('@/pages/customer/CustomerCustomOrdersPage.vue'),
        },
        {
          path: 'profile',
          name: 'customerProfile',
          component: () => import('@/pages/customer/CustomerProfilePage.vue'),
        },
        {
          path: 'security',
          name: 'customerSecurity',
          component: () => import('@/pages/customer/CustomerPasswordPage.vue'),
        },
        {
          path: 'reviews',
          name: 'customerReviews',
          component: () => import('@/pages/customer/CustomerReviewsPage.vue'),
        },
      ],
    },
    {
      path: '/tailor',
      component: () => import('@/layouts/TailorLayout.vue'),
      meta: { requiresAuth: true, roles: ['tailor'] },
      children: [
        {
          path: '',
          name: 'tailorDashboard',
          component: () => import('@/pages/tailor/TailorDashboardPage.vue'),
        },
        {
          path: 'orders/active',
          name: 'tailorActiveOrders',
          component: () => import('@/pages/tailor/TailorActiveOrdersPage.vue'),
        },
        {
          path: 'orders/:id',
          name: 'tailorOrderDetails',
          component: () => import('@/pages/tailor/TailorOrderDetailsPage.vue'),
        },
        {
          path: 'availability',
          name: 'tailorAvailability',
          component: () => import('@/pages/tailor/TailorAvailabilityPage.vue'),
        },
        {
          path: 'profile',
          name: 'tailorProfile',
          component: () => import('@/pages/tailor/TailorProfilePage.vue'),
        },
      ],
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: { name: 'root' },
    },
  ],
});
