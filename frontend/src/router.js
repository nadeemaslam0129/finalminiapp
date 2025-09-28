// src/router.js
import { createRouter, createWebHistory } from 'vue-router';
import { store } from "./store";

// Pages
import LoginPage from './pages/LoginPage.vue';
import RegisterPage from './pages/RegisterPage.vue';

const routes = [
  {
    path: '/login',
    component: LoginPage,
    meta: { guest: true }
  },
  {
    path: '/register',
    component: RegisterPage,
    meta: { guest: true }
  },
  {
    path: '/dashboard',
    component: () => import('./pages/DashboardPage.vue'), // lazy load if you add Dashboard later
    meta: { auth: true }
  },
  {
    path: '/',
    redirect: '/dashboard'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation guard
router.beforeEach((to, from, next) => {
  if (to.meta.auth && !store.user.id) {
    return next('/login');
  }
  if (to.meta.guest && store.user.id) {
    return next('/dashboard');
  }
  next();
});

export default router;
