<template>
  <div class="table-wrapper">
    <!-- ── Toolbar: search, date filters, export button ── -->
    <div class="toolbar">
      <div class="toolbar-filters">
        <!-- @input uses debounce — waits 300ms before firing API call -->
        <input
          v-model="search"
          type="text"
          class="filter-input"
          placeholder="Search description..."
          @input="debouncedFetch"
        />
        <!-- Date range filters — fires immediately on change -->
        <input
          v-model="dateFrom"
          type="date"
          class="filter-input"
          @change="fetchEntries"
        />
        <input
          v-model="dateTo"
          type="date"
          class="filter-input"
          @change="fetchEntries"
        />
        <button class="btn btn--secondary" @click="clearFilters">Clear</button>
      </div>
      <button class="btn btn--primary" @click="exportCsv">⬇ Export CSV</button>
    </div>

    <!-- ── Loading state — shows spinner while API call is in progress ── -->
    <div v-if="loading" class="state-box">
      <div class="spinner"></div>
      <span>Loading entries...</span>
    </div>

    <!-- ── Empty state — shows when API returns no results ── -->
    <div v-else-if="!entries.length" class="state-box">
      <span>No entries found.</span>
    </div>

    <!-- ── Table — only renders when entries exist ── -->
    <div v-else class="table-scroll">
      <table class="table">
        <thead>
          <tr>
            <!-- sortable columns — clicking fires sortBy() which toggles asc/desc -->
            <th @click="sortBy('description')" class="sortable">
              Description
              <span class="sort-icon">{{ sortIcon("description") }}</span>
            </th>
            <th @click="sortBy('amount')" class="sortable">
              Amount <span class="sort-icon">{{ sortIcon("amount") }}</span>
            </th>
            <th @click="sortBy('currency')" class="sortable">
              Currency <span class="sort-icon">{{ sortIcon("currency") }}</span>
            </th>
            <th @click="sortBy('transaction_date')" class="sortable">
              Date
              <span class="sort-icon">{{ sortIcon("transaction_date") }}</span>
            </th>
            <th @click="sortBy('created_at')" class="sortable">
              Created
              <span class="sort-icon">{{ sortIcon("created_at") }}</span>
            </th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- v-for renders one row per entry — :key helps Vue track DOM changes -->
          <tr v-for="entry in entries" :key="entry.id">
            <td>{{ entry.description }}</td>
            <!-- formatCurrency from utils/format.js — uses Intl.NumberFormat -->
            <td class="mono">
              {{ formatCurrency(entry.amount, entry.currency) }}
            </td>
            <!-- badge styled pill for currency code -->
            <td>
              <span class="badge">{{ entry.currency }}</span>
            </td>
            <!-- formatDate from utils/format.js — converts to '10 Mar 2026' -->
            <td>{{ formatDate(entry.transaction_date) }}</td>
            <td class="text-muted">{{ formatDate(entry.created_at) }}</td>
            <td>
              <div class="actions">
                <!-- opens EditModal with spread copy of entry to avoid mutating original -->
                <button
                  class="btn-icon btn-icon--edit"
                  @click="openEdit(entry)"
                >
                  ✏️
                </button>
                <!-- sets deleteEntry — triggers confirmation dialog -->
                <button
                  class="btn-icon btn-icon--delete"
                  @click="confirmDelete(entry)"
                >
                  🗑️
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ── Pagination ── -->
    <div class="pagination" v-if="meta.last_page > 1 || entries.length">
      <div class="pagination-info">
        <!-- ?? 0 — nullish coalescing, shows 0 if value is null/undefined -->
        Showing {{ meta.from ?? 0 }}–{{ meta.to ?? 0 }} of
        {{ meta.total ?? 0 }} entries
      </div>
      <div class="pagination-controls">
        <!-- per_page selector — refetches on change -->
        <select
          v-model="perPage"
          class="filter-input per-page"
          @change="fetchEntries"
        >
          <option v-for="n in [10, 25, 50]" :key="n" :value="n">
            {{ n }} / page
          </option>
        </select>
        <button
          class="btn btn--secondary"
          :disabled="page === 1"
          @click="changePage(page - 1)"
        >
          Prev
        </button>
        <!-- dynamic page buttons — highlights active page with btn--primary -->
        <button
          v-for="p in meta.last_page"
          :key="p"
          class="btn"
          :class="p === page ? 'btn--primary' : 'btn--secondary'"
          @click="changePage(p)"
        >
          {{ p }}
        </button>
        <button
          class="btn btn--secondary"
          :disabled="page === meta.last_page"
          @click="changePage(page + 1)"
        >
          Next
        </button>
      </div>
    </div>

    <!-- ── Edit Modal — only renders when editEntry is not null ── -->
    <EditModal
      v-if="editEntry"
      :entry="editEntry"
      @close="editEntry = null"
      @updated="onUpdated"
    />

    <!-- ── Delete Confirmation Dialog ── -->
    <!-- @click.self — only closes when clicking the overlay, not the box -->
    <div v-if="deleteEntry" class="overlay" @click.self="deleteEntry = null">
      <div class="confirm-box">
        <h3 class="confirm-title">Delete Entry?</h3>
        <p class="confirm-msg">
          Are you sure you want to delete
          <strong>{{ deleteEntry.description }}</strong
          >? This cannot be undone.
        </p>
        <div class="confirm-actions">
          <button class="btn btn--secondary" @click="deleteEntry = null">
            Cancel
          </button>
          <button
            class="btn btn--danger"
            @click="doDelete"
            :disabled="deleting"
          >
            {{ deleting ? "Deleting..." : "Delete" }}
          </button>
        </div>
      </div>
    </div>

    <!-- Toast notification — auto dismisses after 3 seconds -->
    <ToastNotification
      v-if="toast.show"
      :message="toast.message"
      :type="toast.type"
      @close="toast.show = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import api from "../api/axios.js";
