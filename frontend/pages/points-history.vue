<script setup lang="ts">
import type { PointTransaction, PointTransactionReason, PointTransactionFilters } from '~/types/points'
import type { User } from '~/types/auth'

definePageMeta({
  middleware: ['auth'],
})

const { isParent, isChild } = useUserRole()
const { user } = useAuth()

const transactions = ref<PointTransaction[]>([])
const children = ref<User[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

// Filters - default to last month
const today = new Date()
const monthAgo = new Date(today)
monthAgo.setMonth(monthAgo.getMonth() - 1)

const selectedChild = ref<number | null>(null)
const selectedReason = ref<PointTransactionReason | null>(null)
const startDate = ref<string>(monthAgo.toISOString().split('T')[0])
const endDate = ref<string>(today.toISOString().split('T')[0])

// Reason options
const reasonOptions: { value: PointTransactionReason; label: string }[] = [
  { value: 'task_completed', label: 'Užduotis atlikta' },
  { value: 'task_cancelled', label: 'Užduotis atmesta' },
  { value: 'shop_purchase', label: 'Pirkinys parduotuvėje' },
]

// Get child name by ID
function getChildName(userId: number): string {
  const child = children.value.find(c => c.id === userId)
  return child?.name || 'Nežinomas'
}

// Format date for display
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

// Get reason label
function getReasonLabel(reason: PointTransactionReason): string {
  const option = reasonOptions.find(o => o.value === reason)
  return option?.label || reason
}

// Format amount with sign
function formatAmount(amount: string): string {
  const num = parseFloat(amount)
  if (num > 0) {
    return `+${num}`
  }
  return num.toString()
}

// Get amount class
function getAmountClass(amount: string): string {
  const num = parseFloat(amount)
  return num >= 0 ? 'amount-positive' : 'amount-negative'
}

// Fetch transactions
async function fetchTransactions() {
  loading.value = true
  error.value = null
  try {
    const filters: PointTransactionFilters = {
      child_id: selectedChild.value,
      reason: selectedReason.value,
      start_date: startDate.value,
      end_date: endDate.value,
    }
    const response = await useFetchPointTransactions(filters)
    transactions.value = response.transactions
  } catch (err: any) {
    error.value = err.data?.message || 'Nepavyko užkrauti taškų istorijos'
    console.error('Error fetching transactions:', err)
  } finally {
    loading.value = false
  }
}

// Fetch children
async function fetchChildren() {
  try {
    const response = await useFetchChildren()
    children.value = response.users
  } catch (err: any) {
    console.error('Error fetching children:', err)
  }
}

// Clear filters and reset to defaults
function clearFilters() {
  selectedChild.value = null
  selectedReason.value = null
  const today = new Date()
  const monthAgo = new Date(today)
  monthAgo.setMonth(monthAgo.getMonth() - 1)
  startDate.value = monthAgo.toISOString().split('T')[0]
  endDate.value = today.toISOString().split('T')[0]
}

// Initialize based on role
onMounted(async () => {
  await fetchChildren()

  // If child, auto-select themselves
  if (isChild.value && user.value) {
    selectedChild.value = user.value.id
  }

  await fetchTransactions()
})
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Taškų istorija</h1>
      <NuxtLink to="/statistics-table" class="back-link">
        ← Grįžti į statistiką
      </NuxtLink>
    </div>

    <div v-if="loading" class="loading-state">
      Kraunama...
    </div>

    <div v-else-if="error" class="error-state">
      {{ error }}
    </div>

    <div v-else class="history-container">
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

          <!-- Reason filter -->
          <div class="filter-group">
            <label class="filter-label">Įvykis</label>
            <select v-model="selectedReason" class="filter-select">
              <option :value="null">Visi įvykiai</option>
              <option v-for="reason in reasonOptions" :key="reason.value" :value="reason.value">
                {{ reason.label }}
              </option>
            </select>
          </div>

          <!-- Date range -->
          <div class="filter-group">
            <label class="filter-label">Data nuo</label>
            <input
              v-model="startDate"
              type="date"
              class="filter-input"
            />
          </div>

          <div class="filter-group">
            <label class="filter-label">Data iki</label>
            <input
              v-model="endDate"
              type="date"
              class="filter-input"
            />
          </div>

          <!-- Action buttons -->
          <div class="filter-group filter-group-buttons">
            <button @click="fetchTransactions" class="search-btn">
              Ieškoti
            </button>
            <button @click="clearFilters" class="clear-filters-btn">
              Išvalyti filtrus
            </button>
          </div>
        </div>
      </div>

      <!-- Results count -->
      <div class="results-count">
        Rasta įrašų: <strong>{{ transactions.length }}</strong>
      </div>

      <!-- Transactions Table -->
      <div class="table-wrapper">
        <table class="history-table">
          <thead>
            <tr>
              <th>Vaikas</th>
              <th>Įvykis</th>
              <th>Taškai</th>
              <th>Balansas</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="transaction in transactions" :key="transaction.id">
              <td class="child-cell">{{ transaction.user?.name || getChildName(transaction.user_id) }}</td>
              <td class="reason-cell">
                <span class="reason-badge" :class="`reason-${transaction.reason}`">
                  {{ getReasonLabel(transaction.reason) }}
                </span>
              </td>
              <td class="amount-cell" :class="getAmountClass(transaction.amount)">
                {{ formatAmount(transaction.amount) }}
              </td>
              <td class="balance-cell">{{ transaction.new_balance }}</td>
              <td class="date-cell">{{ formatDateTime(transaction.created_at) }}</td>
            </tr>
            <tr v-if="transactions.length === 0">
              <td colspan="5" class="empty-state">
                Nėra įrašų pagal pasirinktus filtrus
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Legend -->
      <div class="legend">
        <div class="legend-item">
          <div class="legend-color reason-task_completed"></div>
          <span>Užduotis atlikta</span>
        </div>
        <div class="legend-item">
          <div class="legend-color reason-task_cancelled"></div>
          <span>Užduotis atmesta</span>
        </div>
        <div class="legend-item">
          <div class="legend-color reason-shop_purchase"></div>
          <span>Pirkinys parduotuvėje</span>
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
  max-width: 1200px;
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

.history-container {
  max-width: 1200px;
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
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group-buttons {
  display: flex;
  align-items: flex-end;
  gap: 0.5rem;
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

.search-btn {
  padding: 0.625rem 1.25rem;
  background: #667eea;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.search-btn:hover {
  background: #5a67d8;
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
.table-wrapper {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  overflow-x: auto;
}

.history-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 700px;
}

.history-table th,
.history-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.history-table th {
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.child-cell {
  font-weight: 500;
  color: #1f2937;
}

.reason-cell {
  white-space: nowrap;
}

.reason-badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.reason-task_completed {
  background: #d1fae5;
  color: #047857;
  border: 1px solid #6ee7b7;
}

.reason-task_cancelled {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
}

.reason-shop_purchase {
  background: #dbeafe;
  color: #1d4ed8;
  border: 1px solid #93c5fd;
}

.amount-cell {
  font-weight: 700;
  font-size: 1rem;
}

.amount-positive {
  color: #047857;
}

.amount-negative {
  color: #b91c1c;
}

.balance-cell {
  color: #4b5563;
  font-weight: 500;
}

.date-cell {
  color: #6b7280;
  font-size: 0.875rem;
  white-space: nowrap;
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

.legend-color.reason-task_completed {
  background: #d1fae5;
  border: 2px solid #10b981;
}

.legend-color.reason-task_cancelled {
  background: #fee2e2;
  border: 2px solid #ef4444;
}

.legend-color.reason-shop_purchase {
  background: #dbeafe;
  border: 2px solid #3b82f6;
}

/* Loading and error states */
.loading-state,
.error-state {
  max-width: 1200px;
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

  .legend {
    flex-wrap: wrap;
    gap: 1rem;
  }
}
</style>
