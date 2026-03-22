<script setup lang="ts">
interface Props {
  label?: string
  error?: string
  type?: string
  modelValue?: string
  placeholder?: string
  disabled?: boolean
}

withDefaults(defineProps<Props>(), {
  label: '',
  error: '',
  type: 'text',
  modelValue: '',
  placeholder: '',
  disabled: false,
})

defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <div>
    <label v-if="label" class="mb-1 block text-sm font-medium text-slate-300">
      {{ label }}
    </label>
    <input
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      class="w-full rounded-lg border bg-slate-800/50 px-3 py-2 text-sm text-white placeholder-slate-500 transition focus:outline-none focus:ring-2 focus:ring-indigo-500"
      :class="error ? 'border-red-500' : 'border-slate-700'"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
    <p v-if="error" class="mt-1 text-xs text-red-400">{{ error }}</p>
  </div>
</template>