import { formatCurrency, formatDate } from "../utils/format.js";
import EditModal from "./EditModal.vue";
import ToastNotification from "./ToastNotification.vue";

// ref() for single values
const entries = ref([]);
const meta = ref({});
const loading = ref(false);
const page = ref(1);
const perPage = ref(10);
const search = ref("");
const dateFrom = ref("");
const dateTo = ref("");
const sortCol = ref("created_at"); // default sort column
const sortDir = ref("desc"); // default sort direction
const editEntry = ref(null); // null = modal closed, entry object = modal open
const deleteEntry = ref(null); // null = dialog closed, entry object = dialog open
const deleting = ref(false);

// reactive() for toast — object with multiple related properties
const toast = reactive({ show: false, message: "", type: "success" });

let debounceTimer = null;

// Debounce — waits 300ms after user stops typing before firing API call
// Prevents spamming the API on every keystroke
function debouncedFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    page.value = 1; // reset to page 1 on new search
    fetchEntries();
  }, 300);
}

async function fetchEntries() {
  loading.value = true;
  try {
    const { data } = await api.get("/entries", {
      params: {
        page: page.value,
        per_page: perPage.value,
        sort: sortCol.value,
        order: sortDir.value,
        // undefined params are excluded from the request automatically
        search: search.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
      },
    });
    entries.value = data.data; // paginated entries array
    meta.value = data; // pagination metadata (total, last_page, etc)
  } catch {
    showToast("Failed to load entries.", "error");
  } finally {
    loading.value = false;
  }
}

// Toggle sort direction if same column, reset to asc if new column
function sortBy(col) {
  if (sortCol.value === col) {
    sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  } else {
    sortCol.value = col;
    sortDir.value = "asc";
  }
  fetchEntries();
}

// Returns sort indicator icon for column headers
function sortIcon(col) {
  if (sortCol.value !== col) return "↕"; // unsorted
  return sortDir.value === "asc" ? "▲" : "▼";
}

function changePage(p) {
  page.value = p;
  fetchEntries();
}

function clearFilters() {
  search.value = "";
  dateFrom.value = "";
  dateTo.value = "";
  page.value = 1;
  fetchEntries();
}

// Spread operator — creates a copy so EditModal doesn't mutate the original
function openEdit(entry) {
  editEntry.value = { ...entry };
}

function onUpdated() {
  editEntry.value = null;
  showToast("Entry updated successfully!");
  fetchEntries(); // refresh table after update
}

function confirmDelete(entry) {
  deleteEntry.value = entry;
}

async function doDelete() {
  deleting.value = true;
  try {
    await api.delete(`/entries/${deleteEntry.value.id}`);
    deleteEntry.value = null;
    showToast("Entry deleted.");
    fetchEntries(); // refresh table after delete
  } catch {
    showToast("Failed to delete entry.", "error");
  } finally {
    deleting.value = false;
  }
}

