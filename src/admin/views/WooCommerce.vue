<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-semibold tracking-tight">WooCommerce Integration</h2>
      <p class="text-muted-foreground">
        Automatically track ecommerce events from your WooCommerce store
      </p>
    </div>

    <!-- WooCommerce Status Alert -->
    <Alert v-if="!isWooCommerceActive" variant="warning">
      <AlertTriangle class="h-4 w-4" />
      <AlertTitle>WooCommerce Not Detected</AlertTitle>
      <AlertDescription>
        WooCommerce does not appear to be installed or activated on your site.
        These settings will be saved but ecommerce tracking will not function until WooCommerce is active.
        <a
          href="https://wordpress.org/plugins/woocommerce/"
          target="_blank"
          rel="noopener noreferrer"
          class="font-medium underline inline-flex items-center gap-1 ml-1"
        >
          Install WooCommerce
          <ExternalLink class="h-3 w-3" />
        </a>
      </AlertDescription>
    </Alert>

    <Alert v-else variant="success">
      <CheckCircle2 class="h-4 w-4" />
      <AlertTitle>WooCommerce Detected</AlertTitle>
      <AlertDescription>
        Ecommerce tracking is available for your store
      </AlertDescription>
    </Alert>

    <!-- Main layout: main content + sidebar -->
    <div class="flex flex-col lg:flex-row gap-6">
      <!-- Main Settings Panel -->
      <div class="flex-1 min-w-0 space-y-6">
        <!-- Master Toggle Card -->
        <Card>
          <CardHeader>
            <CardTitle>
              <ShoppingCart class="h-5 w-5 text-primary mr-2" />
              Ecommerce Tracking
            </CardTitle>
          </CardHeader>
          <CardContent class="pt-6">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 space-y-1">
                <h4 class="text-base font-medium">Enable WooCommerce Tracking</h4>
                <p class="text-sm text-muted-foreground">
                  When enabled, Rybbit will automatically track product views, cart additions,
                  checkout initiations, and completed purchases from your WooCommerce store.
                </p>
              </div>
              <Switch
                v-model="localSettings.woocommerce.enabled"
                :disabled="loading"
              />
            </div>

            <!-- Disabled message -->
            <Alert v-if="!localSettings.woocommerce.enabled" variant="default" class="mt-4">
              <Info class="h-4 w-4" />
              <AlertDescription>
                Enable WooCommerce tracking above to configure individual event settings.
              </AlertDescription>
            </Alert>
          </CardContent>
        </Card>

        <!-- Individual Event Toggles Card -->
        <Card :class="{ 'opacity-60': !localSettings.woocommerce.enabled }">
          <CardHeader>
            <CardTitle>
              Event Configuration
              <Tooltip content="Choose which ecommerce events to track. Each event sends data to your Rybbit dashboard when the action occurs.">
                <button type="button" class="inline-flex items-center justify-center text-muted-foreground hover:text-foreground">
                  <CircleHelp class="h-4 w-4" />
                </button>
              </Tooltip>
            </CardTitle>
          </CardHeader>
          <CardContent class="pt-6">
            <div class="space-y-4">
              <!-- View Item Event -->
              <EventToggle
                v-model="localSettings.woocommerce.events.view_item"
                event-name="view_item"
                title="Product Page Views"
                description="Tracks when a visitor views a product detail page"
                :sample-data="sampleEvents.view_item"
                :disabled="!localSettings.woocommerce.enabled || loading"
                :expanded="expandedEvent === 'view_item'"
                @toggle-expand="toggleExpand('view_item')"
              />

              <!-- Add to Cart Event -->
              <EventToggle
                v-model="localSettings.woocommerce.events.add_to_cart"
                event-name="add_to_cart"
                title="Add to Cart"
                description="Tracks when a product is added to the shopping cart (including AJAX)"
                :sample-data="sampleEvents.add_to_cart"
                :disabled="!localSettings.woocommerce.enabled || loading"
                :expanded="expandedEvent === 'add_to_cart'"
                @toggle-expand="toggleExpand('add_to_cart')"
              />

              <!-- Begin Checkout Event -->
              <EventToggle
                v-model="localSettings.woocommerce.events.begin_checkout"
                event-name="begin_checkout"
                title="Checkout Initiated"
                description="Tracks when a visitor begins the checkout process"
                :sample-data="sampleEvents.begin_checkout"
                :disabled="!localSettings.woocommerce.enabled || loading"
                :expanded="expandedEvent === 'begin_checkout'"
                @toggle-expand="toggleExpand('begin_checkout')"
              />

              <!-- Purchase Event -->
              <EventToggle
                v-model="localSettings.woocommerce.events.purchase"
                event-name="purchase"
                title="Order Completed"
                description="Tracks when a purchase is successfully completed"
                :sample-data="sampleEvents.purchase"
                :disabled="!localSettings.woocommerce.enabled || loading"
                :expanded="expandedEvent === 'purchase'"
                @toggle-expand="toggleExpand('purchase')"
              />
            </div>
          </CardContent>
        </Card>

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
              :disabled="loading || !hasUnsavedChanges"
              @click="saveSettings"
            >
              <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
              <Check v-else class="mr-2 h-4 w-4" />
              {{ loading ? 'Saving...' : 'Save Changes' }}
            </Button>
          </div>
        </div>
      </div>

      <!-- Sidebar Info Panel -->
      <div class="lg:w-80 flex-shrink-0 space-y-6">
        <!-- Info Card -->
        <Alert variant="info" class="shadow-sm">
          <Info class="h-4 w-4" />
          <AlertTitle>About Ecommerce Tracking</AlertTitle>
          <AlertDescription>
            Rybbit captures ecommerce events in real-time as visitors interact with your store.
            This data helps you understand your sales funnel and optimize conversions.
          </AlertDescription>
        </Alert>

        <!-- Event Data Summary Card -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">Data Captured</CardTitle>
          </CardHeader>
          <CardContent class="pt-6">
            <ul class="space-y-2 text-sm">
              <li v-for="item in dataCaptured" :key="item" class="flex items-start gap-2">
                <CheckCircle2 class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                <span class="text-muted-foreground">{{ item }}</span>
              </li>
            </ul>
          </CardContent>
        </Card>

        <!-- Documentation Link Card -->
        <Card>
          <CardContent class="pt-6">
            <h4 class="text-sm font-semibold mb-2">Need Help?</h4>
            <p class="text-sm text-muted-foreground mb-3">
              Learn more about ecommerce tracking and how to analyze your store data.
            </p>
            <a
              href="https://www.rybbit.io/docs"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-2 text-sm text-primary font-medium hover:underline"
            >
              View Documentation
              <ExternalLink class="h-4 w-4" />
            </a>
          </CardContent>
        </Card>

        <!-- Quick Stats Card -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">Tracking Status</CardTitle>
          </CardHeader>
          <CardContent class="pt-6">
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Master Toggle</span>
                <Badge :variant="localSettings.woocommerce.enabled ? 'success' : 'secondary'">
                  {{ localSettings.woocommerce.enabled ? 'Enabled' : 'Disabled' }}
                </Badge>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Active Events</span>
                <span class="text-sm font-medium">
                  {{ activeEventCount }} / 4
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">WooCommerce</span>
                <Badge :variant="isWooCommerceActive ? 'success' : 'warning'">
                  {{ isWooCommerceActive ? 'Active' : 'Not Found' }}
                </Badge>
              </div>
            </div>
          </CardContent>
        </Card>
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
  Switch,
  Alert,
  AlertTitle,
  AlertDescription,
  Badge,
  Tooltip
} from '@components/ui'
import {
  ShoppingCart,
  CircleHelp,
  Loader2,
  Check,
  CheckCircle2,
  AlertTriangle,
  ExternalLink,
  Info
} from 'lucide-vue-next'
import EventToggle from '../components/EventToggle.vue'
import api from '../services/api'

