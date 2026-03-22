<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue'
</script>

<template>
  <div class="flex h-screen bg-slate-900">
    <AppSidebar />
    <main class="flex-1 overflow-y-auto">
      <div class="p-6">
        <RouterView />
      </div>
    </main>
  </div>
</template>
