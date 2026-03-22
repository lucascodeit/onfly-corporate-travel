import apiClient from '@/services/api'

export interface User {
  id: number
  first_name: string
  last_name: string
  email: string
  type: 'admin' | 'staff'
  is_active: boolean
  created_at: string
}

export interface StoreUserPayload {
  first_name: string
  last_name: string
  email: string
  password: string
  password_confirmation: string
  type: 'admin' | 'staff'
}

export interface UpdateUserPayload {
  first_name: string
  last_name: string
  email: string
  is_active: boolean
}

export function getUsers(page = 1) {
  return apiClient.get<{ data: User[]; meta: Record<string, unknown> }>('/users', { params: { page } })
}

export function getUser(id: number) {
  return apiClient.get<{ data: User }>(`/users/${id}`)
}

export function createUser(payload: StoreUserPayload) {
  return apiClient.post<{ data: User }>('/users', payload)
}

export function updateUser(id: number, payload: UpdateUserPayload) {
  return apiClient.put<{ data: User }>(`/users/${id}`, payload)
}

export function deleteUser(id: number) {
  return apiClient.delete(`/users/${id}`)
}

export function changeUserPassword(id: number, password: string, password_confirmation: string) {
  return apiClient.put(`/users/${id}/password`, { password, password_confirmation })
}
