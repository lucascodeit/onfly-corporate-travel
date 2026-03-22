<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useNotificationStore } from '@/stores/notifications'
import AppButton from '@/components/AppButton.vue'

const route = useRoute()
const router = useRouter()
const store = useNotificationStore()

const statusConfig: Record<string, { class: string; label: string }> = {
  requested: { class: 'bg-amber-500/15 text-amber-400', label: 'Requested' },
  approved: { class: 'bg-emerald-500/15 text-emerald-400', label: 'Approved' },
  disapproved: { class: 'bg-red-500/15 text-red-400', label: 'Disapproved' },
  cancelled: { class: 'bg-slate-500/15 text-slate-400', label: 'Cancelled' },
}

function formatDateTime(dateStr: string) {
  return new Date(dateStr).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatDate(dateStr: string) {
  return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

onMounted(() => {
  const id = Number(route.params.id)
  store.fetchNotification(id)
})
</script>

<template>
  <div>
    <div class="mb-6">
      <AppButton variant="secondary" size="sm" @click="router.push({ name: 'notifications' })">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Back to Notifications
      </AppButton>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="py-12 text-center text-slate-400">
      <svg class="mx-auto h-8 w-8 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
      <p class="mt-2 text-sm">Loading notification...</p>
    </div>

    <template v-else-if="store.currentNotification">
      <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-6">
        <!-- Header -->
        <div class="mb-4 flex items-start justify-between">
          <div>
            <h1 class="text-xl font-bold text-white">Notification Details</h1>
            <p class="mt-1 text-sm text-slate-400">
              Received {{ formatDateTime(store.currentNotification.created_at) }}
            </p>
          </div>
          <span
            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
            :class="store.currentNotification.is_read ? 'bg-slate-500/15 text-slate-400' : 'bg-indigo-500/15 text-indigo-400'"
          >
            {{ store.currentNotification.is_read ? 'Read' : 'Unread' }}
          </span>
        </div>

        <!-- Message -->
        <div class="mb-6 rounded-lg border border-slate-600 bg-slate-900/50 p-4">
          <p class="text-base text-white">{{ store.currentNotification.message }}</p>
        </div>

        <!-- Meta -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <p class="text-xs font-medium uppercase tracking-wider text-slate-400">From</p>
            <p class="mt-1 text-sm text-white">
              {{ store.currentNotification.user_from?.first_name }}
              {{ store.currentNotification.user_from?.last_name }}
            </p>
          </div>
          <div v-if="store.currentNotification.read_at">
            <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Read at</p>
            <p class="mt-1 text-sm text-slate-300">
              {{ formatDateTime(store.currentNotification.read_at) }}
            </p>
          </div>
        </div>

        <!-- Travel Request Details -->
        <div
          v-if="store.currentNotification.travel_request"
          class="rounded-lg border border-slate-600 bg-slate-900/50 p-4"
        >
          <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-400">
            Travel Request
          </h2>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
              <p class="text-xs text-slate-400">Destination</p>
              <p class="mt-0.5 text-sm font-medium text-white">
                {{ store.currentNotification.travel_request.destination }}
              </p>
            </div>
            <div>
              <p class="text-xs text-slate-400">Start Date</p>
              <p class="mt-0.5 text-sm text-slate-300">
                {{ formatDate(store.currentNotification.travel_request.start_date) }}
              </p>
            </div>
            <div>
              <p class="text-xs text-slate-400">End Date</p>
              <p class="mt-0.5 text-sm text-slate-300">
                {{ formatDate(store.currentNotification.travel_request.end_date) }}
              </p>
            </div>
            <div>
              <p class="text-xs text-slate-400">Status</p>
              <span
                class="mt-0.5 inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                :class="statusConfig[store.currentNotification.travel_request.status]?.class"
              >
                {{ statusConfig[store.currentNotification.travel_request.status]?.label }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
