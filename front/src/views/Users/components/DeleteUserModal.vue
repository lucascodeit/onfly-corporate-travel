<script setup lang="ts">
import type { User } from '@/services/users'
import AppModal from '@/components/AppModal.vue'
import AppButton from '@/components/AppButton.vue'

interface Props {
  show: boolean
  user: User | null
  loading?: boolean
}

defineProps<Props>()

defineEmits<{
  close: []
  confirm: []
}>()
</script>

<template>
  <AppModal :show="show" title="Delete User" @close="$emit('close')">
    <p class="text-sm text-slate-300">
      Are you sure you want to delete
      <span class="font-semibold text-white">{{ user?.first_name }} {{ user?.last_name }}</span>?
      This action cannot be undone.
    </p>

    <template #actions>
      <AppButton variant="secondary" @click="$emit('close')">Cancel</AppButton>
      <AppButton variant="danger" :loading="loading" @click="$emit('confirm')">Delete</AppButton>
    </template>
  </AppModal>
</template>
