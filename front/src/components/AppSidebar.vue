<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const route = useRoute()
const collapsed = ref(false)
const userMenuOpen = ref(false)

function toggleSidebar() {
  collapsed.value = !collapsed.value
}

function isActive(path: string) {
  return route.path.startsWith(path)
}

async function handleLogout() {
  await authStore.signOut()
  window.location.href = '/login'
}
</script>

<template>
  <aside
    class="flex h-screen flex-col border-r border-slate-700 bg-slate-900 transition-all duration-300"
    :class="collapsed ? 'w-16' : 'w-64'"
  >
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-700 px-4 py-4">
      <span v-if="!collapsed" class="text-lg font-bold text-white">
        Onfly<span class="text-indigo-400">CT</span>
      </span>
      <button
        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-800 hover:text-white cursor-pointer"
        @click="toggleSidebar"
      >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
      </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 space-y-1 px-3 py-4">
      <RouterLink
        to="/dashboard"
        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition"
        :class="isActive('/dashboard') ? 'bg-indigo-600/20 text-indigo-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
      >
        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
        <span v-if="!collapsed">Dashboard</span>
      </RouterLink>

      <RouterLink
        v-if="authStore.isAdmin"
        to="/users"
        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition"
        :class="isActive('/users') ? 'bg-indigo-600/20 text-indigo-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
      >
        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
        </svg>
        <span v-if="!collapsed">Users</span>
      </RouterLink>
    </nav>

    <!-- User Section -->
    <div class="relative border-t border-slate-700 px-3 py-3">
      <button
        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm transition hover:bg-slate-800 cursor-pointer"
        @click="userMenuOpen = !userMenuOpen"
      >
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
          {{ authStore.user?.first_name?.charAt(0) }}{{ authStore.user?.last_name?.charAt(0) }}
        </div>
        <div v-if="!collapsed" class="flex-1 text-left">
          <p class="truncate font-medium text-white">{{ authStore.fullName }}</p>
          <p class="truncate text-xs text-slate-400">{{ authStore.user?.email }}</p>
        </div>
        <svg
          v-if="!collapsed"
          class="h-4 w-4 text-slate-400 transition"
          :class="userMenuOpen ? 'rotate-180' : ''"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
        </svg>
      </button>

      <!-- Dropdown menu -->
      <Transition
        enter-active-class="transition duration-150 ease-out"
        enter-from-class="scale-95 opacity-0"
        enter-to-class="scale-100 opacity-100"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="scale-100 opacity-100"
        leave-to-class="scale-95 opacity-0"
      >
        <div
          v-if="userMenuOpen"
          class="absolute bottom-full left-3 right-3 mb-2 rounded-lg border border-slate-700 bg-slate-800 py-1 shadow-xl"
        >
          <RouterLink
            to="/profile"
            class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 transition hover:bg-slate-700 hover:text-white"
            @click="userMenuOpen = false"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            Settings
          </RouterLink>
          <RouterLink
            to="/profile/password"
            class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 transition hover:bg-slate-700 hover:text-white"
            @click="userMenuOpen = false"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Password
          </RouterLink>
          <hr class="my-1 border-slate-700" />
          <button
            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-400 transition hover:bg-slate-700 hover:text-red-300 cursor-pointer"
            @click="handleLogout"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
            </svg>
            Sign out
          </button>
        </div>
      </Transition>
    </div>
  </aside>
</template>
