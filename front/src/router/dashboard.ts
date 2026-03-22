import type { RouteRecordRaw } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import travelRequestRoutes from './travelRequests'
import adminTravelRequestRoutes from './adminTravelRequests'
import notificationRoutes from './notifications'

const dashboardRoutes: RouteRecordRaw[] = [
  {
    path: '/',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/views/Dashboard/Dashboard.vue'),
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('@/views/Users/UserList.vue'),
        meta: { requiresAdmin: true },
      },
      {
        path: 'users/create',
        name: 'users-create',
        component: () => import('@/views/Users/UserForm.vue'),
        meta: { requiresAdmin: true },
      },
      {
        path: 'users/:id/edit',
        name: 'users-edit',
        component: () => import('@/views/Users/UserForm.vue'),
        meta: { requiresAdmin: true },
      },
      {
        path: 'users/:id/password',
        name: 'users-password',
        component: () => import('@/views/Users/UserPassword.vue'),
        meta: { requiresAdmin: true },
      },
      ...travelRequestRoutes,
      ...adminTravelRequestRoutes,
      ...notificationRoutes,
      {
        path: 'profile',
        name: 'profile',
        component: () => import('@/views/Profile/ProfileSettings.vue'),
      },
      {
        path: 'profile/password',
        name: 'profile-password',
        component: () => import('@/views/Profile/ProfilePassword.vue'),
      },
    ],
  },
]

export default dashboardRoutes
