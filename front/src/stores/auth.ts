import { defineStore } from 'pinia'
import { login as loginApi, refreshToken as refreshApi, logout as logoutApi } from '@/services/auth'
import type { AuthResponse } from '@/services/auth'

interface AuthState {
  token: string | null
  user: AuthResponse['user'] | null
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    token: localStorage.getItem('token'),
    user: JSON.parse(localStorage.getItem('user') || 'null'),
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.type === 'admin',
    isStaff: (state) => state.user?.type === 'staff',
    fullName: (state) => state.user ? `${state.user.first_name} ${state.user.last_name}` : '',
  },

  actions: {
    async signIn(email: string, password: string) {
      const { data } = await loginApi({ email, password })
      this.setAuth(data.data)
    },

    async refreshToken() {
      const { data } = await refreshApi()
      this.setAuth(data.data)
    },

    async signOut() {
      try {
        await logoutApi()
      } finally {
        this.clearAuth()
      }
    },

    setAuth(payload: AuthResponse) {
      this.token = payload.token
      this.user = payload.user
      localStorage.setItem('token', payload.token)
      localStorage.setItem('user', JSON.stringify(payload.user))
    },

    clearAuth() {
      this.token = null
      this.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    },
  },
})
