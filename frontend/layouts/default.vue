<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <NuxtLink to="/" class="text-xl font-bold text-gray-900">
                UzDuok
              </NuxtLink>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <template v-if="isAuthenticated">
              <span class="text-sm text-gray-700">
                {{ user?.name }}
              </span>

              <div v-if="isChild" class="flex items-center bg-indigo-100 px-4 py-2 rounded-md">
                <svg class="w-5 h-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span class="text-sm font-semibold text-indigo-800">
                  {{ user?.points || 0 }} taškai
                </span>
              </div>

              <button
                @click="handleLogout"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Atsijungti
              </button>
            </template>
            <!-- <template v-else>
              <NuxtLink
                to="/login"
                class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
              >
                Prisijungti
              </NuxtLink>
              <NuxtLink
                to="/register"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Registruotis
              </NuxtLink>
            </template> -->
          </div>
        </div>
      </div>
    </nav>

    <main>
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
import type { User } from '~/types/auth'

// Initialize auth - only on client side to avoid SSR issues
const isAuthenticated = ref(false)
const user = ref<User | null>(null)
const logout = ref<() => Promise<void>>(() => Promise.resolve())
const { isChild } = useUserRole()

// Only initialize auth on client side
onMounted(() => {
  const auth = useAuth()
  isAuthenticated.value = auth.isAuthenticated.value
  user.value = auth.user.value
  logout.value = auth.logout

  // Watch for changes
  watch(() => auth.isAuthenticated.value, (val) => {
    isAuthenticated.value = val
  })
  watch(() => auth.user.value, (val) => {
    user.value = val
  })
})

const handleLogout = async () => {
  try {
    await logout.value()
    // Force a full page reload to clear all state
    window.location.href = '/login'
  }
  catch (error: any) {
    console.error('Logout error:', error)
    // Even if logout fails, redirect to login
    window.location.href = '/login'
  }
}
</script>
