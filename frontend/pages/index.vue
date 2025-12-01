<script setup lang="ts">
import type { Task } from '~/types/task'

definePageMeta({
  middleware: ['auth'],
})

const { user } = useAuth()
const { isParent } = useUserRole()

const tasks = ref<Task[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

async function fetchTasks() {
  loading.value = true
  error.value = null
  try {
    const response = await useFetchTasks()
    tasks.value = response.tasks
  } catch (err: any) {
    error.value = err.data?.message || 'Nepavyko užkrauti užduočių'
    console.error('Error fetching tasks:', err)
  } finally {
    loading.value = false
  }
}

// Fetch tasks on mount
onMounted(() => {
  fetchTasks()
})
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <div class="header-left">
        <h1 class="page-title">Užduotys</h1>
        <NuxtLink v-if="isParent" to="/statistics" class="stats-link">
          📊 Statistika
        </NuxtLink>
      </div>
      <div class="user-info">
        <span class="user-name">{{ user?.name }}</span>
        <span class="user-role">{{ user?.role }}</span>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      Kraunama...
    </div>

    <div v-else-if="error" class="error-state">
      {{ error }}
    </div>

    <CalendarWeekCalendar
      v-else
      :tasks="tasks"
      @refresh="fetchTasks"
    />
  </div>
</template>

<style scoped>
.page-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 0;
}

.page-header {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: white;
  margin: 0;
}

.stats-link {
  color: white;
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  transition: all 0.2s;
  font-size: 0.875rem;
}

.stats-link:hover {
  background: rgba(255, 255, 255, 0.3);
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  color: white;
}

.user-name {
  font-size: 1.125rem;
  font-weight: 600;
}

.user-role {
  font-size: 0.875rem;
  opacity: 0.9;
  text-transform: capitalize;
}

.loading-state,
.error-state {
  max-width: 1400px;
  margin: 2rem auto;
  padding: 2rem;
  text-align: center;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.loading-state {
  color: #6b7280;
  font-size: 1.125rem;
}

.error-state {
  color: #ef4444;
  font-size: 1.125rem;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .page-title {
    font-size: 2rem;
  }

  .user-info {
    align-items: flex-start;
  }
}
</style>
