<template>
  <div class="home">
    <div class="page-header">
      <h1 class="page-title">New Entry</h1>
      <p class="page-subtitle">Fill in the form below to add a new transaction entry.</p>
    </div>
    <EntryForm @submitted="onSubmitted" />
    <ToastNotification v-if="toast.show" :message="toast.message" :type="toast.type" @close="toast.show = false" />
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import EntryForm from '../components/EntryForm.vue'
import ToastNotification from '../components/ToastNotification.vue'

const router = useRouter()
const toast = reactive({ show: false, message: '', type: 'success' })

function onSubmitted() {
  toast.message = 'Entry saved successfully!'
  toast.type = 'success'
  toast.show = true
  setTimeout(() => router.push('/list'), 1500)
}
</script>

<style scoped>
.page-header { margin-bottom: var(--space-6); }
.page-title { font-size: var(--text-2xl); font-weight: 700; color: var(--color-text); }
.page-subtitle { margin-top: var(--space-1); color: var(--color-text-muted); font-size: var(--text-sm); }
</style>