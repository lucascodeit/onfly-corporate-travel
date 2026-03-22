import type { RouteRecordRaw } from 'vue-router'

const authRoutes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/Login/Login.vue'),
    meta: { guest: true },
  },
]

export default authRoutes
