import apiClient from '@/services/api'
import type { User } from '@/services/users'

export interface UpdateProfilePayload {
  first_name: string
  last_name: string
  email: string
}

export interface ChangePasswordPayload {
  current_password: string
  new_password: string
  new_password_confirmation: string
}

export function getProfile() {
  return apiClient.get<{ data: User }>('/profile')
}

export function updateProfile(payload: UpdateProfilePayload) {
  return apiClient.put<{ data: User }>('/profile', payload)
}

export function changePassword(payload: ChangePasswordPayload) {
  return apiClient.put('/profile/password', payload)
}
