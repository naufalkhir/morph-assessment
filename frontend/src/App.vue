<template>
  <div id="app">
    <!-- Sticky navigation bar — stays at top when scrolling -->
    <nav class="nav">
      <div class="nav-inner">
        <!-- App brand/logo -->
        <span class="nav-brand">💰 Morph Entries</span>
        <div class="nav-links">
          <!-- RouterLink — Vue Router's equivalent of <a> tag -->
          <!-- Automatically gets 'router-link-active' class when route matches -->
          <RouterLink to="/" class="nav-link">New Entry</RouterLink>
          <RouterLink to="/list" class="nav-link">All Entries</RouterLink>

          <!-- Dark mode toggle — shows sun if dark, moon if light -->
          <button class="theme-toggle" @click="toggleTheme">
            {{ isDark ? "☀️" : "🌙" }}
          </button>
        </div>
      </div>
    </nav>

    <!-- RouterView — renders the current route's component here -->
    <!-- Either HomeView or ListView depending on the URL -->
    <main class="main">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

// Track dark mode state — ref() because it's a single boolean value
const isDark = ref(false);

// onMounted — runs after component is rendered in the DOM
// Check localStorage for saved preference, or use system preference
onMounted(() => {
  const saved = localStorage.getItem("theme");
  if (
    saved === "dark" ||
    (!saved && window.matchMedia("(prefers-color-scheme: dark)").matches)
  ) {
    isDark.value = true;
    // Set data-theme="dark" on <html> — triggers CSS custom property overrides
    document.documentElement.setAttribute("data-theme", "dark");
  }
});

function toggleTheme() {
  // Toggle the boolean
  isDark.value = !isDark.value;

  // Apply or remove dark theme on <html> element
  document.documentElement.setAttribute(
    "data-theme",
    isDark.value ? "dark" : "",
  );

  // Persist preference so it survives page refresh
  localStorage.setItem("theme", isDark.value ? "dark" : "light");
}
</script>
<style scoped>
.nav {
  background: var(--color-bg);
  border-bottom: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
  position: sticky;
  top: 0;
  z-index: 100;
}
.nav-inner {
  max-width: 1100px;
  margin: 0 auto;
  padding: var(--space-4) var(--space-6);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.nav-brand {
  font-weight: 700;
  font-size: var(--text-lg);
  color: var(--color-primary);
}
.nav-links {
  display: flex;
  align-items: center;
  gap: var(--space-4);
}
.nav-link {
  text-decoration: none;
  color: var(--color-text-muted);
  font-size: var(--text-sm);
  font-weight: 500;
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-md);
  transition: var(--transition);
}
.nav-link:hover,
.nav-link.router-link-active {
  color: var(--color-primary);
  background: var(--color-primary-light);
}
.theme-toggle {
  background: none;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: var(--space-2) var(--space-3);
  cursor: pointer;
  font-size: var(--text-sm);
  transition: var(--transition);
}
.theme-toggle:hover {
  background: var(--color-bg-subtle);
}
.main {
  max-width: 1100px;
  margin: 0 auto;
  padding: var(--space-8) var(--space-6);
}
</style>
