<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationStore } from '@/stores/notifications'
import AppButton from '@/components/AppButton.vue'

const store = useNotificationStore()
const router = useRouter()

const currentPage = ref(1)
const activeFilter = ref<string | undefined>(undefined)

const filterOptions = [
  { label: 'All', value: undefined },
  { label: 'Unread', value: 'unread' },
  { label: 'Read', value: 'read' },
]

function setFilter(value: string | undefined) {
  activeFilter.value = value
  currentPage.value = 1
  store.fetchNotifications(1, value)
}

function goToPage(page: number) {
  currentPage.value = page
  store.fetchNotifications(page, activeFilter.value)
}

function openNotification(id: number) {
  router.push({ name: 'notification-detail', params: { id } })
}

async function handleMarkAsRead(id: number) {
  await store.markAsRead(id)
  await store.fetchNotifications(currentPage.value, activeFilter.value)
}

function timeAgo(dateStr: string) {
  const now = new Date()
  const date = new Date(dateStr)
  const seconds = Math.floor((now.getTime() - date.getTime()) / 1000)

  if (seconds < 60) return 'Just now'
  const minutes = Math.floor(seconds / 60)
  if (minutes < 60) return `${minutes}m ago`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours}h ago`
  const days = Math.floor(hours / 24)
  if (days < 30) return `${days}d ago`
  return date.toLocaleDateString()
}

onMounted(() => {
  store.fetchNotifications()
})
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-white">Notifications</h1>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-4 flex gap-2">
      <button
        v-for="option in filterOptions"
        :key="option.label"
        class="rounded-lg px-4 py-2 text-sm font-medium transition cursor-pointer"
        :class="activeFilter === option.value
          ? 'bg-indigo-600 text-white'
          : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white'"
        @click="setFilter(option.value)"
      >
        {{ option.label }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="store.loading" class="py-12 text-center text-slate-400">
      <svg class="mx-auto h-8 w-8 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
      <p class="mt-2 text-sm">Loading notifications...</p>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="store.notifications.length === 0"
      class="rounded-lg border border-slate-700 bg-slate-800/50 py-16 text-center"
    >
      <svg class="mx-auto h-12 w-12 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
      </svg>
      <p class="mt-3 text-sm text-slate-400">No notifications found.</p>
    </div>

    <!-- Notification Cards -->
    <div v-else class="space-y-2">
      <div
        v-for="notification in store.notifications"
        :key="notification.id"
        class="flex items-start gap-4 rounded-lg border p-4 transition cursor-pointer"
        :class="notification.is_read
          ? 'border-slate-700 bg-slate-800/30 hover:bg-slate-800/50'
          : 'border-indigo-500/30 bg-indigo-500/5 hover:bg-indigo-500/10'"
        @click="openNotification(notification.id)"
      >
        <!-- Unread Indicator -->
        <div class="mt-1.5 shrink-0">
          <div
            class="h-2.5 w-2.5 rounded-full"
            :class="notification.is_read ? 'bg-slate-600' : 'bg-indigo-500'"
          />
        </div>

        <!-- Content -->
        <div class="min-w-0 flex-1">
          <p class="text-sm" :class="notification.is_read ? 'text-slate-300' : 'text-white font-medium'">
            {{ notification.message }}
          </p>
          <div class="mt-1 flex items-center gap-3 text-xs text-slate-400">
            <span>From {{ notification.user_from?.first_name }} {{ notification.user_from?.last_name }}</span>
            <span>&middot;</span>
            <span>{{ timeAgo(notification.created_at) }}</span>
          </div>
        </div>

        <!-- Mark as Read Button -->
        <div v-if="!notification.is_read" class="shrink-0" @click.stop>
          <AppButton size="sm" variant="secondary" @click="handleMarkAsRead(notification.id)">
            Mark read
          </AppButton>
        </div>
      </div>
    </div>

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
  </div>
</template>
