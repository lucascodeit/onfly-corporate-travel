import apiClient from '@/services/api'

export interface LoginPayload {
  email: string
  password: string
}

export interface AuthResponse {
  token: string
  token_type: string
  expires_in: number
  user: {
    id: number
    first_name: string
    last_name: string
    email: string
    type: 'admin' | 'staff'
    is_active: boolean
    created_at: string
  }
}

export function login(payload: LoginPayload) {
  return apiClient.post<{ data: AuthResponse }>('/auth/login', payload)
}

export function refreshToken() {
  return apiClient.post<{ data: AuthResponse }>('/auth/refresh')
}

export function logout() {
  return apiClient.post('/auth/logout')
}
