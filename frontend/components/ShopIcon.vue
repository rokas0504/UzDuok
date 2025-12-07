<script setup lang="ts">
import { getIconSvg, DEFAULT_ICON } from '~/utils/shopIcons'

defineProps<{
  name: string
}>()
</script>

<template>
  <span 
    class="shop-icon"
    v-html="getIconSvg(name || DEFAULT_ICON)"
  ></span>
</template>

<style scoped>
.shop-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  color: #4f46e5;
}

.shop-icon :deep(svg) {
  width: 100%;
  height: 100%;
}
</style>
