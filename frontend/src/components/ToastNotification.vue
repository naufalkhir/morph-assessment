<template>
  <div class="toast" :class="`toast--${type}`">
    <span class="toast-message">{{ message }}</span>
    <button class="toast-close" @click="$emit('close')">✕</button>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

const props = defineProps({
  message: { type: String, required: true },
  type: { type: String, default: 'success' },
})
const emit = defineEmits(['close'])

onMounted(() => setTimeout(() => emit('close'), 3000))
</script>

<style scoped>
.toast {
  position: fixed;
  bottom: var(--space-6);
  right: var(--space-6);
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-4) var(--space-5);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  font-size: var(--text-sm);
  font-weight: 500;
  z-index: 1000;
  animation: slideIn 0.25s ease;
}
.toast--success { background: var(--color-success-light); color: var(--color-success); border: 1px solid var(--color-success-border); }
.toast--error   { background: var(--color-error-light);   color: var(--color-error);   border: 1px solid var(--color-error-border); }
.toast-close {
  background: none;
  border: none;
  cursor: pointer;
  color: inherit;
  font-size: var(--text-base);
  line-height: 1;
  opacity: 0.7;
}
.toast-close:hover { opacity: 1; }
</style>