<template>
  <div class="list">
    <div class="page-header">
      <h1 class="page-title">All Entries</h1>
      <p class="page-subtitle">
        Browse, search, and manage your transaction records.
      </p>
    </div>
    <EntryTable />
    <ToastNotification
      v-if="toast.show"
      :message="toast.message"
      :type="toast.type"
      @close="toast.show = false"
    />
  </div>
</template>

<script setup>
import { reactive } from "vue";
import EntryTable from "../components/EntryTable.vue";
import ToastNotification from "../components/ToastNotification.vue";

const toast = reactive({ show: false, message: "", type: "success" });
</script>

<style scoped>
.page-header {
  margin-bottom: var(--space-6);
}
.page-title {
  font-size: var(--text-2xl);
  font-weight: 700;
  color: var(--color-text);
}
.page-subtitle {
  margin-top: var(--space-1);
  color: var(--color-text-muted);
  font-size: var(--text-sm);
}
</style>
