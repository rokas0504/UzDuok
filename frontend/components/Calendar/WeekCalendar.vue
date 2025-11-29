<script setup lang="ts">
import type { Task } from '~/types/task'

const props = defineProps<{
  tasks: Task[]
}>()

const emit = defineEmits<{
  refresh: []
}>()

const { isParent, isChild } = useUserRole()

// Current week start date
const currentWeekStart = ref(getMonday(new Date()))
const isModalOpen = ref(false)
const selectedTask = ref<Task | null>(null)
const selectedDate = ref<string>('')

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

  for (let i = 0; i < 7; i++) {
    const date = new Date(currentWeekStart.value)
    date.setDate(date.getDate() + i)

    const dateString = date.toISOString().split('T')[0]

    days.push({
      date: dateString,
      dayName: date.toLocaleDateString('lt-LT', { weekday: 'short' }),
      dayNumber: date.getDate(),
      isToday: date.getTime() === today.getTime(),
    })
  }

  return days
})

// Get tasks for a specific day
function getTasksForDay(dateString: string) {
  return props.tasks.filter((task) => {
    const taskStart = new Date(task.start_date)
    const taskEnd = new Date(task.end_date)
    const currentDate = new Date(dateString)

    return currentDate >= taskStart && currentDate <= taskEnd
  })
}

// Format price
function formatPrice(price: string) {
  return `${price}`
}

// Modal handlers
function openCreateModal(date: string) {
  selectedTask.value = null
  selectedDate.value = date
  isModalOpen.value = true
}

function editTask(task: Task) {
  selectedTask.value = task
  selectedDate.value = task.start_date
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
  selectedTask.value = null
  selectedDate.value = ''
}

function handleSaveTask() {
  closeModal()
  emit('refresh')
}
</script>
<template>
  <div class="week-calendar">
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

    <!-- Week grid -->
    <div class="week-grid">
      <div
        v-for="day in weekDays"
        :key="day.date"
        class="day-column"
        :class="{ 'is-today': day.isToday }"
      >
        <!-- Day header -->
        <div class="day-header">
          <div class="day-name">{{ day.dayName }}</div>
          <div class="day-number">{{ day.dayNumber }}</div>
        </div>

        <!-- Tasks for this day -->
        <div class="day-tasks">
          <div
            v-for="task in getTasksForDay(day.date)"
            :key="task.id"
            class="task-card"
            :class="`task-${task.status}`"
            @click="editTask(task)"
          >
            <div class="task-title">{{ task.title }}</div>
            <div class="task-price">{{ formatPrice(task.price) }}</div>
          </div>

          <!-- Add task button (Parent only) -->
          <button
            v-if="isParent"
            class="add-task-button"
            @click="openCreateModal(day.date)"
          >
            + Pridėti užduotį
          </button>
        </div>
      </div>
    </div>

    <!-- Task Modal -->
    <CalendarTaskModal
      v-if="isModalOpen"
      :task="selectedTask"
      :initial-date="selectedDate"
      @close="closeModal"
      @save="handleSaveTask"
    />
  </div>
</template>

<style scoped>
.week-calendar {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
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

.week-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1rem;
}

.day-column {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  min-height: 400px;
  display: flex;
  flex-direction: column;
}

.day-column.is-today .day-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.day-header {
  padding: 1rem;
  background: #f9fafb;
  text-align: center;
  border-bottom: 2px solid #e5e7eb;
}

.day-name {
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6b7280;
}

.day-column.is-today .day-name {
  color: rgba(255, 255, 255, 0.9);
}

.day-number {
  font-size: 1.5rem;
  font-weight: 700;
  margin-top: 0.25rem;
  color: #1f2937;
}

.day-column.is-today .day-number {
  color: white;
}

.day-tasks {
  padding: 1rem;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.task-card {
  padding: 0.75rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  border-left: 4px solid;
}

.task-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

.task-title {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
  font-size: 0.875rem;
}

.task-price {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

.add-task-button {
  margin-top: auto;
  padding: 0.75rem;
  border: 2px dashed #d1d5db;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.875rem;
  color: #6b7280;
  transition: all 0.2s;
  font-weight: 500;
}

.add-task-button:hover {
  border-color: #667eea;
  color: #667eea;
  background: #f9fafb;
}

@media (max-width: 1024px) {
  .week-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 768px) {
  .week-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .week-title {
    font-size: 1.125rem;
  }
}

@media (max-width: 480px) {
  .week-grid {
    grid-template-columns: 1fr;
  }
}
</style>