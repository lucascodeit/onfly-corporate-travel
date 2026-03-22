import { createRouter, createWebHistory } from 'vue-router'
import HealthCheck from '@/views/HealthCheck.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'health',
      component: HealthCheck,
    },
  ],
})

export default router
