import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/Auth/Login.vue'),
    meta: { requiresGuest: true },
  },
  {
    path: '/',
    component: () => import('@/components/Layout/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('@/views/Dashboard.vue'),
      },
      {
        path: '/products',
        name: 'products.index',
        component: () => import('@/views/Products/Index.vue'),
      },
      {
        path: '/products/create',
        name: 'products.create',
        component: () => import('@/views/Products/Create.vue'),
        meta: { requiresRole: ['admin'] },
      },
      {
        path: '/products/:id',
        name: 'products.show',
        component: () => import('@/views/Products/Show.vue'),
      },
      {
        path: '/products/:id/edit',
        name: 'products.edit',
        component: () => import('@/views/Products/Edit.vue'),
        meta: { requiresRole: ['admin'] },
      },
      {
        path: '/components',
        name: 'components.index',
        component: () => import('@/views/Components/Index.vue'),
      },
      {
        path: '/components/create',
        name: 'components.create',
        component: () => import('@/views/Components/Create.vue'),
        meta: { requiresRole: ['admin'] },
      },
      {
        path: '/components/:id/edit',
        name: 'components.edit',
        component: () => import('@/views/Components/Edit.vue'),
        meta: { requiresRole: ['admin'] },
      },
      {
        path: '/purchase-histories',
        name: 'purchase-histories.index',
        component: () => import('@/views/PurchaseHistories/Index.vue'),
      },
      {
        path: '/purchase-histories/create',
        name: 'purchase-histories.create',
        component: () => import('@/views/PurchaseHistories/Create.vue'),
        meta: { requiresRole: ['admin', 'finance'] },
      },
      {
        path: '/purchase-histories/:id/edit',
        name: 'purchase-histories.edit',
        component: () => import('@/views/PurchaseHistories/Edit.vue'),
        meta: { requiresRole: ['admin', 'finance'] },
      },
      {
        path: '/hpe/calculate',
        name: 'hpe.calculate',
        component: () => import('@/views/Hpe/Calculate.vue'),
        meta: { requiresRole: ['admin', 'finance'] },
      },
      {
        path: '/hpe/results',
        name: 'hpe.results.index',
        component: () => import('@/views/Hpe/Index.vue'),
      },
      {
        path: '/hpe/results/:id',
        name: 'hpe.results.show',
        component: () => import('@/views/Hpe/Show.vue'),
      },
      {
        path: '/exchange-rates',
        name: 'exchange-rates.index',
        component: () => import('@/views/ExchangeRates/Index.vue'),
      },
      {
        path: '/audit-logs',
        name: 'audit-logs.index',
        component: () => import('@/views/AuditLogs/Index.vue'),
        meta: { requiresRole: ['admin'] },
      },
      {
        path: '/reporting',
        name: 'reporting.index',
        component: () => import('@/views/Reporting/Index.vue'),
        meta: { requiresRole: ['admin', 'finance'] },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' });
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next({ name: 'dashboard' });
  } else if (to.meta.requiresRole) {
    const userRole = authStore.user?.role;
    if (!userRole || !to.meta.requiresRole.includes(userRole)) {
      next({ name: 'dashboard' });
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;

