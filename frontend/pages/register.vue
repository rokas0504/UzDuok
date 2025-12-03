<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Sukurti paskyrą šeimos nariui
        </h2>
        <!-- <p class="mt-2 text-center text-sm text-gray-600">
          Arba
          <NuxtLink to="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
            Prisijungti
          </NuxtLink>
        </p> -->
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <!-- Success message -->
        <div v-if="successMessage" class="rounded-md bg-green-50 p-4">
          <div class="flex">
            <div class="ml-3">
              <h3 class="text-sm font-medium text-green-800">
                {{ successMessage }}
              </h3>
            </div>
          </div>
        </div>

        <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                {{ errorMessage }}
              </h3>
            </div>
          </div>
        </div>

        <div class="rounded-md shadow-sm space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Vardas</label>
            <input
              id="name"
              v-model="form.name"
              name="name"
              type="text"
              autocomplete="name"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="John Doe"
            >
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">
              {{ errors.name }}
            </p>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">El. paštas</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="john@example.com"
            >
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
              {{ errors.email }}
            </p>
          </div>

          <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Rolė</label>
            <select
              id="role"
              v-model="form.role"
              name="role"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">Select a role</option>
              <option value="parent">Parent</option>
              <option value="child">Child</option>
            </select>
            <p v-if="errors.role" class="mt-1 text-sm text-red-600">
              {{ errors.role }}
            </p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Slaptažodis</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="new-password"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
            >
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">
              {{ errors.password }}
            </p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Pakartoti slaptažodį</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              name="password_confirmation"
              type="password"
              autocomplete="new-password"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
            >
            <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">
              {{ errors.password_confirmation }}
            </p>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading">Kuriama paskyra...</span>
            <span v-else>Registruoti šeimos narį</span>
          </button>
          <NuxtLink to="/" class="back-link">
            ← Grįžti į užduotis
          </NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: ['auth'],
})

const { register } = useAuth()
const route = useRoute() // added this

const form = ref({
  name: '',
  email: '',
  role: '',
  password: '',
  password_confirmation: '',
})

const errors = ref<Record<string, string>>({})
const errorMessage = ref('')
const successMessage = ref('') // added this
const loading = ref(false)

const handleRegister = async () => {
  loading.value = true
  errors.value = {}
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await register(form.value)

    // show success message
    successMessage.value = 'Paskyra sėkmingai sukurta.'

    // po trumpų kelių sekundžių grąžinti atgal:
    setTimeout(() => {
      const redirect = (route.query.redirect || route.query.from) as string | undefined

      if (redirect) {
        navigateTo(redirect)
      }
      else if (typeof window !== 'undefined' && window.history.length > 1) {
        window.history.back()
      }
      else {
        navigateTo('/')
      }
    }, 3000)
  }
  catch (error: any) {
    if (error.response?.data?.errors) {
      // Laravel validation errors
      const validationErrors = error.response.data.errors
      Object.keys(validationErrors).forEach((key) => {
        errors.value[key] = Array.isArray(validationErrors[key])
          ? validationErrors[key][0]
          : validationErrors[key]
      })
    }
    else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message
    }
    else {
      errorMessage.value = 'An error occurred during registration. Please try again.'
    }
  }
  finally {
    loading.value = false
  }
}
</script>
