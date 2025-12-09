<script setup lang="ts">
import { shopIcons, DEFAULT_ICON } from '~/utils/shopIcons'

const props = defineProps<{
  modelValue: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const selectedIcon = computed({
  get: () => props.modelValue || DEFAULT_ICON,
  set: (value: string) => emit('update:modelValue', value)
})
</script>

<template>
  <div class="icon-picker">
    <label class="picker-label">Ikona</label>
    <div class="icons-grid">
      <button
        v-for="icon in shopIcons"
        :key="icon.name"
        type="button"
        class="icon-option"
        :class="{ selected: selectedIcon === icon.name }"
        @click="selectedIcon = icon.name"
        :title="icon.label"
      >
        <span class="icon-svg" v-html="icon.svg"></span>
        <span class="icon-label">{{ icon.label }}</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.icon-picker {
  margin-bottom: 1.5rem;
}

.picker-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #374151;
}

.icons-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.icon-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 0.75rem;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 70px;
}

.icon-option:hover {
  border-color: #a5b4fc;
  background: #f5f3ff;
}

.icon-option.selected {
  border-color: #4f46e5;
  background: #eef2ff;
}

.icon-svg {
  width: 32px;
  height: 32px;
  color: #4f46e5;
}

.icon-svg :deep(svg) {
  width: 100%;
  height: 100%;
}

.icon-label {
  font-size: 0.75rem;
  color: #6b7280;
}

.icon-option.selected .icon-label {
  color: #4f46e5;
  font-weight: 600;
}
</style>
