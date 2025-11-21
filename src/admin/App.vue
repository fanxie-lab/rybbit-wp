<template>
  <div class="rybbit-admin-container max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <header class="mb-6 pb-6 border-b">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="text-primary">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="16" cy="16" r="14" stroke="currentColor" stroke-width="3"/>
              <path d="M16 8V16L20 20" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            </svg>
          </div>
          <h1 class="text-2xl font-semibold">Rybbit Analytics</h1>
        </div>
        <div class="text-sm text-muted-foreground">
          v{{ version }}
        </div>
      </div>
    </header>

    <!-- Navigation -->
    <nav class="flex space-x-1 mb-8 border-b">
      <router-link
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        class="nav-link"
        :class="{ 'nav-link-active': isActiveRoute(item.to) }"
      >
        {{ item.label }}
      </router-link>
    </nav>

    <!-- Main content area -->
    <main class="min-h-[500px]">
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
import { useRoute } from 'vue-router'
import NotificationBar from './components/NotificationBar.vue'

// Get data from WordPress
const wpData = window.rybbitAdmin || {}

const route = useRoute()
const version = computed(() => wpData.version || '1.0.0')

const navItems = [
  { to: '/', label: 'Dashboard' },
  { to: '/settings', label: 'Settings' },
  { to: '/exclusions', label: 'Exclusions' },
  { to: '/woocommerce', label: 'WooCommerce' },
]

function isActiveRoute(to) {
  if (to === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(to)
}
</script>

<style scoped>
.nav-link {
  @apply px-4 py-3 text-sm font-medium text-muted-foreground hover:text-foreground border-b-2 border-transparent transition-colors;
}

.nav-link-active {
  @apply text-primary border-primary;
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
