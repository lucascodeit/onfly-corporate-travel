<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'

const authStore = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')
const errors = ref<Record<string, string>>({})

async function handleLogin() {
  error.value = ''
  errors.value = {}
  loading.value = true

  try {
    await authStore.signIn(email.value, password.value)
    router.push('/dashboard')
  } catch (e: unknown) {
    const axiosError = e as { response?: { status?: number; data?: { message?: string; errors?: Record<string, string[]> } } }
    if (axiosError.response?.status === 422) {
      const fieldErrors = axiosError.response.data?.errors || {}
      for (const [key, msgs] of Object.entries(fieldErrors)) {
        errors.value[key] = msgs[0]
      }
    } else if (axiosError.response?.status === 401 || axiosError.response?.status === 403) {
      error.value = axiosError.response.data?.message || 'Invalid credentials.'
    } else {
      error.value = 'An unexpected error occurred.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6">
    <div class="w-full max-w-sm">
      <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-white">
          Onfly<span class="text-indigo-400">CT</span>
        </h1>
        <p class="mt-2 text-sm text-slate-400">Corporate Travel Management</p>
      </div>

      <form
        class="rounded-xl border border-slate-700/50 bg-slate-800/60 p-6 shadow-2xl backdrop-blur-sm"
        @submit.prevent="handleLogin"
      >
        <h2 class="mb-6 text-lg font-semibold text-white">Sign in</h2>

        <div
          v-if="error"
          class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400"
        >
          {{ error }}
        </div>

        <div class="space-y-4">
          <AppInput
            v-model="email"
            label="Email"
            type="email"
            placeholder="you@company.com"
            :error="errors.email"
          />

          <AppInput
            v-model="password"
            label="Password"
            type="password"
            placeholder="Enter your password"
            :error="errors.password"
          />
        </div>

        <AppButton
          type="submit"
          variant="primary"
          size="md"
          :loading="loading"
          class="mt-6 w-full"
        >
          Sign in
        </AppButton>
      </form>
    </div>
  </div>
</template>
