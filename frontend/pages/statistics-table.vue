<script setup lang="ts">
import type { Task, TaskStatus } from '~/types/task'
import type { User } from '~/types/auth'

definePageMeta({
  middleware: ['auth'],
})

const { isParent, isChild } = useUserRole()
const { user } = useAuth()

const tasks = ref<Task[]>([])
const children = ref<User[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

// Modal state
const isModalOpen = ref(false)
const selectedTask = ref<Task | null>(null)

// Filters
const selectedChild = ref<number | null>(null)
const selectedStatus = ref<TaskStatus | null>(null)
const startDateFrom = ref<string>('')
const startDateTo = ref<string>('')
const endDateFrom = ref<string>('')
const endDateTo = ref<string>('')
const createdAtFrom = ref<string>('')
const createdAtTo = ref<string>('')

// Status options
const statusOptions: { value: TaskStatus; label: string }[] = [
  { value: 'in_progress', label: 'Vykdoma' },
  { value: 'pending', label: 'Laukia patvirtinimo' },
  { value: 'completed', label: 'Atlikta' },
  { value: 'cancelled', label: 'Atmesta' },
]

// Get child name by ID
function getChildName(userId: number): string {
  const child = children.value.find(c => c.id === userId)
  return child?.name || 'Nežinomas'
}

// Format date for display
function formatDate(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('lt-LT', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

// Format datetime for display
function formatDateTime(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('lt-LT', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// Get status label
function getStatusLabel(status: TaskStatus): string {
  const option = statusOptions.find(o => o.value === status)
  return option?.label || status
}

// Filtered tasks
const filteredTasks = computed(() => {
  return tasks.value.filter(task => {
    // Filter by child
    if (selectedChild.value !== null && task.user_id !== selectedChild.value) {
      return false
    }

    // Filter by status
    if (selectedStatus.value !== null && task.status !== selectedStatus.value) {
      return false
    }

    // Filter by start date range
    if (startDateFrom.value) {
      const taskStart = new Date(task.start_date)
      const filterFrom = new Date(startDateFrom.value)
      if (taskStart < filterFrom) return false
    }
    if (startDateTo.value) {
      const taskStart = new Date(task.start_date)
      const filterTo = new Date(startDateTo.value)
      filterTo.setHours(23, 59, 59, 999)
      if (taskStart > filterTo) return false
    }

    // Filter by end date range
    if (endDateFrom.value) {
      const taskEnd = new Date(task.end_date)
      const filterFrom = new Date(endDateFrom.value)
      if (taskEnd < filterFrom) return false
    }
    if (endDateTo.value) {
      const taskEnd = new Date(task.end_date)
      const filterTo = new Date(endDateTo.value)
      filterTo.setHours(23, 59, 59, 999)
      if (taskEnd > filterTo) return false
    }

    // Filter by created_at range
    if (createdAtFrom.value) {
      const taskCreated = new Date(task.created_at)
      const filterFrom = new Date(createdAtFrom.value)
      if (taskCreated < filterFrom) return false
    }
    if (createdAtTo.value) {
      const taskCreated = new Date(task.created_at)
      const filterTo = new Date(createdAtTo.value)
      filterTo.setHours(23, 59, 59, 999)
      if (taskCreated > filterTo) return false
    }

    return true
  })
})

// Open task modal
function openTaskModal(task: Task) {
  selectedTask.value = task
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
  selectedTask.value = null
}

function handleSaveTask() {
  closeModal()
  fetchData()
}

// Clear all filters
function clearFilters() {
  selectedChild.value = null
  selectedStatus.value = null
  startDateFrom.value = ''
  startDateTo.value = ''
  endDateFrom.value = ''
  endDateTo.value = ''
  createdAtFrom.value = ''
  createdAtTo.value = ''
}

// Fetch data
async function fetchData() {
  loading.value = true
  error.value = null
  try {
    const [tasksResponse, childrenResponse] = await Promise.all([
      useFetchTasks(),
      useFetchChildren(),
    ])
    tasks.value = tasksResponse.tasks
    children.value = childrenResponse.users
  } catch (err: any) {
    error.value = err.data?.message || 'Nepavyko užkrauti duomenų'
    console.error('Error fetching data:', err)
  } finally {
    loading.value = false
  }
}

// Initialize based on role
onMounted(() => {
  fetchData()

  // If child, auto-select themselves
  if (isChild.value && user.value) {
    selectedChild.value = user.value.id
  }
})
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <div class="header-left">
        <h1 class="page-title">Statistika</h1>
      </div>
      <div class="header-right">
        <NuxtLink to="/points-history" class="header-link">
          Taškų istorija
        </NuxtLink>
        <NuxtLink to="/" class="back-link">
          ← Grįžti į užduotis
        </NuxtLink>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      Kraunama...
    </div>

    <div v-else-if="error" class="error-state">
      {{ error }}
    </div>

    <div v-else class="statistics-container">
      <!-- Filters -->
      <div class="filters-container">
        <h3 class="filters-title">Filtrai</h3>
        <div class="filters-grid">
          <!-- Child filter (only for parents) -->
          <div v-if="isParent" class="filter-group">
            <label class="filter-label">Vaikas</label>
            <select v-model="selectedChild" class="filter-select">
              <option :value="null">Visi vaikai</option>
              <option v-for="child in children" :key="child.id" :value="child.id">
                {{ child.name }}
              </option>
            </select>
          </div>

          <!-- Status filter -->
          <div class="filter-group">
            <label class="filter-label">Statusas</label>
            <select v-model="selectedStatus" class="filter-select">
              <option :value="null">Visi statusai</option>
              <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </div>

          <!-- Start date range -->
          <div class="filter-group filter-group-range">
            <label class="filter-label">Pradžios data</label>
            <div class="date-range">
              <input
                v-model="startDateFrom"
                type="date"
                class="filter-input"
                placeholder="Nuo"
              />
              <span class="range-separator">—</span>
              <input
                v-model="startDateTo"
                type="date"
                class="filter-input"
                placeholder="Iki"
              />
            </div>
          </div>

          <!-- End date range -->
          <div class="filter-group filter-group-range">
            <label class="filter-label">Pabaigos data</label>
            <div class="date-range">
              <input
                v-model="endDateFrom"
                type="date"
                class="filter-input"
                placeholder="Nuo"
              />
              <span class="range-separator">—</span>
              <input
                v-model="endDateTo"
                type="date"
                class="filter-input"
                placeholder="Iki"
              />
            </div>
          </div>

          <!-- Created at range -->
          <div class="filter-group filter-group-range">
            <label class="filter-label">Sukūrimo data</label>
            <div class="date-range">
              <input
                v-model="createdAtFrom"
                type="date"
                class="filter-input"
                placeholder="Nuo"
              />
              <span class="range-separator">—</span>
              <input
                v-model="createdAtTo"
                type="date"
                class="filter-input"
                placeholder="Iki"
              />
            </div>
          </div>

          <!-- Clear filters button -->
          <div class="filter-group filter-group-button">
            <button @click="clearFilters" class="clear-filters-btn">
              Išvalyti filtrus
            </button>
          </div>
        </div>
      </div>

      <!-- Results count -->
      <div class="results-count">
        Rasta užduočių: <strong>{{ filteredTasks.length }}</strong>
      </div>

      <!-- Statistics Table -->
      <div class="stats-table-wrapper">
        <table class="stats-table">
          <thead>
            <tr>
              <th>Vaikas</th>
              <th>Statusas</th>
              <th>Užduoties pavadinimas</th>
              <th>Pradžios data</th>
              <th>Pabaigos data</th>
              <th>Sukūrimo data</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="task in filteredTasks" :key="task.id" class="task-row" @click="openTaskModal(task)">
              <td class="child-cell">{{ getChildName(task.user_id) }}</td>
              <td class="status-cell">
                <span class="status-badge" :class="`status-${task.status}`">
                  {{ getStatusLabel(task.status) }}
                </span>
              </td>
              <td class="title-cell">{{ task.title }}</td>
              <td class="date-cell">{{ formatDate(task.start_date) }}</td>
              <td class="date-cell">{{ formatDate(task.end_date) }}</td>
              <td class="date-cell">{{ formatDateTime(task.created_at) }}</td>
            </tr>
            <tr v-if="filteredTasks.length === 0">
              <td colspan="6" class="empty-state">
                Nėra užduočių pagal pasirinktus filtrus
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Task Modal -->
      <CalendarTaskModal
        v-if="isModalOpen"
        :task="selectedTask"
        :initial-date="selectedTask?.start_date"
        @close="closeModal"
        @save="handleSaveTask"
      />

      <!-- Legend -->
      <div class="legend">
        <div class="legend-item">
          <div class="legend-color status-in_progress"></div>
          <span>Vykdoma</span>
        </div>
        <div class="legend-item">
          <div class="legend-color status-pending"></div>
          <span>Laukia patvirtinimo</span>
        </div>
        <div class="legend-item">
          <div class="legend-color status-completed"></div>
          <span>Atlikta</span>
        </div>
        <div class="legend-item">
          <div class="legend-color status-cancelled"></div>
          <span>Atmesta</span>
        </div>
      </div>
    </div>
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
  gap: 1rem;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-link {
  color: white;
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 1rem;
  background: #10b981;
  border-radius: 8px;
  transition: all 0.2s;
}

.header-link:hover {
  background: #059669;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: white;
  margin: 0;
}

.back-link {
  color: white;
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  transition: all 0.2s;
}

.back-link:hover {
  background: rgba(255, 255, 255, 0.3);
}

.statistics-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* Filters */
.filters-container {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.filters-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 1rem 0;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group-range {
  grid-column: span 1;
}

.filter-group-button {
  display: flex;
  align-items: flex-end;
}

.filter-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.filter-select,
.filter-input {
  padding: 0.625rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.875rem;
  color: #1f2937;
  background: white;
  transition: all 0.2s;
}

.filter-select:focus,
.filter-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.date-range {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.date-range .filter-input {
  flex: 1;
  min-width: 0;
}

.range-separator {
  color: #9ca3af;
  font-weight: 500;
}

.clear-filters-btn {
  padding: 0.625rem 1rem;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.clear-filters-btn:hover {
  background: #e5e7eb;
}

/* Results count */
.results-count {
  background: white;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
  color: #4b5563;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Table */
.stats-table-wrapper {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  overflow-x: auto;
}

.stats-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 900px;
}

.stats-table th,
.stats-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.stats-table th {
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.task-row {
  cursor: pointer;
  transition: background 0.2s;
}

.task-row:hover {
  background: #f3f4f6;
}

.child-cell {
  font-weight: 500;
  color: #1f2937;
}

.title-cell {
  color: #1f2937;
  max-width: 250px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.date-cell {
  color: #6b7280;
  font-size: 0.875rem;
  white-space: nowrap;
}

/* Status badges */
.status-cell {
  white-space: nowrap;
}

.status-badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.status-in_progress {
  background: #dbeafe;
  color: #1d4ed8;
  border: 1px solid #93c5fd;
}

.status-pending {
  background: #fef3c7;
  color: #b45309;
  border: 1px solid #fcd34d;
}

.status-completed {
  background: #d1fae5;
  color: #047857;
  border: 1px solid #6ee7b7;
}

.status-cancelled {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
}

.empty-state {
  padding: 3rem;
  text-align: center;
  color: #6b7280;
  font-style: italic;
}

/* Legend */
.legend {
  display: flex;
  justify-content: center;
  gap: 2rem;
  margin-top: 1.5rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #4b5563;
}

.legend-color {
  width: 16px;
  height: 16px;
  border-radius: 9999px;
}

.legend-color.status-in_progress {
  background: #dbeafe;
  border: 2px solid #3b82f6;
}

.legend-color.status-pending {
  background: #fef3c7;
  border: 2px solid #f59e0b;
}

.legend-color.status-completed {
  background: #d1fae5;
  border: 2px solid #10b981;
}

.legend-color.status-cancelled {
  background: #fee2e2;
  border: 2px solid #ef4444;
}

/* Loading and error states */
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

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .date-range {
    flex-direction: column;
    align-items: stretch;
  }

  .range-separator {
    text-align: center;
  }

  .legend {
    flex-wrap: wrap;
    gap: 1rem;
  }
}
</style>
