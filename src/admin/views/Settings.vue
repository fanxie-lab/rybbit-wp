<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-semibold tracking-tight">Settings</h2>
      <p class="text-muted-foreground">Configure your Rybbit Analytics integration</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Site Configuration Card -->
      <Card>
        <CardHeader>
          <CardTitle>
            Site Configuration
            <Tooltip content="Basic settings to connect your WordPress site to Rybbit Analytics">
              <button type="button" class="inline-flex items-center justify-center text-muted-foreground hover:text-foreground">
                <CircleHelp class="h-4 w-4" />
              </button>
            </Tooltip>
          </CardTitle>
        </CardHeader>
        <CardContent class="pt-6 space-y-6">
          <!-- Site ID -->
          <div class="space-y-2">
            <Label for="site-id" class="flex items-center gap-1">
              Site ID <span class="text-destructive">*</span>
            </Label>
            <Input
              id="site-id"
              v-model="localSettings.site_id"
              placeholder="Enter your Rybbit Site ID"
              :class="{ 'border-destructive focus-visible:ring-destructive': errors.site_id }"
              @blur="validateField('site_id')"
              @input="clearError('site_id')"
            />
            <p v-if="errors.site_id" class="text-sm text-destructive">
              {{ errors.site_id }}
            </p>
            <p v-else class="text-sm text-muted-foreground">
              Find your Site ID in the Rybbit dashboard under Site Settings at
              <a href="https://app.rybbit.io" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">
                app.rybbit.io
              </a>
            </p>
          </div>

          <!-- Script URL -->
          <div class="space-y-2">
            <Label for="script-url" class="flex items-center gap-1">
              Script URL
              <Tooltip content="URL to load the Rybbit tracking script. Use the default for cloud-hosted or provide your own for self-hosted instances">
                <button type="button" class="inline-flex items-center justify-center text-muted-foreground hover:text-foreground">
                  <CircleHelp class="h-4 w-4" />
                </button>
              </Tooltip>
            </Label>
            <Input
              id="script-url"
              v-model="localSettings.script_url"
              type="url"
              placeholder="https://app.rybbit.io/api/script.js"
              :class="{ 'border-destructive focus-visible:ring-destructive': errors.script_url }"
              @blur="validateField('script_url')"
              @input="clearError('script_url')"
            />
            <p v-if="errors.script_url" class="text-sm text-destructive">
              {{ errors.script_url }}
            </p>
            <p v-else class="text-sm text-muted-foreground">
              Use the default URL or provide a custom URL for self-hosted instances
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Tracking Exclusions Card -->
      <Card>
        <CardHeader>
          <CardTitle>
            Tracking Exclusions
            <Tooltip content="Control which users and content should be excluded from analytics tracking">
              <button type="button" class="inline-flex items-center justify-center text-muted-foreground hover:text-foreground">
                <CircleHelp class="h-4 w-4" />
              </button>
            </Tooltip>
          </CardTitle>
        </CardHeader>
        <CardContent class="pt-6 space-y-4">
          <div>
            <Label class="mb-3 block">Exclude User Roles</Label>
            <p class="text-sm text-muted-foreground mb-4">
              Users with these roles will not be tracked when logged in. Recommended: exclude administrators and editors.
            </p>
            <div class="space-y-3">
              <div v-for="role in userRoles" :key="role.value" class="flex items-center gap-3">
                <Checkbox
                  :id="`role-${role.value}`"
                  :model-value="localSettings.exclude_roles?.includes(role.value)"
                  @update:model-value="toggleRole(role.value, $event)"
                />
                <div class="flex items-center gap-2">
                  <Label :for="`role-${role.value}`" class="font-normal cursor-pointer">
                    {{ role.label }}
                  </Label>
                  <span class="text-xs text-muted-foreground">{{ role.description }}</span>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Remote Configuration Card -->
      <Card>
        <CardHeader>
          <CardTitle>
            Remote Configuration
            <Tooltip content="These settings are managed through your Rybbit dashboard and apply automatically">
              <button type="button" class="inline-flex items-center justify-center text-muted-foreground hover:text-foreground">
                <CircleHelp class="h-4 w-4" />
              </button>
            </Tooltip>
          </CardTitle>
        </CardHeader>
        <CardContent class="pt-6">
          <p class="text-sm text-muted-foreground mb-4">
            The following features are controlled through your
            <a :href="rybbitDashboardUrl" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">Rybbit dashboard</a>
            and cannot be configured here:
          </p>

          <ul class="space-y-2 text-sm text-muted-foreground">
            <li v-for="feature in remoteFeatures" :key="feature" class="flex items-center gap-2">
              <ChevronRight class="h-4 w-4 text-muted-foreground/60" />
              {{ feature }}
            </li>
          </ul>

          <Alert variant="info" class="mt-4">
            <Info class="h-4 w-4" />
            <AlertDescription>
              To change these settings, visit your site's configuration in the Rybbit dashboard.
            </AlertDescription>
          </Alert>
        </CardContent>
      </Card>

      <!-- Advanced Settings Card -->
      <Card>
        <CardHeader>
          <CardTitle>
            Advanced Settings
            <Tooltip content="Advanced configuration options for fine-tuning tracking behavior">
              <button type="button" class="inline-flex items-center justify-center text-muted-foreground hover:text-foreground">
                <CircleHelp class="h-4 w-4" />
              </button>
            </Tooltip>
          </CardTitle>
        </CardHeader>
        <CardContent class="pt-6 space-y-4">
          <!-- Debounce Delay -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <Label class="flex items-center gap-1">
                SPA Debounce Delay
                <Tooltip content="Time to wait after navigation before tracking a pageview in SPAs. Higher values reduce duplicate events but may miss quick navigations">
                  <button type="button" class="inline-flex items-center justify-center text-muted-foreground hover:text-foreground">
                    <CircleHelp class="h-4 w-4" />
                  </button>
                </Tooltip>
              </Label>
              <span class="text-sm font-medium tabular-nums">
                {{ localSettings.debounce_delay }}ms
              </span>
            </div>
            <Slider
              :model-value="[localSettings.debounce_delay]"
              :min="100"
              :max="2000"
              :step="100"
              @update:model-value="localSettings.debounce_delay = $event[0]"
            />
            <p class="text-sm text-muted-foreground">
              Recommended: 500ms for most sites
            </p>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Save Actions Bar -->
    <div class="sticky bottom-0 bg-background border rounded-lg shadow-sm p-4 flex items-center justify-between gap-4">
      <p v-if="hasUnsavedChanges" class="text-sm text-muted-foreground">
        You have unsaved changes
      </p>
      <div v-else />

      <div class="flex items-center gap-3">
        <Button
          v-if="hasUnsavedChanges"
          variant="ghost"
          :disabled="loading"
          @click="resetChanges"
        >
          Reset
        </Button>

        <Button
          variant="outline"
          :disabled="loading || !localSettings.site_id || hasUnsavedChanges"
          @click="testConnection"
        >
          <Loader2 v-if="testing" class="mr-2 h-4 w-4 animate-spin" />
          <CheckCircle2 v-else class="mr-2 h-4 w-4" />
          {{ testing ? 'Testing...' : 'Test Connection' }}
        </Button>

        <Button
          :disabled="loading || !hasUnsavedChanges"
          @click="saveSettings"
        >
          <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
          <Check v-else class="mr-2 h-4 w-4" />
          {{ loading ? 'Saving...' : 'Save Settings' }}
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useSettingsStore } from '../stores/settings'
import { useNotificationsStore } from '../stores/notifications'
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  Button,
  Input,
  Label,
  Checkbox,
  Slider,
  Alert,
  AlertDescription,
  Tooltip
} from '@components/ui'
import {
  CircleHelp,
  Loader2,
  CheckCircle2,
  Check,
  ChevronRight,
  Info
} from 'lucide-vue-next'

