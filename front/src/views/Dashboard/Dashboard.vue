<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-white">
      Welcome back, {{ authStore.user?.first_name }}!
    </h1>
    <p class="mt-2 text-slate-400">
      You are signed in as <span class="font-medium text-indigo-400">{{ authStore.user?.type }}</span>.
    </p>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <div class="rounded-xl border border-slate-700 bg-slate-800/60 p-6">
        <div class="flex items-center gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-500/10">
            <svg class="h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-slate-400">Your role</p>
            <p class="text-lg font-semibold capitalize text-white">{{ authStore.user?.type }}</p>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-slate-700 bg-slate-800/60 p-6">
        <div class="flex items-center gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-500/10">
            <svg class="h-6 w-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-slate-400">Email</p>
            <p class="text-lg font-semibold text-white">{{ authStore.user?.email }}</p>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-slate-700 bg-slate-800/60 p-6">
        <div class="flex items-center gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-500/10">
            <svg class="h-6 w-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-slate-400">Account status</p>
            <p class="text-lg font-semibold text-emerald-400">Active</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
