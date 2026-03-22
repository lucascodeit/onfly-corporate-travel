import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8080/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

let isRefreshing = false

apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config

    if (error.response?.status === 401 && !originalRequest._retry && !isRefreshing) {
      const isAuthRoute = originalRequest.url?.includes('/auth/')
      if (isAuthRoute) {
        return Promise.reject(error)
      }

      originalRequest._retry = true
      isRefreshing = true

      try {
        const authStore = useAuthStore()
        await authStore.refreshToken()
        originalRequest.headers.Authorization = `Bearer ${authStore.token}`
        return apiClient(originalRequest)
      } catch {
        const authStore = useAuthStore()
        authStore.clearAuth()
        window.location.href = '/login'
        return Promise.reject(error)
      } finally {
        isRefreshing = false
      }
    }

    return Promise.reject(error)
  },
)

export default apiClient