const settingsStore = useSettingsStore()
const notificationsStore = useNotificationsStore()
const { settings, loading } = storeToRefs(settingsStore)

const localSettings = ref({})
const errors = ref({})
const testing = ref(false)

const userRoles = [
  { value: 'administrator', label: 'Administrator', description: '- Full site access' },
  { value: 'editor', label: 'Editor', description: '- Can manage content' },
  { value: 'author', label: 'Author', description: '- Can publish posts' },
  { value: 'contributor', label: 'Contributor', description: '- Can write drafts' },
]

const remoteFeatures = [
  'Track Initial Pageview',
  'Track SPA Navigation',
  'Track URL Parameters',
  'Track Outbound Links',
  'Track Web Vitals',
  'Capture Errors',
  'Session Replay',
]

const rybbitDashboardUrl = computed(() => {
  const baseUrl = 'https://app.rybbit.io'
  return localSettings.value.site_id
    ? `${baseUrl}/site/${localSettings.value.site_id}`
    : baseUrl
})

const hasUnsavedChanges = computed(() => {
  return JSON.stringify(localSettings.value) !== JSON.stringify(settings.value)
})

function toggleRole(role, checked) {
  if (!localSettings.value.exclude_roles) {
    localSettings.value.exclude_roles = []
  }
  if (checked) {
    if (!localSettings.value.exclude_roles.includes(role)) {
      localSettings.value.exclude_roles.push(role)
    }
  } else {
    localSettings.value.exclude_roles = localSettings.value.exclude_roles.filter(r => r !== role)
  }
}

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

watch(settings, (newSettings) => {
  if (!hasUnsavedChanges.value) {
    localSettings.value = JSON.parse(JSON.stringify(newSettings))
  }
}, { deep: true })
</script>
