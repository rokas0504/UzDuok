<script setup lang="ts">
import type { Task, TaskFormData } from '~/types/task'
import type { User } from '~/types/auth'

const props = defineProps<{
  task?: Task | null
  initialDate?: string
}>()

const emit = defineEmits<{
  close: []
  save: []
}>()

const { isParent, isChild } = useUserRole()
const isEditing = computed(() => !!props.task)
const isViewOnly = computed(() => isChild.value)

const children = ref<User[]>([])
const loadingChildren = ref(false)

const formData = ref<TaskFormData>({
  title: props.task?.title || '',
  description: props.task?.description || '',
  price: props.task ? parseFloat(props.task.price) : 0,
  is_repeated: props.task?.is_repeated || false,
  start_date: props.task?.start_date || props.initialDate || new Date().toISOString().split('T')[0],
  end_date: props.task?.end_date || props.initialDate || new Date().toISOString().split('T')[0],
  user_id: props.task?.user_id || 0,
})

const loading = ref(false)
const error = ref<string | null>(null)

// Fetch child users for parents
async function fetchChildren() {
  if (!isParent.value) return

  loadingChildren.value = true
  try {
    const response = await useFetchChildren()
    children.value = response.users

    // Set default child if creating new task and there are children
    if (!props.task && children.value.length > 0 && !formData.value.user_id) {
      formData.value.user_id = children.value[0].id
    }
  } catch (err: any) {
    console.error('Error fetching children:', err)
  } finally {
    loadingChildren.value = false
  }
}

// Fetch children when component mounts
onMounted(() => {
  fetchChildren()
})

async function handleSubmit() {
  loading.value = true
  error.value = null

  try {
    if (isEditing.value && props.task) {
      await useUpdateTask(props.task.id, formData.value)
    } else {
      await useCreateTask(formData.value)
    }
    emit('save')
  } catch (err: any) {
    error.value = err.data?.message || 'Klaida išsaugant užduotį'
    console.error('Error saving task:', err)
  } finally {
    loading.value = false
  }
}

async function markAsPending() {
  if (!props.task) return

  loading.value = true
  error.value = null

  try {
    await useUpdateTaskStatus(props.task.id, 'pending')
    emit('save')
  } catch (err: any) {
    error.value = err.data?.message || 'Klaida atnaujinant užduotį'
    console.error('Error updating task status:', err)
  } finally {
    loading.value = false
  }
}
async function approveTask() {
  if (!props.task) return

  loading.value = true
  error.value = null

  try {
    await useUpdateTaskStatus(props.task.id, 'completed')
    emit('save')
  } catch (err: any) {
    error.value = err.data?.message || 'Klaida patvirtinant užduotį'
    console.error('Error approving task:', err)
  } finally {
    loading.value = false
  }
}

async function declineTask() {
  if (!props.task) return

  loading.value = true
  error.value = null

  try {
    await useUpdateTaskStatus(props.task.id, 'cancelled')
    emit('save')
  } catch (err: any) {
    error.value = err.data?.message || 'Klaida atmetant užduotį'
    console.error('Error declining task:', err)
  } finally {
    loading.value = false
  }
}

async function deleteTask() {
  if (!props.task) return

  if (!confirm('Ar tikrai norite ištrinti šią užduotį?')) {
    return
  }

  loading.value = true
  error.value = null

  try {
    await useDeleteTask(props.task.id)
    emit('save')
  } catch (err: any) {
    error.value = err.data?.message || 'Klaida trinant užduotį'
    console.error('Error deleting task:', err)
  } finally {
    loading.value = false
  }
}

const showApproveDecline = computed(() => {
  return isParent.value && props.task?.status === 'pending'
})
</script>

