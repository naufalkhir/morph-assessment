<template>
  <div class="overlay" @click.self="$emit('close')">
    <div class="modal">
      <div class="modal-header">
        <h2 class="modal-title">Edit Entry</h2>
        <button class="modal-close" @click="$emit('close')">✕</button>
      </div>
      <form @submit.prevent="submit">
        <div class="form-grid">

          <div class="form-group form-group--full">
            <label class="form-label">Description <span class="required">*</span></label>
            <input v-model="form.description" type="text" class="form-input"
              :class="{ 'form-input--error': errors.description }" maxlength="255" />
            <span v-if="errors.description" class="form-error">{{ errors.description[0] }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Amount <span class="required">*</span></label>
            <input v-model="form.amount" type="number" class="form-input"
              :class="{ 'form-input--error': errors.amount }" step="0.01" min="0.01" />
            <span v-if="errors.amount" class="form-error">{{ errors.amount[0] }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Currency <span class="required">*</span></label>
            <select v-model="form.currency" class="form-input"
              :class="{ 'form-input--error': errors.currency }">
              <option v-for="c in currencies" :key="c" :value="c">{{ c }}</option>
            </select>
            <span v-if="errors.currency" class="form-error">{{ errors.currency[0] }}</span>
          </div>

          <div class="form-group form-group--full">
            <label class="form-label">Transaction Date <span class="required">*</span></label>
            <input v-model="form.transaction_date" type="date" class="form-input"
              :class="{ 'form-input--error': errors.transaction_date }" />
            <span v-if="errors.transaction_date" class="form-error">{{ errors.transaction_date[0] }}</span>
          </div>

        </div>
        <div class="form-actions">
          <button type="button" class="btn btn--secondary" @click="$emit('close')">Cancel</button>
          <button type="submit" class="btn btn--primary" :disabled="loading">
            {{ loading ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import api from '../api/axios.js'

const props = defineProps({ entry: { type: Object, required: true } })
const emit  = defineEmits(['close', 'updated'])

const currencies = ['MYR', 'USD', 'EUR', 'GBP', 'SGD', 'AUD', 'JPY']
const loading = ref(false)
const errors  = reactive({})

const form = reactive({
  description:      props.entry.description,
  amount:           props.entry.amount,
  currency:         props.entry.currency,
  transaction_date: props.entry.transaction_date,
})

async function submit() {
  Object.keys(errors).forEach(k => delete errors[k])
  loading.value = true
  try {
    await api.put(`/entries/${props.entry.id}`, form)
    emit('updated')
  } catch (err) {
    if (err && typeof err === 'object') Object.assign(errors, err)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center;
  z-index: 200;
}
.modal {
  background: var(--color-bg);
  border-radius: var(--radius-lg);
  padding: var(--space-8);
  width: 90%; max-width: 520px;
  box-shadow: var(--shadow-lg);
  animation: fadeIn 0.2s ease;
}
.modal-header {
  display: flex; justify-content: space-between;
  align-items: center; margin-bottom: var(--space-6);
}
.modal-title { font-size: var(--text-xl); font-weight: 700; }
.modal-close {
  background: none; border: none; cursor: pointer;
  font-size: var(--text-lg); color: var(--color-text-muted);
}
.modal-close:hover { color: var(--color-text); }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-5); }
.form-group { display: flex; flex-direction: column; gap: var(--space-1); }
.form-group--full { grid-column: 1 / -1; }
.form-label { font-size: var(--text-sm); font-weight: 600; }
.required { color: var(--color-error); }
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
.form-input--error { border-color: var(--color-error-border); }
.form-error { font-size: var(--text-xs); color: var(--color-error); }
.form-actions {
  display: flex; justify-content: flex-end;
  gap: var(--space-3); margin-top: var(--space-6);
}
.btn {
  padding: var(--space-2) var(--space-6);
  border-radius: var(--radius-md);
  font-size: var(--text-sm); font-weight: 600;
  cursor: pointer; border: none; transition: var(--transition);
}
.btn--primary { background: var(--color-primary); color: #fff; }
.btn--primary:hover:not(:disabled) { background: var(--color-primary-hover); }
.btn--primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn--secondary {
  background: var(--color-bg-muted); color: var(--color-text-muted);
  border: 1px solid var(--color-border);
}
.btn--secondary:hover { background: var(--color-border); }
</style>