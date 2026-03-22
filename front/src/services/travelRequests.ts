import apiClient from '@/services/api'
import type { User } from '@/services/users'

export interface TravelRequest {
  id: number
  status: 'requested' | 'approved' | 'disapproved' | 'cancelled'
  destination: string
  start_date: string
  end_date: string
  user: User
  admin: User | null
  created_at: string
}

export interface StoreTravelRequestPayload {
  destination: string
  start_date: string
  end_date: string
}

export interface TravelRequestFilters {
  start_date?: string
  end_date?: string
  status?: string
}

export function getTravelRequests(page = 1, filters?: TravelRequestFilters) {
  return apiClient.get<{ data: TravelRequest[]; meta: Record<string, unknown> }>(
    '/travel-requests',
    { params: { page, ...filters } },
  )
}

export function createTravelRequest(payload: StoreTravelRequestPayload) {
  return apiClient.post<{ data: TravelRequest }>('/travel-requests', payload)
}

export function cancelTravelRequest(id: number) {
  return apiClient.patch<{ data: TravelRequest }>(`/travel-requests/${id}/cancel`)
}

export function getAdminTravelRequests(page = 1, userId?: number, filters?: TravelRequestFilters) {
  return apiClient.get<{ data: TravelRequest[]; meta: Record<string, unknown> }>(
    '/admin/travel-requests',
    { params: { page, user_id: userId, ...filters } },
  )
}

export function approveTravelRequest(id: number) {
  return apiClient.patch<{ data: TravelRequest }>(`/admin/travel-requests/${id}/approve`)
}

export function disapproveTravelRequest(id: number) {
  return apiClient.patch<{ data: TravelRequest }>(`/admin/travel-requests/${id}/disapprove`)
}
