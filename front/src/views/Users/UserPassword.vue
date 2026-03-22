<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getUser, changeUserPassword } from '@/services/users'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'

const route = useRoute()
const router = useRouter()
const userId = Number(route.params.id)

const userName = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(true)
const saving = ref(false)
const errors = ref<Record<string, string>>({})
const success = ref(false)

async function loadUser() {
  try {
    const { data } = await getUser(userId)
    userName.value = `${data.data.first_name} ${data.data.last_name}`
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  errors.value = {}
  success.value = false
  saving.value = true

  try {
    await changeUserPassword(userId, password.value, passwordConfirmation.value)
    success.value = true
    password.value = ''
    passwordConfirmation.value = ''
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

onMounted(loadUser)
</script>

<template>
  <div class="mx-auto max-w-md">
    <div class="mb-6">
      <button
        class="flex items-center gap-1 text-sm text-slate-400 transition hover:text-white cursor-pointer"
        @click="router.push('/users')"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Back to users
      </button>
      <h1 class="mt-2 text-2xl font-bold text-white">Reset Password</h1>
      <p v-if="userName" class="mt-1 text-sm text-slate-400">For {{ userName }}</p>
    </div>

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
        Password updated successfully.
      </div>

      <AppInput
        v-model="password"
        label="New Password"
        type="password"
        placeholder="Min 8 characters"
        :error="errors.password"
      />

      <AppInput
        v-model="passwordConfirmation"
        label="Confirm Password"
        type="password"
        placeholder="Repeat password"
      />

      <div class="flex justify-end gap-3 pt-2">
        <AppButton variant="secondary" @click="router.push('/users')">Cancel</AppButton>
        <AppButton type="submit" :loading="saving">Reset Password</AppButton>
      </div>
    </form>
  </div>
</template>
