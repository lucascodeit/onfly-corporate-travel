import type { RouteRecordRaw } from 'vue-router'

const adminTravelRequestRoutes: RouteRecordRaw[] = [
  {
    path: 'manage-travel-requests',
    name: 'manage-travel-requests',
    component: () => import('@/views/ManageTravelRequests/ManageTravelRequestList.vue'),
    meta: { requiresAdmin: true },
  },
]

export default adminTravelRequestRoutes
