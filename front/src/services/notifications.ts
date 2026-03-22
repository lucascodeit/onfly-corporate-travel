import apiClient from '@/services/api'
import type { User } from '@/services/users'
import type { TravelRequest } from '@/services/travelRequests'

export interface Notification {
  id: number
  notification_type: 'response_travel' | 'travel_create' | 'general'
  message: string | null
  is_read: boolean
  read_at: string | null
  created_at: string
  user_from: User
  travel_request: TravelRequest | null
}

export function getNotifications(page = 1, filter?: string) {
  return apiClient.get<{ data: Notification[]; meta: Record<string, unknown> }>('/notifications', {
    params: { page, filter },
  })
}

export function getNotification(id: number) {
  return apiClient.get<{ data: Notification }>(`/notifications/${id}`)
}

export function markNotificationAsRead(id: number) {
  return apiClient.patch<{ data: Notification }>(`/notifications/${id}/read`)
}

export function getUnreadCount() {
  return apiClient.get<{ count: number }>('/notifications/unread-count')
}
