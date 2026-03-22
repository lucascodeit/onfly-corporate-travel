<script setup lang="ts">
interface Props {
  columns: { key: string; label: string }[]
  loading?: boolean
}

withDefaults(defineProps<Props>(), {
  loading: false,
})
</script>

<template>
  <div class="overflow-hidden rounded-xl border border-slate-700 bg-slate-800/60">
    <div v-if="loading" class="flex justify-center p-8">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-slate-600 border-t-indigo-400" />
    </div>
    <table v-else class="w-full text-left text-sm">
      <thead class="border-b border-slate-700 bg-slate-800">
        <tr>
          <th
            v-for="col in columns"
            :key="col.key"
            class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-slate-400"
          >
            {{ col.label }}
          </th>
          <th class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-slate-400">
            Actions
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-700/50">
        <slot />
      </tbody>
    </table>
  </div>
</template>