const settingsStore = useSettingsStore()
const notificationsStore = useNotificationsStore()
const { settings, loading } = storeToRefs(settingsStore)

// WordPress data
const wpData = window.rybbitAdmin || {}
const isWooCommerceActive = computed(() => wpData.isWooCommerce || false)

// Local state for form
const localSettings = ref({
  woocommerce: {
    enabled: false,
    events: {
      view_item: true,
      add_to_cart: true,
      begin_checkout: true,
      purchase: true
    }
  }
})

// Sample event data
const sampleEvents = ref({
  view_item: {
    event: 'view_item',
    currency: 'USD',
    value: 29.99,
    items: [{
      item_id: 'SKU-12345',
      item_name: 'Sample Product',
      item_category: 'Clothing',
      price: 29.99,
      quantity: 1
    }]
  },
  add_to_cart: {
    event: 'add_to_cart',
    currency: 'USD',
    value: 29.99,
    items: [{
      item_id: 'SKU-12345',
      item_name: 'Sample Product',
      item_category: 'Clothing',
      price: 29.99,
      quantity: 1
    }]
  },
  begin_checkout: {
    event: 'begin_checkout',
    currency: 'USD',
    value: 59.98,
    items: [{
      item_id: 'SKU-12345',
      item_name: 'Sample Product',
      item_category: 'Clothing',
      price: 29.99,
      quantity: 2
    }]
  },
  purchase: {
    event: 'purchase',
    transaction_id: 'WC-1234',
    currency: 'USD',
    value: 67.97,
    tax: 5.99,
    shipping: 2.00,
    items: [{
      item_id: 'SKU-12345',
      item_name: 'Sample Product',
      item_category: 'Clothing',
      price: 29.99,
      quantity: 2
    }]
  }
})

