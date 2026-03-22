import { defineStore } from 'pinia'
import {
  getNotifications,
  getNotification,
  markNotificationAsRead,
  getUnreadCount,
  type Notification,
} from '@/services/notifications'

interface NotificationState {
  notifications: Notification[]
  meta: Record<string, unknown>
  unreadCount: number
  currentNotification: Notification | null
  loading: boolean
}

export const useNotificationStore = defineStore('notifications', {
  state: (): NotificationState => ({
    notifications: [],
    meta: {},
    unreadCount: 0,
    currentNotification: null,
    loading: false,
  }),

  actions: {
    async fetchNotifications(page = 1, filter?: string) {
      this.loading = true
      try {
        const { data } = await getNotifications(page, filter)
        this.notifications = data.data
        this.meta = data.meta
      } finally {
        this.loading = false
      }
    },

    async fetchUnreadCount() {
      try {
        const { data } = await getUnreadCount()
        this.unreadCount = data.count
      } catch {
        // silently fail — sidebar badge is non-critical
      }
    },

    async fetchNotification(id: number) {
      this.loading = true
      try {
        const { data } = await getNotification(id)
        const wasUnread = !data.data.is_read
        this.currentNotification = data.data

        if (wasUnread && this.unreadCount > 0) {
          this.unreadCount--
        }

        const idx = this.notifications.findIndex((n) => n.id === id)
        if (idx !== -1) {
          this.notifications[idx] = { ...this.notifications[idx], is_read: true, read_at: data.data.read_at }
        }
      } finally {
        this.loading = false
      }
    },

    async markAsRead(id: number) {
      try {
        const { data } = await markNotificationAsRead(id)

        const idx = this.notifications.findIndex((n) => n.id === id)
        if (idx !== -1) {
          const wasUnread = !this.notifications[idx].is_read
          this.notifications[idx] = data.data
          if (wasUnread && this.unreadCount > 0) {
            this.unreadCount--
          }
        }
      } catch {
        throw new Error('Failed to mark notification as read.')
      }
    },
  },
})
