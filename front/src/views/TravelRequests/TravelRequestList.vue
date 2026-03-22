<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTravelRequestStore } from '@/stores/travelRequests'
import type { TravelRequest, DateRangeFilter } from '@/services/travelRequests'
import AppButton from '@/components/AppButton.vue'
import AppTable from '@/components/AppTable.vue'
import CancelTravelRequestModal from './components/CancelTravelRequestModal.vue'

const router = useRouter()
const store = useTravelRequestStore()
const cancelTarget = ref<TravelRequest | null>(null)

const filterStartDate = ref('')
const filterEndDate = ref('')
const currentPage = ref(1)

const columns = [
  { key: 'destination', label: 'Destination' },
  { key: 'start_date', label: 'Start Date' },
  { key: 'end_date', label: 'End Date' },
  { key: 'status', label: 'Status' },
  { key: 'admin', label: 'Reviewed By' },
  { key: 'created_at', label: 'Created At' },
]

const statusConfig: Record<string, { class: string; label: string }> = {
  requested: { class: 'bg-amber-500/15 text-amber-400', label: 'Requested' },
  approved: { class: 'bg-emerald-500/15 text-emerald-400', label: 'Approved' },
  disapproved: { class: 'bg-red-500/15 text-red-400', label: 'Disapproved' },
  cancelled: { class: 'bg-slate-500/15 text-slate-400', label: 'Cancelled' },
}

const dateFilters = computed<DateRangeFilter>(() => {
  const filters: DateRangeFilter = {}
  if (filterStartDate.value) filters.start_date = filterStartDate.value
  if (filterEndDate.value) filters.end_date = filterEndDate.value
  return filters
})

async function handleFilterChange() {
  currentPage.value = 1
  await store.fetchRequests(1, dateFilters.value)
}

async function handleCancel() {
  if (!cancelTarget.value) return
  await store.cancel(cancelTarget.value.id)
  cancelTarget.value = null
  await store.fetchRequests(currentPage.value, dateFilters.value)
}

async function goToPage(page: number) {
  currentPage.value = page
  await store.fetchRequests(page, dateFilters.value)
}

function formatDate(dateStr: string) {
  return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

onMounted(() => store.fetchRequests())
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">My Travel Requests</h1>
      <AppButton @click="router.push('/travel-requests/create')">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        New Request
      </AppButton>
    </div>

    <div class="mb-4 flex flex-wrap items-end gap-4">
      <div>
        <label for="filter-start-date" class="mb-1 block text-sm text-slate-300">Start date</label>
        <input
          id="filter-start-date"
          v-model="filterStartDate"
          type="date"
          class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-1.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
          @change="handleFilterChange"
        />
      </div>
      <div>
        <label for="filter-end-date" class="mb-1 block text-sm text-slate-300">End date</label>
        <input
          id="filter-end-date"
          v-model="filterEndDate"
          type="date"
          class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-1.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
          @change="handleFilterChange"
        />
      </div>
    </div>

    <AppTable :columns="columns" :loading="store.loading">
      <tr v-for="request in store.requests" :key="request.id" class="transition hover:bg-slate-800/40">
        <td class="px-6 py-4 text-sm text-white">{{ request.destination }}</td>
        <td class="px-6 py-4 text-sm text-slate-300">{{ formatDate(request.start_date) }}</td>
        <td class="px-6 py-4 text-sm text-slate-300">{{ formatDate(request.end_date) }}</td>
        <td class="px-6 py-4">
          <span
            class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
            :class="statusConfig[request.status]?.class"
          >
            {{ statusConfig[request.status]?.label }}
          </span>
        </td>
        <td class="px-6 py-4 text-sm text-slate-300">
          <template v-if="request.admin">
            {{ request.admin.first_name }} {{ request.admin.last_name }}
          </template>
          <span v-else class="text-slate-500">-</span>
        </td>
        <td class="px-6 py-4 text-sm text-slate-300">
          {{ new Date(request.created_at).toLocaleDateString() }}
        </td>
        <td class="px-6 py-4">
          <button
            v-if="request.status === 'requested'"
            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-500/20 hover:text-red-400 cursor-pointer"
            title="Cancel Request"
            @click="cancelTarget = request"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </td>
      </tr>
    </AppTable>

    <!-- Pagination -->
    <div v-if="(store.meta as any)?.last_page > 1" class="mt-4 flex justify-center gap-2">
      <button
        v-for="page in (store.meta as any).last_page"
        :key="page"
        class="rounded-lg px-3 py-1.5 text-sm font-medium transition cursor-pointer"
        :class="page === currentPage
          ? 'bg-indigo-600 text-white'
          : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
        @click="goToPage(page)"
      >
        {{ page }}
      </button>
    </div>

    <CancelTravelRequestModal
      :show="!!cancelTarget"
      :travel-request="cancelTarget"
      :loading="store.cancelling"
      @close="cancelTarget = null"
      @confirm="handleCancel"
    />
  </div>
</template>
