import { defineStore } from 'pinia'
import {
  getTravelRequests,
  createTravelRequest,
  cancelTravelRequest,
  type TravelRequest,
  type StoreTravelRequestPayload,
  type TravelRequestFilters,
} from '@/services/travelRequests'

interface TravelRequestState {
  requests: TravelRequest[]
  meta: Record<string, unknown>
  loading: boolean
  saving: boolean
  cancelling: boolean
}

export const useTravelRequestStore = defineStore('travelRequests', {
  state: (): TravelRequestState => ({
    requests: [],
    meta: {},
    loading: false,
    saving: false,
    cancelling: false,
  }),

  actions: {
    async fetchRequests(page = 1, filters?: TravelRequestFilters) {
      this.loading = true
      try {
        const { data } = await getTravelRequests(page, filters)
        this.requests = data.data
        this.meta = data.meta
      } finally {
        this.loading = false
      }
    },

    async create(payload: StoreTravelRequestPayload) {
      this.saving = true
      try {
        await createTravelRequest(payload)
      } finally {
        this.saving = false
      }
    },

    async cancel(id: number) {
      this.cancelling = true
      try {
        await cancelTravelRequest(id)
      } finally {
        this.cancelling = false
      }
    },
  },
})
