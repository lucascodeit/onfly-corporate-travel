<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getProfile, updateProfile } from '@/services/profile'
import { useAuthStore } from '@/stores/auth'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'

const authStore = useAuthStore()

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
})

const loading = ref(true)
const saving = ref(false)
const errors = ref<Record<string, string>>({})
const success = ref(false)

async function loadProfile() {
  try {
    const { data } = await getProfile()
    form.value.first_name = data.data.first_name
    form.value.last_name = data.data.last_name
    form.value.email = data.data.email
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  errors.value = {}
  success.value = false
  saving.value = true

  try {
    const { data } = await updateProfile(form.value)
    if (authStore.user) {
      authStore.user.first_name = data.data.first_name
      authStore.user.last_name = data.data.last_name
      authStore.user.email = data.data.email
      localStorage.setItem('user', JSON.stringify(authStore.user))
    }
    success.value = true
  } catch (e: unknown) {
    const axiosError = e as { response?: { data?: { errors?: Record<string, string[]> } } }
    const fieldErrors = axiosError.response?.data?.errors || {}
    for (const [key, msgs] of Object.entries(fieldErrors)) {
      errors.value[key] = msgs[0] || ''
    }
  } finally {
    saving.value = false
  }
}

onMounted(loadProfile)
</script>

<template>
  <div class="mx-auto max-w-2xl">
    <h1 class="mb-6 text-2xl font-bold text-white">Profile Settings</h1>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-slate-600 border-t-indigo-400" />
    </div>

    <form
      v-else
      class="rounded-xl border border-slate-700 bg-slate-800/60 p-6 space-y-5"
      @submit.prevent="handleSubmit"
    >
      <div
        v-if="success"
        class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400"
      >
        Profile updated successfully.
      </div>

      <div class="grid gap-5 sm:grid-cols-2">
        <AppInput
          v-model="form.first_name"
          label="First Name"
          :error="errors.first_name"
        />
        <AppInput
          v-model="form.last_name"
          label="Last Name"
          :error="errors.last_name"
        />
      </div>

      <AppInput
        v-model="form.email"
        label="Email"
        type="email"
        :error="errors.email"
      />

      <div class="flex justify-end pt-4">
        <AppButton type="submit" :loading="saving">Save Changes</AppButton>
      </div>
    </form>
  </div>
</template>
