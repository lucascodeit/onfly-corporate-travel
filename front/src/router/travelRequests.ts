import type { RouteRecordRaw } from 'vue-router'

const travelRequestRoutes: RouteRecordRaw[] = [
  {
    path: 'travel-requests',
    name: 'travel-requests',
    component: () => import('@/views/TravelRequests/TravelRequestList.vue'),
    meta: { requiresStaff: true },
  },
  {
    path: 'travel-requests/create',
    name: 'travel-requests-create',
    component: () => import('@/views/TravelRequests/TravelRequestForm.vue'),
    meta: { requiresStaff: true },
  },
]

export default travelRequestRoutes
