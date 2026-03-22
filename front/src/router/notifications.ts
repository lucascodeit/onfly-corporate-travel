import type { RouteRecordRaw } from 'vue-router'

const notificationRoutes: RouteRecordRaw[] = [
  {
    path: 'notifications',
    name: 'notifications',
    component: () => import('@/views/Notifications/NotificationList.vue'),
  },
  {
    path: 'notifications/:id',
    name: 'notification-detail',
    component: () => import('@/views/Notifications/NotificationDetail.vue'),
  },
]

export default notificationRoutes
