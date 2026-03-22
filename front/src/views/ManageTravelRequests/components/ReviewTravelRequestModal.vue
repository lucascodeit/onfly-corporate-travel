<script setup lang="ts">
import type { TravelRequest } from '@/services/travelRequests'
import AppModal from '@/components/AppModal.vue'
import AppButton from '@/components/AppButton.vue'

interface Props {
  show: boolean
  travelRequest: TravelRequest | null
  action: 'approve' | 'disapprove'
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
  <AppModal
    :show="show"
    :title="action === 'approve' ? 'Approve Travel Request' : 'Disapprove Travel Request'"
    @close="$emit('close')"
  >
    <p class="text-sm text-slate-300">
      Are you sure you want to
      <span :class="action === 'approve' ? 'text-emerald-400' : 'text-red-400'" class="font-semibold">
        {{ action }}
      </span>
      the travel request to
      <span class="font-semibold text-white">{{ travelRequest?.destination }}</span>?
    </p>
    <p class="mt-1 text-xs text-slate-400">
      Requested by
      <span class="text-slate-300">{{ travelRequest?.user?.first_name }} {{ travelRequest?.user?.last_name }}</span>
    </p>
    <p class="mt-1 text-xs text-slate-400">
      {{ formatDate(travelRequest?.start_date) }} &mdash; {{ formatDate(travelRequest?.end_date) }}
    </p>

    <template #actions>
      <AppButton variant="secondary" @click="$emit('close')">Cancel</AppButton>
      <AppButton
        :variant="action === 'approve' ? 'primary' : 'danger'"
        :loading="loading"
        @click="$emit('confirm')"
      >
        {{ action === 'approve' ? 'Yes, approve' : 'Yes, disapprove' }}
      </AppButton>
    </template>
  </AppModal>
</template>