// Static data
const dataCaptured = [
  'Product SKU, name, and category',
  'Price and quantity',
  'Order total, tax, and shipping',
  'Transaction ID and currency',
  'Variable product attributes',
]

// Expanded event tracking
const expandedEvent = ref(null)

// Computed properties
const hasUnsavedChanges = computed(() => {
  const current = JSON.stringify(localSettings.value.woocommerce)
  const saved = JSON.stringify(settings.value.woocommerce || {
    enabled: false,
    events: {
      view_item: true,
      add_to_cart: true,
      begin_checkout: true,
      purchase: true
    }
  })
  return current !== saved
})

const activeEventCount = computed(() => {
  if (!localSettings.value.woocommerce.enabled) return 0
  const events = localSettings.value.woocommerce.events
  return Object.values(events).filter(Boolean).length
})

// Methods
function toggleExpand(eventName) {
  expandedEvent.value = expandedEvent.value === eventName ? null : eventName
}

async function fetchSampleEvents() {
  if (!isWooCommerceActive.value) return

  try {
    const data = await api.getWooCommerceSampleEvents()
    if (data && Object.keys(data).length > 0) {
      sampleEvents.value = { ...sampleEvents.value, ...data }
    }
  } catch (error) {
    console.debug('Could not fetch sample events:', error.message)
  }
}

async function saveSettings() {
  try {
    await settingsStore.updateSettings({
      ...settings.value,
      woocommerce: localSettings.value.woocommerce
    })
    notificationsStore.success('WooCommerce settings saved successfully!')
  } catch (error) {
    notificationsStore.error(`Error saving settings: ${error.message}`)
  }
}

function resetChanges() {
  localSettings.value = {
    woocommerce: JSON.parse(JSON.stringify(settings.value.woocommerce || {
      enabled: false,
      events: {
        view_item: true,
        add_to_cart: true,
        begin_checkout: true,
        purchase: true
      }
    }))
  }
  notificationsStore.info('Changes reset to last saved values')
}

// Initialize
onMounted(async () => {
  await settingsStore.fetchSettings()
  localSettings.value = {
    woocommerce: JSON.parse(JSON.stringify(settings.value.woocommerce || {
      enabled: false,
      events: {
        view_item: true,
        add_to_cart: true,
        begin_checkout: true,
        purchase: true
      }
    }))
  }
  await fetchSampleEvents()
})

// Watch for external settings changes
watch(settings, (newSettings) => {
  if (!hasUnsavedChanges.value) {
    localSettings.value = {
      woocommerce: JSON.parse(JSON.stringify(newSettings.woocommerce || {
        enabled: false,
        events: {
          view_item: true,
          add_to_cart: true,
          begin_checkout: true,
          purchase: true
        }
      }))
    }
  }
}, { deep: true })
</script>
