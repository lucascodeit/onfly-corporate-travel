import { defineStore } from 'pinia'
import {
  getAdminTravelRequests,
  approveTravelRequest,
  disapproveTravelRequest,
  type TravelRequest,
  type DateRangeFilter,
} from '@/services/travelRequests'

interface AdminTravelRequestState {
  requests: TravelRequest[]
  meta: Record<string, unknown>
  loading: boolean
  approvingId: number | null
  disapprovingId: number | null
}

export const useAdminTravelRequestStore = defineStore('adminTravelRequests', {
  state: (): AdminTravelRequestState => ({
    requests: [],
    meta: {},
    loading: false,
    approvingId: null,
    disapprovingId: null,
  }),

  actions: {
    async fetchRequests(page = 1, userId?: number, filters?: DateRangeFilter) {
      this.loading = true
      try {
        const { data } = await getAdminTravelRequests(page, userId, filters)
        this.requests = data.data
        this.meta = data.meta
      } finally {
        this.loading = false
      }
    },

    async approve(id: number) {
      this.approvingId = id
      try {
        await approveTravelRequest(id)
      } finally {
        this.approvingId = null
      }
    },

    async disapprove(id: number) {
      this.disapprovingId = id
      try {
        await disapproveTravelRequest(id)
      } finally {
        this.disapprovingId = null
      }
    },
  },
})
