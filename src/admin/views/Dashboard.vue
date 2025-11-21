<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-semibold tracking-tight">Dashboard</h2>
      <p class="text-muted-foreground">Monitor your Rybbit Analytics connection</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Connection Status Card -->
      <Card>
        <CardHeader>
          <CardTitle>
            Connection Status
            <Tooltip content="Shows whether your site is successfully connected to Rybbit Analytics">
              <button type="button" class="inline-flex items-center justify-center text-muted-foreground hover:text-foreground">
                <CircleHelp class="h-4 w-4" />
              </button>
            </Tooltip>
          </CardTitle>
        </CardHeader>
        <CardContent class="pt-6">
          <div class="space-y-4">
            <!-- Status Indicator -->
            <div class="flex items-center gap-3">
              <div
                :class="[
                  'h-3 w-3 rounded-full',
                  connectionStatus === 'connected' ? 'bg-green-500' :
                  connectionStatus === 'checking' ? 'bg-yellow-500 animate-pulse' : 'bg-red-500'
                ]"
              />
              <span class="text-sm font-medium">
                {{ statusMessage }}
              </span>
            </div>

            <!-- Site ID Display -->
            <div v-if="settings.site_id" class="flex items-center gap-2 text-sm text-muted-foreground">
              <span class="font-medium">Site ID:</span>
              <code class="px-2 py-1 bg-muted rounded font-mono text-xs">{{ settings.site_id }}</code>
            </div>

            <!-- Test Button -->
            <Button
              variant="outline"
              class="w-full"
              :disabled="testingInstallation || !settings.site_id"
              @click="testInstallation"
            >
              <Loader2 v-if="testingInstallation" class="mr-2 h-4 w-4 animate-spin" />
              <CheckCircle2 v-else class="mr-2 h-4 w-4" />
              {{ testingInstallation ? 'Testing...' : 'Test Installation' }}
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Quick Actions Card -->
      <Card>
        <CardHeader>
          <CardTitle>Quick Actions</CardTitle>
        </CardHeader>
        <CardContent class="pt-6">
          <div class="flex flex-col gap-3">
            <Button
              v-if="!settings.connected"
              to="/setup"
              class="w-full justify-center"
            >
              <Zap class="mr-2 h-4 w-4" />
              Run Setup Wizard
            </Button>

            <Button
              variant="secondary"
              to="/settings"
              class="w-full justify-center"
            >
              <Settings class="mr-2 h-4 w-4" />
              Configure Settings
            </Button>

            <Button
              variant="outline"
              :href="rybbitDashboardUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="w-full justify-center"
            >
              <ExternalLink class="mr-2 h-4 w-4" />
              Open Rybbit Dashboard
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Setup Reminder Alerts -->
    <Alert v-if="!settings.connected && settings.site_id" variant="warning">
      <AlertTriangle class="h-4 w-4" />
      <AlertTitle>Connection Not Verified</AlertTitle>
      <AlertDescription>
        Your Site ID is configured but the connection hasn't been tested.
        <button @click="testInstallation" class="font-medium underline">Test now</button> or
        <router-link to="/setup" class="font-medium underline">run the setup wizard</router-link>
      </AlertDescription>
    </Alert>

    <Alert v-else-if="!settings.site_id" variant="info">
      <Info class="h-4 w-4" />
      <AlertTitle>Setup Required</AlertTitle>
      <AlertDescription>
        You need to configure your Site ID to start tracking.
        <router-link to="/setup" class="font-medium underline">Run setup wizard</router-link> or
        <router-link to="/settings" class="font-medium underline">configure manually</router-link>
      </AlertDescription>
    </Alert>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useSettingsStore } from '../stores/settings'
import { useNotificationsStore } from '../stores/notifications'
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  Button,
  Alert,
  AlertTitle,
  AlertDescription,
  Tooltip
} from '@components/ui'
import {
  CircleHelp,
  Loader2,
  CheckCircle2,
  Zap,
  Settings,
  ExternalLink,
  AlertTriangle,
  Info
} from 'lucide-vue-next'

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