function showToast(message, type = "success") {
  toast.message = message;
  toast.type = type;
  toast.show = true;
}

// CSV export — builds CSV string from current page entries
// Uses Blob API and URL.createObjectURL for browser download
function exportCsv() {
  const headers = [
    "ID",
    "Description",
    "Amount",
    "Currency",
    "Date",
    "Created",
  ];
  const rows = entries.value.map((e) => [
    e.id,
    `"${e.description}"`, // wrap in quotes — handles commas in description
    e.amount,
    e.currency,
    formatDate(e.transaction_date),
    formatDate(e.created_at),
  ]);
  const csv = [headers, ...rows].map((r) => r.join(",")).join("\n");
  const blob = new Blob([csv], { type: "text/csv" });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = "entries.csv";
  a.click();
}

// Fetch entries when component first mounts
onMounted(fetchEntries);
</script>
<style scoped>
.table-wrapper {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}
.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: var(--space-3);
}
.toolbar-filters {
  display: flex;
  gap: var(--space-2);
  flex-wrap: wrap;
}
.filter-input {
  padding: var(--space-2) var(--space-3);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
  background: var(--color-bg);
  color: var(--color-text);
  transition: var(--transition);
}
.filter-input:focus {
  outline: none;
  border-color: var(--color-border-focus);
}
.per-page {
  width: 110px;
}
.state-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-12);
  color: var(--color-text-muted);
  font-size: var(--text-sm);
}
.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
.table-scroll {
  overflow-x: auto;
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-border);
}
.table {
  width: 100%;
  border-collapse: collapse;
  font-size: var(--text-sm);
}
.table thead {
  background: var(--color-bg-subtle);
}
.table th {
  padding: var(--space-3) var(--space-4);
  text-align: left;
  font-weight: 600;
  color: var(--color-text-muted);
  border-bottom: 1px solid var(--color-border);
  white-space: nowrap;
}
.table td {
  padding: var(--space-3) var(--space-4);
  border-bottom: 1px solid var(--color-border);
  color: var(--color-text);
}
.table tbody tr:last-child td {
  border-bottom: none;
}
.table tbody tr:hover {
  background: var(--color-bg-subtle);
}
.sortable {
  cursor: pointer;
  user-select: none;
}
.sortable:hover {
  color: var(--color-primary);
}
.sort-icon {
  font-size: var(--text-xs);
  margin-left: var(--space-1);
}
.mono {
  font-family: var(--font-mono);
}
.text-muted {
  color: var(--color-text-muted);
}
.badge {
  display: inline-block;
  padding: 2px var(--space-2);
  background: var(--color-primary-light);
  color: var(--color-primary);
  border-radius: var(--radius-sm);
  font-size: var(--text-xs);
  font-weight: 600;
}
.actions {
  display: flex;
  gap: var(--space-2);
}
.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: var(--text-base);
  padding: var(--space-1);
  border-radius: var(--radius-sm);
  transition: var(--transition);
}
.btn-icon:hover {
  background: var(--color-bg-muted);
}
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: var(--space-3);
}
.pagination-info {
  font-size: var(--text-sm);
  color: var(--color-text-muted);
}
.pagination-controls {
  display: flex;
  gap: var(--space-2);
  align-items: center;
  flex-wrap: wrap;
}
.btn {
  padding: var(--space-2) var(--space-4);
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
.btn--secondary {
  background: var(--color-bg-muted);
  color: var(--color-text-muted);
  border: 1px solid var(--color-border);
}
.btn--secondary:hover:not(:disabled) {
  background: var(--color-border);
}
.btn--secondary:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.btn--danger {
  background: var(--color-error);
  color: #fff;
}
.btn--danger:hover:not(:disabled) {
  opacity: 0.85;
}
.btn--danger:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
}
.confirm-box {
  background: var(--color-bg);
  border-radius: var(--radius-lg);
  padding: var(--space-8);
  max-width: 400px;
  width: 90%;
  box-shadow: var(--shadow-lg);
}
.confirm-title {
  font-size: var(--text-lg);
  font-weight: 700;
  margin-bottom: var(--space-3);
}
.confirm-msg {
  font-size: var(--text-sm);
  color: var(--color-text-muted);
  margin-bottom: var(--space-6);
}
.confirm-actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-3);
}
</style>
