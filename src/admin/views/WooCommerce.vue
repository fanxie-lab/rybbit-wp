<template>
  <div class="woocommerce">
    <div class="mb-6">
      <h2 class="text-2xl font-semibold text-gray-800">WooCommerce Integration</h2>
      <p class="text-gray-600 mt-1">
        Automatically track ecommerce events from your WooCommerce store
      </p>
    </div>

    <!-- WooCommerce Not Installed Warning -->
    <div v-if="!isWooCommerceActive" class="woo-not-installed mb-6">
      <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
          <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="text-lg font-medium text-amber-800">WooCommerce Not Detected</h3>
          <p class="text-sm text-amber-700 mt-1">
            WooCommerce does not appear to be installed or activated on your site.
            These settings will be saved but ecommerce tracking will not function until WooCommerce is active.
          </p>
          <a
            href="https://wordpress.org/plugins/woocommerce/"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-1 text-sm text-amber-800 hover:text-amber-900 mt-2 font-medium"
          >
            Install WooCommerce
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </a>
        </div>
      </div>
    </div>

    <!-- WooCommerce Active Status -->
    <div v-else class="woo-active-status mb-6">
      <div class="flex items-center gap-3">
        <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-full">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <div>
          <p class="text-sm font-medium text-green-800">WooCommerce Detected</p>
          <p class="text-xs text-green-600">Ecommerce tracking is available for your store</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-4">
      <!-- Main Settings Panel -->
      <div class="space-y-4">
        <!-- Master Toggle Card -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <svg class="w-5 h-5 text-rybbit-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              Ecommerce Tracking
            </h3>
          </div>

          <div class="card-body">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <h4 class="text-base font-medium text-gray-900">Enable WooCommerce Tracking</h4>
                <p class="text-sm text-gray-500 mt-1">
                  When enabled, Rybbit will automatically track product views, cart additions,
                  checkout initiations, and completed purchases from your WooCommerce store.
                </p>
              </div>
              <ToggleSwitch
                v-model="localSettings.woocommerce.enabled"
                :disabled="loading"
              />
            </div>

            <!-- Disabled overlay message -->
            <div v-if="!localSettings.woocommerce.enabled" class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
              <p class="text-sm text-gray-500">
                Enable WooCommerce tracking above to configure individual event settings.
              </p>
            </div>
          </div>
        </div>

        <!-- Individual Event Toggles -->
        <div class="card" :class="{ 'opacity-60': !localSettings.woocommerce.enabled }">
          <div class="card-header">
            <h3 class="card-title">
              Event Configuration
              <HelpTooltip
                text="Choose which ecommerce events to track. Each event sends data to your Rybbit dashboard when the action occurs."
              />
            </h3>
          </div>

          <div class="card-body">
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
          </div>
        </div>

        <!-- Save Actions -->
        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 rounded-lg shadow-sm flex items-center justify-between gap-4">
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
              @click="saveSettings"
              :disabled="loading || !hasUnsavedChanges"
              class="btn btn-primary"
            >
              <svg v-if="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <LoadingSpinner v-else size="small" class="mr-2" />
              {{ loading ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Info Panel -->
      <div class="space-y-4">
        <!-- Info Card -->
        <div class="card bg-blue-50 border-blue-200">
          <div class="card-body">
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <h4 class="text-sm font-semibold text-blue-800">About Ecommerce Tracking</h4>
                <p class="text-sm text-blue-700 mt-1">
                  Rybbit captures ecommerce events in real-time as visitors interact with your store.
                  This data helps you understand your sales funnel and optimize conversions.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Event Data Summary -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title text-base">Data Captured</h3>
          </div>
          <div class="card-body">
            <ul class="space-y-2 text-sm">
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-gray-600">Product SKU, name, and category</span>
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-gray-600">Price and quantity</span>
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-gray-600">Order total, tax, and shipping</span>
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-gray-600">Transaction ID and currency</span>
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-gray-600">Variable product attributes</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Documentation Link -->
        <div class="card">
          <div class="card-body">
            <h4 class="text-sm font-semibold text-gray-800 mb-2">Need Help?</h4>
            <p class="text-sm text-gray-600 mb-3">
              Learn more about ecommerce tracking and how to analyze your store data.
            </p>
            <a
              href="https://www.rybbit.io/docs"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-2 text-sm text-rybbit-primary hover:text-rybbit-secondary font-medium"
            >
              View Documentation
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg>
            </a>
          </div>
        </div>

        <!-- Quick Stats Placeholder -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title text-base">Tracking Status</h3>
          </div>
          <div class="card-body">
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Master Toggle</span>
                <span :class="['text-sm font-medium', localSettings.woocommerce.enabled ? 'text-green-600' : 'text-gray-400']">
                  {{ localSettings.woocommerce.enabled ? 'Enabled' : 'Disabled' }}
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Active Events</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ activeEventCount }} / 4
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">WooCommerce</span>
                <span :class="['text-sm font-medium', isWooCommerceActive ? 'text-green-600' : 'text-amber-600']">
                  {{ isWooCommerceActive ? 'Active' : 'Not Found' }}
                </span>
              </div>
            </div>
          </div>
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

// Sample event data (will be fetched from API if WooCommerce is active)
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
    // Silently fail - use default sample data
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
  // Fetch sample events if WooCommerce is active
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

<style scoped>
.card {
  @apply bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden;
}

.card-header {
  @apply px-6 py-4 border-b border-gray-200 bg-gray-50;
}

.card-title {
  @apply text-lg font-medium text-gray-800 flex items-center;
}

.card-body {
  @apply p-6;
}

.btn {
  @apply px-4 py-2 rounded-md font-medium transition-colors inline-flex items-center justify-center;
}

.btn-primary {
  @apply bg-rybbit-primary text-white hover:bg-rybbit-secondary;
}

.btn-text {
  @apply text-gray-600 hover:text-gray-900;
}

.btn:disabled {
  @apply opacity-50 cursor-not-allowed;
}

.woo-not-installed {
  @apply p-4 bg-amber-50 border border-amber-200 rounded-lg;
}

.woo-active-status {
  @apply p-4 bg-green-50 border border-green-200 rounded-lg;
}
</style>
