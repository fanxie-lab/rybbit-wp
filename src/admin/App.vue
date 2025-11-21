<template>
  <div class="rybbit-admin-container">
    <!-- Header -->
    <header class="rybbit-header">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="rybbit-logo">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="16" cy="16" r="14" stroke="currentColor" stroke-width="3" class="text-rybbit-primary"/>
              <path d="M16 8V16L20 20" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="text-rybbit-primary"/>
            </svg>
          </div>
          <h1 class="text-2xl font-semibold text-gray-800">Rybbit Analytics</h1>
        </div>
        <div class="text-sm text-gray-500">
          v{{ version }}
        </div>
      </div>
    </header>

    <!-- Navigation -->
    <nav class="rybbit-nav">
      <router-link to="/" class="nav-link" active-class="active">
        Dashboard
      </router-link>
      <router-link to="/settings" class="nav-link" active-class="active">
        Settings
      </router-link>
      <router-link to="/exclusions" class="nav-link" active-class="active">
        Exclusions
      </router-link>
      <router-link to="/woocommerce" class="nav-link" active-class="active">
        WooCommerce
      </router-link>
    </nav>

    <!-- Main content area -->
    <main class="rybbit-main">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <!-- Notifications -->
    <NotificationBar />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import NotificationBar from './components/NotificationBar.vue'

// Get data from WordPress
const wpData = window.rybbitAdmin || {}

const version = computed(() => wpData.version || '1.0.0')
const isWooCommerce = computed(() => wpData.isWooCommerce || false)
</script>

<style scoped>
.rybbit-admin-container {
  @apply max-w-7xl mx-auto px-4 py-6;
}

.rybbit-header {
  @apply mb-6 pb-6 border-b border-gray-200;
}

.rybbit-nav {
  @apply flex space-x-1 mb-8 border-b border-gray-200;
}

.nav-link {
  @apply px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:border-b-2 hover:border-rybbit-primary transition-colors;
}

.nav-link.active {
  @apply text-rybbit-primary border-b-2 border-rybbit-primary;
}

.rybbit-main {
  @apply min-h-[500px];
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
