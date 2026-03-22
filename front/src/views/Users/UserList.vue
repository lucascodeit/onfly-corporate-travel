<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getUsers, deleteUser, type User } from '@/services/users'
import AppButton from '@/components/AppButton.vue'
import AppTable from '@/components/AppTable.vue'
import DeleteUserModal from './components/DeleteUserModal.vue'

const router = useRouter()
const users = ref<User[]>([])
const loading = ref(true)
const deleteTarget = ref<User | null>(null)
const deleting = ref(false)

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'type', label: 'Role' },
  { key: 'is_active', label: 'Status' },
]

async function fetchUsers() {
  loading.value = true
  try {
    const { data } = await getUsers()
    users.value = data.data
  } finally {
    loading.value = false
  }
}

async function handleDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await deleteUser(deleteTarget.value.id)
    deleteTarget.value = null
    await fetchUsers()
  } finally {
    deleting.value = false
  }
}

onMounted(fetchUsers)
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">Users</h1>
      <AppButton @click="router.push('/users/create')">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        New User
      </AppButton>
    </div>

    <AppTable :columns="columns" :loading="loading">
      <tr v-for="user in users" :key="user.id" class="hover:bg-slate-800/40 transition">
        <td class="px-6 py-4 text-sm text-white">
          {{ user.first_name }} {{ user.last_name }}
        </td>
        <td class="px-6 py-4 text-sm text-slate-300">{{ user.email }}</td>
        <td class="px-6 py-4">
          <span
            class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium capitalize"
            :class="user.type === 'admin' ? 'bg-indigo-500/15 text-indigo-400' : 'bg-slate-500/15 text-slate-400'"
          >
            {{ user.type }}
          </span>
        </td>
        <td class="px-6 py-4">
          <span
            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
            :class="user.is_active ? 'bg-emerald-500/15 text-emerald-400' : 'bg-red-500/15 text-red-400'"
          >
            <span class="h-1.5 w-1.5 rounded-full" :class="user.is_active ? 'bg-emerald-400' : 'bg-red-400'" />
            {{ user.is_active ? 'Active' : 'Inactive' }}
          </span>
        </td>
        <td class="px-6 py-4">
          <div class="flex items-center gap-2">
            <button
              class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-700 hover:text-white cursor-pointer"
              title="Edit"
              @click="router.push(`/users/${user.id}/edit`)"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
              </svg>
            </button>
            <button
              class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-700 hover:text-white cursor-pointer"
              title="Change Password"
              @click="router.push(`/users/${user.id}/password`)"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
              </svg>
            </button>
            <button
              class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-500/20 hover:text-red-400 cursor-pointer"
              title="Delete"
              @click="deleteTarget = user"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
              </svg>
            </button>
          </div>
        </td>
      </tr>
    </AppTable>

    <DeleteUserModal
      :show="!!deleteTarget"
      :user="deleteTarget"
      :loading="deleting"
      @close="deleteTarget = null"
      @confirm="handleDelete"
    />
  </div>
</template>
