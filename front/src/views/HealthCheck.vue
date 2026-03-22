<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/services/api'

interface HealthData {
  laravel_version: string
  mysql_version: string | null
  database_connection_status: 'Success' | 'Error'
}

const health = ref<HealthData | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

async function fetchHealth() {
  loading.value = true
  error.value = null
  try {
    const { data } = await apiClient.get<{ data: HealthData }>('/healthy')
    health.value = data.data
  } catch (e: unknown) {
    error.value = e instanceof Error ? e.message : 'Failed to reach API'
  } finally {
    loading.value = false
  }
}

onMounted(fetchHealth)
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center p-6">
    <div class="w-full max-w-lg">
      <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-white tracking-tight">
          Onfly
          <span class="text-indigo-400">Corporate Travel</span>
        </h1>
        <p class="mt-2 text-slate-400 text-sm">System Health Monitor</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-slate-600 border-t-indigo-400"></div>
      </div>

      <!-- Error State -->
      <div
        v-else-if="error"
        class="rounded-xl border border-red-500/30 bg-red-500/10 p-6 text-center"
      >
        <div class="text-red-400 text-lg font-semibold mb-2">Connection Failed</div>
        <p class="text-red-300/70 text-sm">{{ error }}</p>
        <button
          class="mt-4 rounded-lg bg-red-500/20 px-4 py-2 text-sm font-medium text-red-300 transition hover:bg-red-500/30 cursor-pointer"
          @click="fetchHealth"
        >
          Retry
        </button>
      </div>

      <!-- Health Data -->
      <div
        v-else-if="health"
        class="rounded-xl border border-slate-700/50 bg-slate-800/60 backdrop-blur-sm shadow-2xl overflow-hidden"
      >
        <div class="border-b border-slate-700/50 px-6 py-4 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-white">API Status</h2>
          <span
            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium"
            :class="health.database_connection_status === 'Success'
              ? 'bg-emerald-500/15 text-emerald-400'
              : 'bg-red-500/15 text-red-400'"
          >
            <span
              class="h-2 w-2 rounded-full"
              :class="health.database_connection_status === 'Success'
                ? 'bg-emerald-400'
                : 'bg-red-400'"
            ></span>
            {{ health.database_connection_status === 'Success' ? 'Healthy' : 'Unhealthy' }}
          </span>
        </div>

        <div class="divide-y divide-slate-700/50">
          <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-500/10">
                <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                </svg>
              </div>
              <span class="text-sm text-slate-300">Laravel Version</span>
            </div>
            <span class="font-mono text-sm font-medium text-white">{{ health.laravel_version }}</span>
          </div>

          <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500/10">
                <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                </svg>
              </div>
              <span class="text-sm text-slate-300">MySQL Version</span>
            </div>
            <span class="font-mono text-sm font-medium text-white">{{ health.mysql_version ?? 'N/A' }}</span>
          </div>

          <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg" :class="health.database_connection_status === 'Success' ? 'bg-emerald-500/10' : 'bg-red-500/10'">
                <svg class="h-5 w-5" :class="health.database_connection_status === 'Success' ? 'text-emerald-400' : 'text-red-400'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
              </div>
              <span class="text-sm text-slate-300">Database Connection</span>
            </div>
            <span
              class="text-sm font-medium"
              :class="health.database_connection_status === 'Success' ? 'text-emerald-400' : 'text-red-400'"
            >{{ health.database_connection_status }}</span>
          </div>
        </div>
      </div>

      <p class="text-center text-slate-600 text-xs mt-8">&copy; {{ new Date().getFullYear() }} Onfly Corporate Travel</p>
    </div>
  </div>
</template>
