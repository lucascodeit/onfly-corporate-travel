import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import authRoutes from './auth'
import dashboardRoutes from './dashboard'
import healthRoutes from './health'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [...authRoutes, ...dashboardRoutes, ...healthRoutes],
})

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore()

  if (to.meta.guest && authStore.isAuthenticated) {
    return next('/dashboard')
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next('/login')
  }

  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return next('/dashboard')
  }

  if (to.meta.requiresStaff && authStore.isAdmin) {
    return next('/dashboard')
  }

  next()
})

export default router
