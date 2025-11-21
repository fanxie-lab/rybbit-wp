<template>
  <div class="dashboard">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Connection Status -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            Connection Status
            <HelpTooltip text="Shows whether your site is successfully connected to Rybbit Analytics" />
          </h3>
        </div>

        <div class="card-body">
          <StatusIndicator
            :status="connectionStatus"
            :message="statusMessage"
          />

          <p v-if="settings.site_id" class="mt-4 text-sm text-gray-600">
            <strong>Site ID:</strong> <code class="px-2 py-1 bg-gray-100 rounded font-mono">{{ settings.site_id }}</code>
          </p>

          <div class="mt-4 space-y-2">
            <button
              @click="testInstallation"
              :disabled="testingInstallation || !settings.site_id"
              class="btn btn-outline w-full"
            >
              <svg v-if="!testingInstallation" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <LoadingSpinner v-else size="small" class="mr-2" />
              {{ testingInstallation ? 'Testing...' : 'Test Installation' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Quick Actions</h3>
        </div>
        <div class="card-body space-y-3">
          <router-link
            v-if="!settings.connected"
            to="/setup"
            class="btn btn-primary block text-center"
          >
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Run Setup Wizard
          </router-link>

          <router-link to="/settings" class="btn btn-secondary block text-center">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Configure Settings
          </router-link>

          <a
            :href="rybbitDashboardUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="btn btn-outline block text-center"
          >
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
            Open Rybbit Dashboard
          </a>
        </div>
      </div>

      <!-- Quick Stats Placeholder - Hidden until V2
      <div class="card md:col-span-2">
        <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center gap-2">
          Quick Stats
          <span class="text-xs font-normal text-gray-500 bg-blue-100 px-2 py-0.5 rounded">Coming in V2</span>
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="stat-card">
            <div class="stat-icon bg-blue-100 text-blue-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </div>
            <div class="stat-label">Page Views</div>
            <div class="stat-value">- -</div>
          </div>

          <div class="stat-card">
            <div class="stat-icon bg-green-100 text-green-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div class="stat-label">Visitors</div>
            <div class="stat-value">- -</div>
          </div>

          <div class="stat-card">
            <div class="stat-icon bg-purple-100 text-purple-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <div class="stat-label">Events</div>
            <div class="stat-value">- -</div>
          </div>

          <div class="stat-card">
            <div class="stat-icon bg-orange-100 text-orange-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="stat-label">Avg. Duration</div>
            <div class="stat-value">- -</div>
          </div>
        </div>
        <p class="mt-4 text-sm text-gray-500 text-center">
          In-dashboard analytics will be available in version 2.0
        </p>
      </div>
      -->
    </div>

    <!-- Setup Reminder -->
    <div v-if="!settings.connected && settings.site_id" class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800">Connection Not Verified</h3>
          <div class="mt-2 text-sm text-yellow-700">
            <p>Your Site ID is configured but the connection hasn't been tested.
              <button @click="testInstallation" class="font-medium underline">Test now</button> or
              <router-link to="/setup" class="font-medium underline">run the setup wizard</router-link>
            </p>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="!settings.site_id" class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-blue-800">Setup Required</h3>
          <div class="mt-2 text-sm text-blue-700">
            <p>You need to configure your Site ID to start tracking.
              <router-link to="/setup" class="font-medium underline">Run setup wizard</router-link> or
              <router-link to="/settings" class="font-medium underline">configure manually</router-link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useSettingsStore } from '../stores/settings'
import { useNotificationsStore } from '../stores/notifications'
import StatusIndicator from '../components/StatusIndicator.vue'
import HelpTooltip from '../components/HelpTooltip.vue'
import LoadingSpinner from '../components/LoadingSpinner.vue'

const settingsStore = useSettingsStore()
const notificationsStore = useNotificationsStore()
const { settings } = storeToRefs(settingsStore)

const testingInstallation = ref(false)

const connectionStatus = computed(() => {
  if (testingInstallation.value) return 'checking'
  if (settings.value.connected) return 'connected'
  return 'disconnected'
})

const statusMessage = computed(() => {
  if (settings.value.connected) {
    return 'Your site is successfully connected to Rybbit Analytics'
  }
  if (settings.value.site_id) {
    return 'Site ID configured but connection not verified'
  }
  return 'No Site ID configured'
})

const rybbitDashboardUrl = computed(() => {
  const baseUrl = 'https://app.rybbit.io'
  return settings.value.site_id
    ? `${baseUrl}/site/${settings.value.site_id}`
    : baseUrl
})

async function testInstallation() {
  if (!settings.value.site_id) {
    notificationsStore.warning('Please configure a Site ID first')
    return
  }

  testingInstallation.value = true

  try {
    const result = await settingsStore.testConnection(settings.value.site_id)

    if (result.success) {
      // Update connected status
      await settingsStore.updateSettings({
        ...settings.value,
        connected: true
      })
      notificationsStore.success('Installation test successful! Your site is connected.')
    } else {
      notificationsStore.error(result.message || 'Installation test failed')
    }
  } catch (error) {
    notificationsStore.error(`Test failed: ${error.message}`)
  } finally {
    testingInstallation.value = false
  }
}

onMounted(() => {
  settingsStore.fetchSettings()
})
</script>

<style scoped>
.card {
  @apply bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden;
}

.card-header {
  @apply px-6 py-4 border-b border-gray-200 bg-gray-50;
}

.card-title {
  @apply text-lg font-medium text-gray-800 flex items-center gap-2;
}

.card-body {
  @apply p-6;
}

.btn {
  @apply px-4 py-2.5 rounded-md font-medium transition-colors inline-flex items-center justify-center;
}

.btn-primary {
  @apply bg-rybbit-primary text-white hover:bg-rybbit-secondary;
}

.btn-secondary {
  @apply bg-gray-100 text-gray-700 hover:bg-gray-200;
}

.btn-outline {
  @apply border-2 border-gray-300 text-gray-700 hover:border-rybbit-primary hover:text-rybbit-primary;
}

.btn:disabled {
  @apply opacity-50 cursor-not-allowed;
}

.stat-card {
  @apply flex flex-col items-center p-4 bg-gray-50 rounded-lg;
}

.stat-icon {
  @apply w-12 h-12 rounded-full flex items-center justify-center mb-3;
}

.stat-label {
  @apply text-sm text-gray-600 mb-1;
}

.stat-value {
  @apply text-2xl font-bold text-gray-900;
}
</style>
