import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import HealthCheck from '@/views/HealthCheck.vue'

const healthRoutes: RouteRecordRaw[] = [
  { path: '/health', component: HealthCheck },
]

export default healthRoutes