<script setup lang="ts">
import type { TravelRequest } from '@/services/travelRequests'
import AppModal from '@/components/AppModal.vue'
import AppButton from '@/components/AppButton.vue'

interface Props {
  show: boolean
  travelRequest: TravelRequest | null
  loading?: boolean
}

defineProps<Props>()

defineEmits<{
  close: []
  confirm: []
}>()

function formatDate(dateStr?: string) {
  if (!dateStr) return ''
  return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<template>
  <AppModal :show="show" title="Cancel Travel Request" @close="$emit('close')">
    <p class="text-sm text-slate-300">
      Are you sure you want to cancel the travel request to
      <span class="font-semibold text-white">{{ travelRequest?.destination }}</span>?
    </p>
    <p class="mt-2 text-xs text-slate-400">
      {{ formatDate(travelRequest?.start_date) }} &mdash; {{ formatDate(travelRequest?.end_date) }}
    </p>

    <template #actions>
      <AppButton variant="secondary" @click="$emit('close')">No, keep it</AppButton>
      <AppButton variant="danger" :loading="loading" @click="$emit('confirm')">Yes, cancel request</AppButton>
    </template>
  </AppModal>
</template>
