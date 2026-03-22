<script setup lang="ts">
interface Props {
  show: boolean
  title?: string
}

defineProps<Props>()

defineEmits<{
  close: []
  confirm: []
}>()
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
        @click.self="$emit('close')"
      >
        <div class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-800 shadow-2xl">
          <div class="flex items-center justify-between border-b border-slate-700 px-6 py-4">
            <h3 class="text-lg font-semibold text-white">{{ title }}</h3>
            <button
              class="text-slate-400 transition hover:text-white cursor-pointer"
              @click="$emit('close')"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="px-6 py-4">
            <slot />
          </div>
          <div class="flex justify-end gap-3 border-t border-slate-700 px-6 py-4">
            <slot name="actions" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
