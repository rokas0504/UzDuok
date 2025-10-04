<script setup lang="ts">
import type { NuxtError } from '#app'

defineProps<{
  error: NuxtError
}>()

const { t } = useI18n({ useScope: 'global' })
const handleError = () => clearError({ redirect: '/' })
</script>

<template>
  <div class="flex justify-center items-center min-h-screen relative">
    <div class="flex flex-col items-center gap-8 w-full flex-grow">
      <div class="flex flex-col items-center justify-center gap-2 lg:min-w-[609px] min-w-[200px] lg:min-h-[589px] min-h-[200px] relative z-5">
        <h2 class="font-medium lg:text-9xl text-7xl text-black">
          {{ error.statusCode }}
        </h2>
        <p class="lg:text-3xl text-xl font-bold text-black">
          {{ t('error_occured') }}
        </p>
        <DevOnly>
          {{ error.message }}
        </DevOnly>
      </div>
      <button
        class="text-black flex gap-2 text-sm font-medium py-3 px-8"
        @click="handleError"
      >
        {{ t('redirect_button') }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.error-inner-background::before,
.error-background::before {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
}

@media (max-width: 768px) {
  .error-background::before {
    background-size: contain;
  }
}
</style>
