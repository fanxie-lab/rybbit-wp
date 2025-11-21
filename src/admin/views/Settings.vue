<template>
  <div class="settings">
    <div class="mb-6">
      <h2 class="text-2xl font-semibold text-gray-800">Settings</h2>
      <p class="text-gray-600 mt-1">Configure your Rybbit Analytics integration</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Site Configuration -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            Site Configuration
            <HelpTooltip text="Basic settings to connect your WordPress site to Rybbit Analytics" />
          </h3>
        </div>

        <div class="card-body space-y-4">
          <!-- Site ID -->
          <div>
            <label for="site-id" class="input-label">
              Site ID <span class="text-red-500">*</span>
            </label>
            <input
              id="site-id"
              v-model="localSettings.site_id"
              type="text"
              class="input"
              :class="{ 'input-error': errors.site_id }"
              placeholder="Enter your Rybbit Site ID"
              @blur="validateField('site_id')"
              @input="clearError('site_id')"
            />
            <p v-if="errors.site_id" class="input-error-text">
              {{ errors.site_id }}
            </p>
            <p v-else class="input-help">
              Find your Site ID in the Rybbit dashboard under Site Settings at
              <a href="https://app.rybbit.io" target="_blank" rel="noopener noreferrer" class="link">
                app.rybbit.io
              </a>
            </p>
          </div>

          <!-- Script URL -->
          <div>
            <label for="script-url" class="input-label">
              Script URL
              <HelpTooltip text="URL to load the Rybbit tracking script. Use the default for cloud-hosted or provide your own for self-hosted instances" />
            </label>
            <input
              id="script-url"
              v-model="localSettings.script_url"
              type="url"
              class="input"
              :class="{ 'input-error': errors.script_url }"
              placeholder="https://app.rybbit.io/api/script.js"
              @blur="validateField('script_url')"
              @input="clearError('script_url')"
            />
            <p v-if="errors.script_url" class="input-error-text">
              {{ errors.script_url }}
            </p>
            <p v-else class="input-help">
              Use the default URL or provide a custom URL for self-hosted instances
            </p>
          </div>
        </div>
      </div>

      <!-- Tracking Exclusions -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            Tracking Exclusions
            <HelpTooltip text="Control which users and content should be excluded from analytics tracking" />
          </h3>
        </div>

        <div class="card-body space-y-4">
          <div>
            <label class="input-label mb-3">Exclude User Roles</label>
            <p class="text-sm text-gray-500 mb-3">
              Users with these roles will not be tracked when logged in. Recommended: exclude administrators and editors to avoid skewing your analytics.
            </p>
            <div class="space-y-2">
              <label class="flex items-center gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  v-model="localSettings.exclude_roles"
                  value="administrator"
                  class="checkbox"
                />
                <span class="text-sm text-gray-700">Administrator</span>
                <span class="text-xs text-gray-400">— Full site access</span>
              </label>
              <label class="flex items-center gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  v-model="localSettings.exclude_roles"
                  value="editor"
                  class="checkbox"
                />
                <span class="text-sm text-gray-700">Editor</span>
                <span class="text-xs text-gray-400">— Can manage content</span>
              </label>
              <label class="flex items-center gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  v-model="localSettings.exclude_roles"
                  value="author"
                  class="checkbox"
                />
                <span class="text-sm text-gray-700">Author</span>
                <span class="text-xs text-gray-400">— Can publish posts</span>
              </label>
              <label class="flex items-center gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  v-model="localSettings.exclude_roles"
                  value="contributor"
                  class="checkbox"
                />
                <span class="text-sm text-gray-700">Contributor</span>
                <span class="text-xs text-gray-400">— Can write drafts</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Remote Configuration (Info Only) -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            Remote Configuration
            <HelpTooltip text="These settings are managed through your Rybbit dashboard and apply automatically" />
          </h3>
        </div>

        <div class="card-body">
          <p class="text-sm text-gray-600 mb-4">
            The following features are controlled through your
            <a :href="rybbitDashboardUrl" target="_blank" rel="noopener noreferrer" class="link">Rybbit dashboard</a>
            and cannot be configured here:
          </p>

          <ul class="space-y-2 text-sm text-gray-600">
            <li class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              Track Initial Pageview
            </li>
            <li class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              Track SPA Navigation
            </li>
            <li class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              Track URL Parameters
            </li>
            <li class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              Track Outbound Links
            </li>
            <li class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              Track Web Vitals
            </li>
            <li class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              Capture Errors
            </li>
            <li class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              Session Replay
            </li>
          </ul>

          <div class="mt-4 p-3 bg-blue-50 rounded-md">
            <p class="text-sm text-blue-700">
              <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              To change these settings, visit your site's configuration in the Rybbit dashboard.
            </p>
          </div>
        </div>
      </div>

      <!-- Advanced Settings -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            Advanced Settings
            <HelpTooltip text="Advanced configuration options for fine-tuning tracking behavior" />
          </h3>
        </div>

        <div class="card-body space-y-4">
          <!-- Debounce Delay -->
          <div>
            <label for="debounce-delay" class="input-label">
              SPA Debounce Delay (ms)
              <HelpTooltip text="Time to wait after navigation before tracking a pageview in SPAs. Higher values reduce duplicate events but may miss quick navigations" />
            </label>
            <div class="flex items-center gap-4">
              <input
                id="debounce-delay"
                v-model.number="localSettings.debounce_delay"
                type="range"
                min="100"
                max="2000"
                step="100"
                class="flex-1"
              />
              <span class="text-sm font-medium text-gray-700 w-16 text-right">
                {{ localSettings.debounce_delay }}ms
              </span>
            </div>
            <p class="input-help">
              Recommended: 500ms for most sites
            </p>
          </div>
        </div>
      </div>

      <!-- Save Actions -->
      <div class="md:col-span-2 sticky bottom-0 bg-white border-t border-gray-200 p-4 mt-2 rounded-lg shadow-sm flex items-center justify-between gap-4">
        <div class="flex-1">
          <p v-if="hasUnsavedChanges" class="text-sm text-gray-600">
            You have unsaved changes
          </p>
        </div>

        <div class="flex items-center gap-3">
          <button
            v-if="hasUnsavedChanges"
            @click="resetChanges"
            class="btn btn-text"
            :disabled="loading"
          >
            Reset
          </button>

          <button
            @click="testConnection"
            :disabled="loading || !localSettings.site_id || hasUnsavedChanges"
            class="btn btn-outline"
          >
            <svg v-if="!testing" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <LoadingSpinner v-else size="small" class="mr-2" />
            {{ testing ? 'Testing...' : 'Test Connection' }}
          </button>

          <button
            @click="saveSettings"
            :disabled="loading || !hasUnsavedChanges"
            class="btn btn-primary"
          >
            <svg v-if="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <LoadingSpinner v-else size="small" class="mr-2" />
            {{ loading ? 'Saving...' : 'Save Settings' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useSettingsStore } from '../stores/settings'
import { useNotificationsStore } from '../stores/notifications'
import ToggleSwitch from '../components/ToggleSwitch.vue'
import HelpTooltip from '../components/HelpTooltip.vue'
import LoadingSpinner from '../components/LoadingSpinner.vue'

const settingsStore = useSettingsStore()
const notificationsStore = useNotificationsStore()
const { settings, loading } = storeToRefs(settingsStore)

const localSettings = ref({})
const errors = ref({})
const testing = ref(false)

const rybbitDashboardUrl = computed(() => {
  const baseUrl = 'https://app.rybbit.io'
  return localSettings.value.site_id
    ? `${baseUrl}/site/${localSettings.value.site_id}`
    : baseUrl
})

const hasUnsavedChanges = computed(() => {
  return JSON.stringify(localSettings.value) !== JSON.stringify(settings.value)
})

function clearError(field) {
  delete errors.value[field]
}

function validateField(field) {
  switch (field) {
    case 'site_id':
      if (!localSettings.value.site_id || localSettings.value.site_id.trim() === '') {
        errors.value.site_id = 'Site ID is required'
      } else if (!/^[a-zA-Z0-9-_]+$/.test(localSettings.value.site_id)) {
        errors.value.site_id = 'Site ID can only contain letters, numbers, hyphens, and underscores'
      } else {
        delete errors.value.site_id
      }
      break

    case 'script_url':
      if (!localSettings.value.script_url) {
        errors.value.script_url = 'Script URL is required'
      } else {
        try {
          new URL(localSettings.value.script_url)
          delete errors.value.script_url
        } catch {
          errors.value.script_url = 'Please enter a valid URL'
        }
      }
      break
  }
}

function validateAll() {
  validateField('site_id')
  validateField('script_url')
  return Object.keys(errors.value).length === 0
}

async function testConnection() {
  if (!localSettings.value.site_id) {
    notificationsStore.warning('Please enter a Site ID first')
    return
  }

  testing.value = true

  try {
    const result = await settingsStore.testConnection(localSettings.value.site_id)

    if (result.success) {
      notificationsStore.success('Connection test successful! Your site is properly configured.')
    } else {
      notificationsStore.error(result.message || 'Connection test failed')
    }
  } catch (error) {
    notificationsStore.error(`Test failed: ${error.message}`)
  } finally {
    testing.value = false
  }
}

async function saveSettings() {
  if (!validateAll()) {
    notificationsStore.error('Please fix validation errors before saving')
    return
  }

  try {
    await settingsStore.updateSettings(localSettings.value)
    notificationsStore.success('Settings saved successfully!')
  } catch (error) {
    notificationsStore.error(`Error saving settings: ${error.message}`)
  }
}

function resetChanges() {
  localSettings.value = JSON.parse(JSON.stringify(settings.value))
  errors.value = {}
  notificationsStore.info('Changes reset to last saved values')
}

onMounted(async () => {
  await settingsStore.fetchSettings()
  localSettings.value = JSON.parse(JSON.stringify(settings.value))
})

// Watch for external settings changes (e.g., from other components)
watch(settings, (newSettings) => {
  if (!hasUnsavedChanges.value) {
    localSettings.value = JSON.parse(JSON.stringify(newSettings))
  }
}, { deep: true })
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

.input-label {
  @apply block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1;
}

.input {
  @apply w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rybbit-primary focus:border-transparent transition-colors;
}

.input-error {
  @apply border-red-500 focus:ring-red-500;
}

.input-error-text {
  @apply mt-1.5 text-sm text-red-600;
}

.input-help {
  @apply mt-1.5 text-sm text-gray-500;
}

.link {
  @apply text-rybbit-primary hover:underline;
}

.btn {
  @apply px-4 py-2 rounded-md font-medium transition-colors inline-flex items-center justify-center;
}

.btn-primary {
  @apply bg-rybbit-primary text-white hover:bg-rybbit-secondary;
}

.btn-outline {
  @apply border-2 border-gray-300 text-gray-700 hover:border-rybbit-primary hover:text-rybbit-primary;
}

.btn-text {
  @apply text-gray-600 hover:text-gray-900;
}

.btn:disabled {
  @apply opacity-50 cursor-not-allowed;
}

.checkbox {
  @apply w-4 h-4 rounded border-gray-300 text-rybbit-primary focus:ring-rybbit-primary focus:ring-offset-0;
}
</style>
