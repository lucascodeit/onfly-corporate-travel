<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getUser, createUser, updateUser } from '@/services/users'
import AppInput from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'

const route = useRoute()
const router = useRouter()

const userId = computed(() => route.params.id ? Number(route.params.id) : null)
const isEditing = computed(() => !!userId.value)

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  type: 'staff' as 'admin' | 'staff',
  is_active: true,
})

const loading = ref(false)
const saving = ref(false)
const errors = ref<Record<string, string>>({})

async function loadUser() {
  if (!userId.value) return
  loading.value = true
  try {
    const { data } = await getUser(userId.value)
    form.value.first_name = data.data.first_name
    form.value.last_name = data.data.last_name
    form.value.email = data.data.email
    form.value.type = data.data.type
    form.value.is_active = data.data.is_active
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  errors.value = {}
  saving.value = true

  try {
    if (isEditing.value) {
      await updateUser(userId.value!, {
        first_name: form.value.first_name,
        last_name: form.value.last_name,
        email: form.value.email,
        is_active: form.value.is_active,
      })
    } else {
      await createUser({
        first_name: form.value.first_name,
        last_name: form.value.last_name,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
        type: form.value.type,
      })
    }
    router.push('/users')
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
  <div class="mx-auto max-w-2xl">
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
      <h1 class="mt-2 text-2xl font-bold text-white">
        {{ isEditing ? 'Edit User' : 'Create User' }}
      </h1>
    </div>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-slate-600 border-t-indigo-400" />
    </div>

    <form
      v-else
      class="rounded-xl border border-slate-700 bg-slate-800/60 p-6 space-y-5"
      @submit.prevent="handleSubmit"
    >
      <div class="grid gap-5 sm:grid-cols-2">
        <AppInput
          v-model="form.first_name"
          label="First Name"
          placeholder="John"
          :error="errors.first_name"
        />
        <AppInput
          v-model="form.last_name"
          label="Last Name"
          placeholder="Doe"
          :error="errors.last_name"
        />
      </div>

      <AppInput
        v-model="form.email"
        label="Email"
        type="email"
        placeholder="john@company.com"
        :error="errors.email"
      />

      <template v-if="!isEditing">
        <div class="grid gap-5 sm:grid-cols-2">
          <AppInput
            v-model="form.password"
            label="Password"
            type="password"
            placeholder="Min 8 characters"
            :error="errors.password"
          />
          <AppInput
            v-model="form.password_confirmation"
            label="Confirm Password"
            type="password"
            placeholder="Repeat password"
          />
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-300">Role</label>
          <select
            v-model="form.type"
            class="w-full rounded-lg border border-slate-700 bg-slate-800/50 px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            <option value="staff">Staff</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </template>

      <div v-if="isEditing" class="flex items-center gap-3">
        <label class="relative inline-flex cursor-pointer items-center">
          <input v-model="form.is_active" type="checkbox" class="peer sr-only" />
          <div class="h-6 w-11 rounded-full bg-slate-600 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-indigo-600 peer-checked:after:translate-x-full" />
        </label>
        <span class="text-sm text-slate-300">Active</span>
      </div>

      <div class="flex justify-end gap-3 pt-4">
        <AppButton variant="secondary" @click="router.push('/users')">Cancel</AppButton>
        <AppButton type="submit" :loading="saving">
          {{ isEditing ? 'Save Changes' : 'Create User' }}
        </AppButton>
      </div>
    </form>
  </div>
</template>
