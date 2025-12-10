<script setup lang="ts">
import type { User } from '~/types/auth'

const props = defineProps<{
  child: User
}>()

const emit = defineEmits<{
  close: []
  success: []
}>()

const formData = ref({
  points: 0,
  reason: '',
})

const loading = ref(false)
const error = ref<string | null>(null)

async function handleSubmit() {
  loading.value = true
  error.value = null

  try {
    await useDeductPoints(props.child.id, formData.value.points, formData.value.reason)
    emit('success')
  } catch (err: any) {
    error.value = err.data?.message || 'Klaida minusuojant taškus'
    console.error('Error deducting points:', err)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="modal-overlay" @click.self="emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Minusuoti taškus: {{ child.name }}</h3>
        <button class="close-button" @click="emit('close')">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="modal-form">
        <!-- Child info -->
        <div class="child-info">
          <span class="child-label">Vaikas:</span>
          <span class="child-name">{{ child.name }}</span>
          <span class="child-points">Dabartiniai taškai: {{ child.points }}</span>
        </div>

        <!-- Points -->
        <div class="form-group">
          <label for="points">Taškai</label>
          <input
            id="points"
            v-model.number="formData.points"
            type="number"
            step="0.01"
            min="0.01"
            required
            placeholder="Įveskite taškų skaičių"
            class="form-input"
          />
        </div>

        <!-- Reason -->
        <div class="form-group">
          <label for="reason">Priežastis</label>
          <textarea
            id="reason"
            v-model="formData.reason"
            rows="3"
            required
            maxlength="255"
            placeholder="Įveskite priežastį (max 255 simboliai)"
            class="form-input"
          ></textarea>
          <span class="char-count">{{ formData.reason.length }}/255</span>
        </div>

        <!-- Error message -->
        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <!-- Actions -->
        <div class="modal-actions">
          <button
            type="button"
            @click="emit('close')"
            class="button button-secondary"
          >
            Atšaukti
          </button>

          <button
            type="submit"
            :disabled="loading"
            class="button button-danger"
          >
            {{ loading ? 'Minusuojama...' : 'Minusuoti taškus' }}
          </button>
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
  max-width: 500px;
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

.child-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 1rem;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.child-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.child-name {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
}

.child-points {
  font-size: 0.875rem;
  color: #4b5563;
  font-weight: 500;
}

.form-group {
  margin-bottom: 1.5rem;
  position: relative;
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
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

textarea.form-input {
  resize: vertical;
  min-height: 80px;
}

.char-count {
  position: absolute;
  bottom: -1.25rem;
  right: 0;
  font-size: 0.75rem;
  color: #9ca3af;
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
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
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
  .modal-actions {
    flex-direction: column;
  }

  .button {
    width: 100%;
  }
}
</style>