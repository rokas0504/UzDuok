<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center">
      <h1 class="text-4xl font-bold text-gray-900 mb-4">
        UzDuok
      </h1>

      <div class="bg-white shadow rounded-lg p-6 max-w-md mx-auto mt-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">
          Naudotojo informacija
        </h2>
        <div class="space-y-3 text-left">
          <div>
            <!--  TODO  Informacija neatsivaizduoja, sutvarkyti         -->
            <span class="font-medium text-gray-600">Vardas:</span>
            <span class="ml-2 text-gray-900">{{ user?.name }}</span>
          </div>
          <div>
            <span class="font-medium text-gray-600">El. paštas:</span>
            <span class="ml-2 text-gray-900">{{ user?.email }}</span>
          </div>
          <div>
            <span class="font-medium text-gray-600">Rolė:</span>
            <span class="ml-2 text-gray-900 capitalize">{{ user?.role }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: ['auth'],
})

const { user } = useAuth()
</script>
