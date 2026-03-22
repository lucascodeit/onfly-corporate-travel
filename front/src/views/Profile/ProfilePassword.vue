<script setup lang="ts">
import { ref } from 'vue'
import { changePassword } from '@/services/profile'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'

const form = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const saving = ref(false)
const errors = ref<Record<string, string>>({})
const success = ref(false)

async function handleSubmit() {
  errors.value = {}
  success.value = false
  saving.value = true

  try {
    await changePassword(form.value)
    success.value = true
    form.value.current_password = ''
    form.value.new_password = ''
    form.value.new_password_confirmation = ''
  } catch (e: unknown) {
    const axiosError = e as { response?: { data?: { errors?: Record<string, string[]> } } }
    const fieldErrors = axiosError.response?.data?.errors || {}
    for (const [key, msgs] of Object.entries(fieldErrors)) {
      errors.value[key] = msgs[0]
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="mx-auto max-w-md">
    <h1 class="mb-6 text-2xl font-bold text-white">Change Password</h1>

    <form
      class="rounded-xl border border-slate-700 bg-slate-800/60 p-6 space-y-5"
      @submit.prevent="handleSubmit"
    >
      <div
        v-if="success"
        class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400"
      >
        Password changed successfully.
      </div>

      <AppInput
        v-model="form.current_password"
        label="Current Password"
        type="password"
        placeholder="Enter current password"
        :error="errors.current_password"
      />

      <AppInput
        v-model="form.new_password"
        label="New Password"
        type="password"
        placeholder="Min 8 characters"
        :error="errors.new_password"
      />

      <AppInput
        v-model="form.new_password_confirmation"
        label="Confirm New Password"
        type="password"
        placeholder="Repeat new password"
      />

      <div class="flex justify-end pt-2">
        <AppButton type="submit" :loading="saving">Change Password</AppButton>
      </div>
    </form>
  </div>
</template>
