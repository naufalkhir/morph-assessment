<template>
  <div class="form-card">
    <!-- @submit.prevent — prevents default browser form submission -->
    <!-- handles submission via our own submit() function instead -->
    <form @submit.prevent="submit">
      <div class="form-grid">
        <!-- form-group--full spans both columns in the 2-column grid -->
        <div class="form-group form-group--full">
          <label class="form-label"
            >Description <span class="required">*</span></label
          >
          <!-- dynamically adds error class if this field has an error -->
          <input
            v-model="form.description"
            type="text"
            class="form-input"
            :class="{ 'form-input--error': errors.description }"
            placeholder="e.g. AWS Invoice March"
            maxlength="255"
          />
          <!-- only shows if errors.description exists — displays first error message -->
          <span v-if="errors.description" class="form-error">{{
            errors.description[0]
          }}</span>
        </div>

        <div class="form-group">
          <label class="form-label"
            >Amount <span class="required">*</span></label
          >
          <!-- allows decimal input -->
          <!-- browser-level validation — no zero or negative -->
          <input
            v-model="form.amount"
            type="number"
            class="form-input"
            :class="{ 'form-input--error': errors.amount }"
            placeholder="0.00"
            step="0.01"
            min="0.01"
          />
          <span v-if="errors.amount" class="form-error">{{
            errors.amount[0]
          }}</span>
        </div>

        <div class="form-group">
          <label class="form-label"
            >Currency <span class="required">*</span></label
          >
          <!-- v-for renders one option per currency — dynamic list -->
          <select
            v-model="form.currency"
            class="form-input"
            :class="{ 'form-input--error': errors.currency }"
          >
            <option v-for="c in currencies" :key="c" :value="c">{{ c }}</option>
          </select>
          <span v-if="errors.currency" class="form-error">{{
            errors.currency[0]
          }}</span>
        </div>

        <div class="form-group">
          <label class="form-label"
            >Transaction Date <span class="required">*</span></label
          >
          <!-- browser renders native date picker -->
          <input
            v-model="form.transaction_date"
            type="date"
            class="form-input"
            :class="{ 'form-input--error': errors.transaction_date }"
          />
          <span v-if="errors.transaction_date" class="form-error">{{
            errors.transaction_date[0]
          }}</span>
        </div>
      </div>

      <div class="form-actions">
        <button type="button" class="btn btn--secondary" @click="reset">
          Reset
        </button>
        <!-- disabled during loading to prevent double submission -->
        <button type="submit" class="btn btn--primary" :disabled="loading">
          {{ loading ? "Saving..." : "Save Entry" }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import api from "../api/axios.js";

// emit — allows this component to notify parent (HomeView) when form is submitted
const emit = defineEmits(["submitted"]);

// Supported currencies list
const currencies = ["MYR", "USD", "EUR", "GBP", "SGD", "AUD", "JPY"];

// reactive() for form — object with multiple related fields
const form = reactive({
  description: "",
  amount: "",
  currency: "MYR", // default to MYR
  transaction_date: "",
});

// reactive() for errors — gets populated from 422 response
const errors = reactive({});

// ref() for loading — single boolean value
const loading = ref(false);

async function submit() {
  // Clear previous errors before new submission
  Object.keys(errors).forEach((k) => delete errors[k]);

  loading.value = true;
  try {
    // POST to Laravel API — Axios interceptor handles 422 errors
    await api.post("/entries", form);
    reset();
    // Notify parent component — HomeView will show toast and redirect
    emit("submitted");
  } catch (err) {
    // err is already unwrapped by Axios interceptor — just assign to errors
    if (err && typeof err === "object") {
      Object.assign(errors, err);
    }
  } finally {
    // Always reset loading state — whether success or error
    loading.value = false;
  }
}

function reset() {
  form.description = "";
  form.amount = "";
  form.currency = "MYR";
  form.transaction_date = "";
  // Clear all error messages
  Object.keys(errors).forEach((k) => delete errors[k]);
}
</script>
<style scoped>
.form-card {
  background: var(--color-bg-subtle);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: var(--space-8);
  box-shadow: var(--shadow-sm);
  max-width: 640px;
}
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-5);
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}
.form-group--full {
  grid-column: 1 / -1;
}
.form-label {
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--color-text);
}
.required {
  color: var(--color-error);
}
.form-input {
  padding: var(--space-2) var(--space-3);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--text-base);
  color: var(--color-text);
  background: var(--color-bg);
  transition: var(--transition);
  width: 100%;
}
.form-input:focus {
  outline: none;
  border-color: var(--color-border-focus);
  box-shadow: 0 0 0 3px var(--color-primary-ring);
}
.form-input--error {
  border-color: var(--color-error-border);
}
.form-error {
  font-size: var(--text-xs);
  color: var(--color-error);
}
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-3);
  margin-top: var(--space-6);
}
.btn {
  padding: var(--space-2) var(--space-6);
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: var(--transition);
}
.btn--primary {
  background: var(--color-primary);
  color: #fff;
}
.btn--primary:hover:not(:disabled) {
  background: var(--color-primary-hover);
}
.btn--primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.btn--secondary {
  background: var(--color-bg-muted);
  color: var(--color-text-muted);
  border: 1px solid var(--color-border);
}
.btn--secondary:hover {
  background: var(--color-border);
}
</style>
