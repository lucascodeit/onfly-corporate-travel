<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useTravelRequestStore } from '@/stores/travelRequests'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'

const router = useRouter()
const store = useTravelRequestStore()

const form = ref({
  destination: '',
  start_date: '',
  end_date: '',
})

const errors = ref<Record<string, string>>({})

async function handleSubmit() {
  errors.value = {}

  try {
    await store.create(form.value)
    router.push('/travel-requests')
  } catch (e: unknown) {
    const axiosError = e as { response?: { data?: { errors?: Record<string, string[]> } } }
    const fieldErrors = axiosError.response?.data?.errors || {}
    for (const [key, msgs] of Object.entries(fieldErrors)) {
      errors.value[key] = msgs[0] || ''
    }
  }
}
</script>

<template>
  <div class="mx-auto max-w-2xl">
    <div class="mb-6">
      <button
        class="flex items-center gap-1 text-sm text-slate-400 transition hover:text-white cursor-pointer"
        @click="router.push('/travel-requests')"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Back to travel requests
      </button>
      <h1 class="mt-2 text-2xl font-bold text-white">New Travel Request</h1>
    </div>

    <form
      class="rounded-xl border border-slate-700 bg-slate-800/60 p-6 space-y-5"
      @submit.prevent="handleSubmit"
    >
      <AppInput
        v-model="form.destination"
        label="Destination"
        placeholder="e.g. São Paulo"
        :error="errors.destination"
      />

      <div class="grid gap-5 sm:grid-cols-2">
        <AppInput
          v-model="form.start_date"
          label="Start Date"
          type="date"
          :error="errors.start_date"
        />
        <AppInput
          v-model="form.end_date"
          label="End Date"
          type="date"
          :error="errors.end_date"
        />
      </div>

      <div class="flex justify-end gap-3 pt-4">
        <AppButton variant="secondary" @click="router.push('/travel-requests')">Cancel</AppButton>
        <AppButton type="submit" :loading="store.saving" :disabled="store.saving">
          Create Request
        </AppButton>
      </div>
    </form>
  </div>
</template>
