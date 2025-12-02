<script setup lang="ts">
import type { Task } from '~/types/task'
import type { User } from '~/types/auth'

definePageMeta({
  middleware: ['auth'],
})

const { isParent } = useUserRole()

const tasks = ref<Task[]>([])
const children = ref<User[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

// Current week start date
const currentWeekStart = ref(getMonday(new Date()))

// Modal state
const isModalOpen = ref(false)
const selectedTask = ref<Task | null>(null)

// Get Monday of the current week
function getMonday(date: Date): Date {
  const d = new Date(date)
  const day = d.getDay()
  const diff = d.getDate() - day + (day === 0 ? -6 : 1)
  return new Date(d.setDate(diff))
}

// Navigate weeks
function previousWeek() {
  const newDate = new Date(currentWeekStart.value)
  newDate.setDate(newDate.getDate() - 7)
  currentWeekStart.value = newDate
}

function nextWeek() {
  const newDate = new Date(currentWeekStart.value)
  newDate.setDate(newDate.getDate() + 7)
  currentWeekStart.value = newDate
}

// Week title (e.g., "Lapkritis 27 - Gruodis 3, 2025")
const weekTitle = computed(() => {
  const start = new Date(currentWeekStart.value)
  const end = new Date(start)
  end.setDate(end.getDate() + 6)

  const startMonth = start.toLocaleDateString('lt-LT', { month: 'long' })
  const endMonth = end.toLocaleDateString('lt-LT', { month: 'long' })
  const year = start.getFullYear()

  if (start.getMonth() === end.getMonth()) {
    return `${startMonth.charAt(0).toUpperCase() + startMonth.slice(1)} ${start.getDate()} - ${end.getDate()}, ${year}`
  }
  return `${startMonth.charAt(0).toUpperCase() + startMonth.slice(1)} ${start.getDate()} - ${endMonth.charAt(0).toUpperCase() + endMonth.slice(1)} ${end.getDate()}, ${year}`
})

// Generate week days
const weekDays = computed(() => {
  const days = []
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  const dayNames = ['Pirmadienis', 'Antradienis', 'Trečiadienis', 'Ketvirtadienis', 'Penktadienis', 'Šeštadienis', 'Sekmadienis']

  for (let i = 0; i < 7; i++) {
    const date = new Date(currentWeekStart.value)
    date.setDate(date.getDate() + i)

    const dateString = date.toISOString().split('T')[0]

    days.push({
      date: dateString,
      dayName: dayNames[i],
      dayNameShort: date.toLocaleDateString('lt-LT', { weekday: 'short' }),
      dayNumber: date.getDate(),
      isToday: date.getTime() === today.getTime(),
    })
  }

  return days
})

// Get tasks for a specific day and child
function getTasksForDayAndChild(dateString: string, childId: number) {
  return tasks.value.filter((task) => {
    if (task.user_id !== childId) return false
    
    const taskStart = new Date(task.start_date)
    const taskEnd = new Date(task.end_date)
    const currentDate = new Date(dateString)

    return currentDate >= taskStart && currentDate <= taskEnd
  })
}

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

// Redirect if not parent
onMounted(() => {
  if (!isParent.value) {
    navigateTo('/')
    return
  }
  fetchData()
})
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Užduotys pagal vaikus</h1>
      <NuxtLink to="/" class="back-link">
        ← Grįžti į užduotis
      </NuxtLink>
    </div>

    <div v-if="loading" class="loading-state">
      Kraunama...
    </div>

    <div v-else-if="error" class="error-state">
      {{ error }}
    </div>

    <div v-else class="statistics-container">
      <!-- Header with navigation -->
      <div class="calendar-header">
        <button @click="previousWeek" class="nav-button">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        <h2 class="week-title">{{ weekTitle }}</h2>
        <button @click="nextWeek" class="nav-button">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>

      <!-- Statistics Table -->
      <div class="stats-table-wrapper">
        <table class="stats-table">
          <thead>
            <tr>
              <th class="child-header">Vaikas</th>
              <th 
                v-for="day in weekDays" 
                :key="day.date"
                class="day-header"
                :class="{ 'is-today': day.isToday }"
              >
                <div class="day-name">{{ day.dayNameShort }}</div>
                <div class="day-number">{{ day.dayNumber }}</div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="child in children" :key="child.id" class="child-row">
              <td class="child-name-cell">
                <div class="child-name">{{ child.name }}</div>
              </td>
              <td 
                v-for="day in weekDays" 
                :key="`${child.id}-${day.date}`"
                class="tasks-cell"
                :class="{ 'is-today': day.isToday }"
              >
                <div class="tasks-container">
                  <div
                    v-for="task in getTasksForDayAndChild(day.date, child.id)"
                    :key="task.id"
                    class="task-square"
                    :class="`task-${task.status}`"
                    :title="task.title"
                    @click="openTaskModal(task)"
                  >
                    <span class="task-name">{{ task.title }}</span>
                  </div>
                </div>
              </td>
            </tr>
            <tr v-if="children.length === 0">
              <td :colspan="8" class="empty-state">
                Nėra vaikų paskyrų
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Legend -->
      <div class="legend">
        <div class="legend-item">
          <div class="legend-color task-in_progress"></div>
          <span>Vykdoma</span>
        </div>
        <div class="legend-item">
          <div class="legend-color task-pending"></div>
          <span>Laukia patvirtinimo</span>
        </div>
        <div class="legend-item">
          <div class="legend-color task-completed"></div>
          <span>Atlikta</span>
        </div>
        <div class="legend-item">
          <div class="legend-color task-cancelled"></div>
          <span>Atmesta</span>
        </div>
      </div>
    </div>

    <!-- Task Modal -->
    <CalendarTaskModal
      v-if="isModalOpen"
      :task="selectedTask"
      :initial-date="selectedTask?.start_date"
      @close="closeModal"
      @save="handleSaveTask"
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

.calendar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.week-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.nav-button {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border: none;
  background: #f3f4f6;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  color: #4b5563;
}

.nav-button:hover {
  background: #e5e7eb;
  color: #1f2937;
}

.stats-table-wrapper {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  overflow-x: auto;
}

.stats-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 800px;
}

