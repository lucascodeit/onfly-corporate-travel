<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAdminTravelRequestStore } from '@/stores/adminTravelRequests'
import { getUsers, type User } from '@/services/users'
import type { TravelRequest, TravelRequestFilters } from '@/services/travelRequests'
import AppButton from '@/components/AppButton.vue'
import AppTable from '@/components/AppTable.vue'
import ReviewTravelRequestModal from './components/ReviewTravelRequestModal.vue'

const store = useAdminTravelRequestStore()

const users = ref<User[]>([])
const selectedUserId = ref<number | undefined>(undefined)
const filterStartDate = ref('')
const filterEndDate = ref('')
const filterStatus = ref('')
const currentPage = ref(1)

const reviewTarget = ref<TravelRequest | null>(null)
const reviewAction = ref<'approve' | 'disapprove'>('approve')

const columns = [
  { key: 'requester', label: 'Requester' },
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

const activeFilters = computed<TravelRequestFilters>(() => {
  const filters: TravelRequestFilters = {}
  if (filterStartDate.value) filters.start_date = filterStartDate.value
  if (filterEndDate.value) filters.end_date = filterEndDate.value
  if (filterStatus.value) filters.status = filterStatus.value
  return filters
})

function openReviewModal(request: TravelRequest, action: 'approve' | 'disapprove') {
  reviewTarget.value = request
  reviewAction.value = action
}

async function handleConfirm() {
  if (!reviewTarget.value) return
  const id = reviewTarget.value.id
  const action = reviewAction.value

  if (action === 'approve') {
    await store.approve(id)
  } else {
    await store.disapprove(id)
  }

  reviewTarget.value = null
  await store.fetchRequests(currentPage.value, selectedUserId.value, activeFilters.value)
}

async function handleFilterChange() {
  currentPage.value = 1
  await store.fetchRequests(1, selectedUserId.value, activeFilters.value)
}

async function goToPage(page: number) {
  currentPage.value = page
  await store.fetchRequests(page, selectedUserId.value, activeFilters.value)
}

function formatDate(dateStr: string) {
  return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

function isActioning(requestId: number) {
  return store.approvingId === requestId || store.disapprovingId === requestId
}

onMounted(async () => {
  store.fetchRequests()
  try {
    const { data } = await getUsers()
    users.value = data.data
  } catch {
    /* users filter is non-critical */
  }
})
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">Manage Travel Requests</h1>
    </div>

    <div class="mb-4 flex flex-wrap items-end gap-4">
      <div>
        <label for="user-filter" class="mb-1 block text-sm text-slate-300">Filter by user</label>
        <select
          id="user-filter"
          v-model="selectedUserId"
          class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-1.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
          @change="handleFilterChange"
        >
          <option :value="undefined">All users</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.first_name }} {{ user.last_name }}
          </option>
        </select>
      </div>
      <div>
        <label for="filter-status" class="mb-1 block text-sm text-slate-300">Status</label>
        <select
          id="filter-status"
          v-model="filterStatus"
          class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-1.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
          @change="handleFilterChange"
        >
          <option value="">All statuses</option>
          <option value="requested">Requested</option>
          <option value="approved">Approved</option>
          <option value="disapproved">Disapproved</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
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
        <td class="px-6 py-4 text-sm text-white">
          {{ request.user?.first_name }} {{ request.user?.last_name }}
        </td>
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
          <div class="flex items-center gap-1">
            <AppButton
              size="sm"
              variant="primary"
              :loading="store.approvingId === request.id"
              :disabled="request.status === 'cancelled' || isActioning(request.id)"
              @click="openReviewModal(request, 'approve')"
            >
              Approve
            </AppButton>
            <AppButton
              size="sm"
              variant="danger"
              :loading="store.disapprovingId === request.id"
              :disabled="request.status === 'cancelled' || isActioning(request.id)"
              @click="openReviewModal(request, 'disapprove')"
            >
              Disapprove
            </AppButton>
          </div>
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

    <ReviewTravelRequestModal
      :show="!!reviewTarget"
      :travel-request="reviewTarget"
      :action="reviewAction"
      :loading="reviewAction === 'approve' ? store.approvingId === reviewTarget?.id : store.disapprovingId === reviewTarget?.id"
      @close="reviewTarget = null"
      @confirm="handleConfirm"
    />
  </div>
</template>