<template>
  <div class="modal-overlay" @click.self="emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h3>{{ isViewOnly ? 'Užduotis' : (isEditing ? 'Redaguoti užduotį' : 'Nauja užduotis') }}</h3>
        <button class="close-button" @click="emit('close')">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="modal-form">
        <!-- Title -->
        <div class="form-group">
          <label for="title">Pavadinimas</label>
          <input
            id="title"
            v-model="formData.title"
            type="text"
            required
            placeholder="Įveskite užduoties pavadinimą"
            class="form-input"
            :disabled="isViewOnly"
            :readonly="isViewOnly"
          />
        </div>

        <!-- Description -->
        <div class="form-group">
          <label for="description">Aprašymas</label>
          <textarea
            id="description"
            v-model="formData.description"
            rows="3"
            placeholder="Įveskite aprašymą (neprivaloma)"
            class="form-input"
            :disabled="isViewOnly"
            :readonly="isViewOnly"
          ></textarea>
        </div>

        <!-- Price -->
        <div class="form-group">
          <label for="price">Kaina</label>
          <input
            id="price"
            v-model.number="formData.price"
            type="number"
            step="0.01"
            min="0"
            required
            placeholder="0.00"
            class="form-input"
            :disabled="isViewOnly"
            :readonly="isViewOnly"
          />
        </div>

        <!-- Child Selection (Parent only) -->
        <div v-if="isParent && !isViewOnly" class="form-group">
          <label for="user_id">Vaikas</label>
          <select
            id="user_id"
            v-model.number="formData.user_id"
            required
            class="form-input"
            :disabled="loadingChildren"
          >
            <option value="0" disabled>Pasirinkite vaiką</option>
            <option v-for="child in children" :key="child.id" :value="child.id">
              {{ child.name }}
            </option>
          </select>
        </div>

        <!-- Dates -->
        <div class="form-row">
          <div class="form-group">
            <label for="start_date">Pradžios data</label>
            <input
              id="start_date"
              v-model="formData.start_date"
              type="date"
              required
              class="form-input"
              :disabled="isViewOnly"
              :readonly="isViewOnly"
            />
          </div>

          <div class="form-group">
            <label for="end_date">Pabaigos data</label>
            <input
              id="end_date"
              v-model="formData.end_date"
              type="date"
              required
              class="form-input"
              :disabled="isViewOnly"
              :readonly="isViewOnly"
            />
          </div>
        </div>

        <!-- Is Repeated -->
        <div class="form-group">
          <label class="checkbox-label">
            <input
              v-model="formData.is_repeated"
              type="checkbox"
              class="form-checkbox"
              :disabled="isViewOnly"
            />
            <span> Pasikartojanti užduotis</span>
          </label>
        </div>

        <!-- Error message -->
        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <!-- Actions -->
        <div class="modal-actions">
          <!-- Delete button for parents (left side) -->
          <button
            v-if="isParent && isEditing"
            type="button"
            @click="deleteTask"
            :disabled="loading"
            class="button button-danger delete-button"
          >
            {{ loading ? 'Trinamas...' : 'Ištrinti' }}
          </button>

          <div class="modal-actions-right">
            <button
              type="button"
              @click="emit('close')"
              class="button button-secondary"
            >
              {{ showApproveDecline ? 'Uždaryti' : (isViewOnly ? 'Uždaryti' : 'Atšaukti') }}
            </button>

            <!-- Parent editing task (not in_progress status) -->
            <button
              v-if="!isViewOnly && !showApproveDecline"
              type="submit"
              :disabled="loading"
              class="button button-primary"
            >
              {{ loading ? 'Išsaugoma...' : 'Išsaugoti' }}
            </button>

            <!-- Child marking task as pending -->
            <button
              v-if="isViewOnly && task"
              type="button"
              @click="markAsPending"
              :disabled="loading"
              class="button button-success"
            >
              {{ loading ? 'Keičiama...' : 'Pažymėti kaip atliktą' }}
            </button>

            <!-- Parent approve/decline buttons (when task is in_progress) -->
            <button
              v-if="showApproveDecline"
              type="button"
              @click="declineTask"
              :disabled="loading"
              class="button button-danger"
            >
              {{ loading ? 'Atmetama...' : 'Atmesti' }}
            </button>
            <button
              v-if="showApproveDecline"
              type="button"
              @click="approveTask"
              :disabled="loading"
              class="button button-success"
            >
              {{ loading ? 'Patvirtinama...' : 'Patvirtinti' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 16px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
}

.close-button {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  color: #6b7280;
  transition: all 0.2s;
}

.close-button:hover {
  background: #f3f4f6;
  color: #1f2937;
}

.modal-form {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s;
  font-family: inherit;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

textarea.form-input {
  resize: vertical;
  min-height: 80px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  cursor: pointer;
  user-select: none;
}

.form-checkbox {
  width: 18px;
  height: 18px;
  cursor: pointer;
  margin: 0;
  vertical-align: middle;
  flex-shrink: 0;
}

.error-message {
  padding: 0.75rem;
  background: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  color: #991b1b;
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.modal-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.modal-actions-right {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.delete-button {
  margin-right: auto;
}

.button {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.button-secondary {
  background: #f3f4f6;
  color: #374151;
}

.button-secondary:hover {
  background: #e5e7eb;
}

.button-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.button-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.button-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.button-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.button-success:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.button-success:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.button-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.button-danger:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.button-danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 640px) {
  .form-row {
    grid-template-columns: 1fr;
  }

  .modal-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .modal-actions-right {
    flex-direction: column;
    width: 100%;
  }

  .delete-button {
    margin-right: 0;
  }

  .button {
    width: 100%;
  }
}
</style>