.stats-table th,
.stats-table td {
  padding: 1rem;
  text-align: center;
  border-bottom: 1px solid #e5e7eb;
}

.child-header {
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
  text-align: left;
  min-width: 150px;
}

.day-header {
  background: #f9fafb;
  min-width: 120px;
}

.day-header.is-today {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.day-name {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6b7280;
}

.day-header.is-today .day-name {
  color: rgba(255, 255, 255, 0.9);
}

.day-number {
  font-size: 1.25rem;
  font-weight: 700;
  margin-top: 0.25rem;
  color: #1f2937;
}

.day-header.is-today .day-number {
  color: white;
}

.child-row:hover {
  background: #f9fafb;
}

.child-name-cell {
  text-align: left;
  background: #fafafa;
}

.child-name {
  font-weight: 600;
  color: #1f2937;
}

.tasks-cell {
  vertical-align: top;
  padding: 0.75rem;
}

.tasks-cell.is-today {
  background: rgba(102, 126, 234, 0.05);
}

.tasks-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-height: 60px;
}

.task-square {
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  border-left: 3px solid;
  text-align: left;
}

.task-square:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.task-name {
  font-size: 0.75rem;
  font-weight: 500;
  color: #1f2937;
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.task-pending {
  background: #fef3c7;
  border-left-color: #f59e0b;
}

.task-in_progress {
  background: #dbeafe;
  border-left-color: #3b82f6;
}

.task-completed {
  background: #d1fae5;
  border-left-color: #10b981;
}

.task-cancelled {
  background: #fee2e2;
  border-left-color: #ef4444;
}

.empty-state {
  padding: 3rem;
  text-align: center;
  color: #6b7280;
  font-style: italic;
}

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
  border-radius: 4px;
  border-left: 3px solid;
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

  .legend {
    flex-wrap: wrap;
    gap: 1rem;
  }

  .week-title {
    font-size: 1.125rem;
  }
}
</style>
