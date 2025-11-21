<template>
  <div class="setup-wizard">
    <div class="max-w-3xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome to Rybbit Analytics</h2>
        <p class="text-gray-600">Let's get your site connected in just a few steps</p>
      </div>

      <!-- Progress Steps -->
      <div class="progress-steps mb-8">
        <div
          v-for="(step, index) in steps"
          :key="index"
          :class="['progress-step', {
            'progress-step-active': currentStep === index,
            'progress-step-completed': currentStep > index,
            'progress-step-upcoming': currentStep < index
          }]"
        >
          <div class="progress-step-circle">
            <svg v-if="currentStep > index" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span v-else>{{ index + 1 }}</span>
          </div>
          <div class="progress-step-label">{{ step.title }}</div>
        </div>
      </div>

      <!-- Step Content -->
      <div class="wizard-content">
        <!-- Step 0: Welcome -->
        <div v-if="currentStep === 0" class="wizard-step">
          <div class="text-center space-y-6">
            <div class="flex justify-center">
              <div class="w-20 h-20 bg-rybbit-lighter rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-rybbit-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
            </div>
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-3">Privacy-friendly Analytics</h3>
              <p class="text-gray-600 max-w-xl mx-auto">
                Rybbit Analytics is a cookieless, privacy-friendly alternative to Google Analytics.
                Track your website visitors without compromising their privacy or needing cookie consent banners.
              </p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-left max-w-xl mx-auto">
              <h4 class="font-medium text-blue-900 mb-2">What you'll need:</h4>
              <ul class="space-y-1 text-sm text-blue-800">
                <li class="flex items-start">
                  <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  A Rybbit account (sign up at <a href="https://app.rybbit.io" target="_blank" class="underline">app.rybbit.io</a>)
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  Your Site ID from the Rybbit dashboard
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Step 1: Site ID Configuration -->
        <div v-if="currentStep === 1" class="wizard-step">
          <div class="space-y-6">
            <div class="text-center mb-6">
              <h3 class="text-xl font-semibold text-gray-900 mb-2">Enter Your Site ID</h3>
              <p class="text-gray-600">
                Find your Site ID in your Rybbit dashboard under Site Settings
              </p>
            </div>

            <div class="max-w-md mx-auto space-y-4">
              <div>
                <label for="site-id-input" class="block text-sm font-medium text-gray-700 mb-2">
                  Site ID <span class="text-red-500">*</span>
                </label>
                <input
                  id="site-id-input"
                  v-model="formData.siteId"
                  type="text"
                  class="input"
                  :class="{ 'input-error': validationError }"
                  placeholder="e.g., my-awesome-site"
                  @input="validationError = ''"
                />
                <p v-if="validationError" class="mt-2 text-sm text-red-600">
                  {{ validationError }}
                </p>
              </div>

              <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">How to find your Site ID:</h4>
                <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600">
                  <li>Go to <a href="https://app.rybbit.io" target="_blank" class="text-rybbit-primary hover:underline">app.rybbit.io</a></li>
                  <li>Navigate to your site's settings</li>
                  <li>Copy the Site ID shown at the top</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: Test Connection -->
        <div v-if="currentStep === 2" class="wizard-step">
          <div class="space-y-6">
            <div class="text-center">
              <LoadingSpinner v-if="testing" size="large" text="Testing connection..." />

              <div v-else-if="connectionResult" class="space-y-4">
                <div v-if="connectionResult.success" class="space-y-4">
                  <div class="flex justify-center">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                      <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </div>
                  <div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">You're all set!</h3>
                    <p class="text-gray-600">
                      Your site is now connected to Rybbit Analytics
                    </p>
                  </div>
                  <div class="bg-green-50 border border-green-200 rounded-lg p-4 max-w-md mx-auto">
                    <p class="text-sm text-green-800">
                      <strong>Site ID:</strong> <code class="px-2 py-1 bg-white rounded">{{ formData.siteId }}</code>
                    </p>
                  </div>
                  <div class="text-sm text-gray-600">
                    <p>The tracking script is now active on your site.</p>
                    <p class="mt-2">Visit your site and then check your Rybbit dashboard to see real-time data.</p>
                  </div>
                </div>

                <div v-else class="space-y-4">
                  <div class="flex justify-center">
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center">
                      <svg class="w-10 h-10 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </div>
                  <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Connection Failed</h3>
                    <p class="text-gray-600">
                      {{ connectionResult.message || 'Could not verify your Site ID. Please check and try again.' }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="wizard-actions">
        <button
          v-if="currentStep > 0 && currentStep < steps.length - 1"
          @click="previousStep"
          class="btn btn-secondary"
          :disabled="testing"
        >
          Back
        </button>

        <div class="flex-1"></div>

        <button
          v-if="currentStep < steps.length - 2"
          @click="nextStep"
          class="btn btn-primary"
        >
          {{ currentStep === 0 ? 'Get Started' : 'Continue' }}
        </button>

        <button
          v-if="currentStep === 1"
          @click="testAndComplete"
          class="btn btn-primary"
          :disabled="!formData.siteId || testing"
        >
          Test Connection
        </button>

        <button
          v-if="currentStep === steps.length - 1 && connectionResult?.success"
          @click="finishSetup"
          class="btn btn-primary"
        >
          Go to Dashboard
        </button>

        <button
          v-if="currentStep === steps.length - 1 && !connectionResult?.success"
          @click="currentStep = 1"
          class="btn btn-primary"
        >
          Try Again
        </button>

        <button
          v-if="currentStep > 0"
          @click="skipSetup"
          class="btn btn-text ml-4"
          :disabled="testing"
        >
          Skip Setup
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useSettingsStore } from '../stores/settings'
import { useNotificationsStore } from '../stores/notifications'
import LoadingSpinner from '../components/LoadingSpinner.vue'

const router = useRouter()
const settingsStore = useSettingsStore()
const notificationsStore = useNotificationsStore()

const steps = [
  { title: 'Welcome', key: 'welcome' },
  { title: 'Site ID', key: 'site-id' },
  { title: 'Complete', key: 'complete' }
]

const currentStep = ref(0)
const testing = ref(false)
const validationError = ref('')
const connectionResult = ref(null)

const formData = reactive({
  siteId: '',
  scriptUrl: 'https://app.rybbit.io/api/script.js'
})

function nextStep() {
  if (currentStep.value < steps.length - 1) {
    currentStep.value++
  }
}

function previousStep() {
  if (currentStep.value > 0) {
    currentStep.value--
    connectionResult.value = null
  }
}

async function testAndComplete() {
  // Validate Site ID
  if (!formData.siteId || formData.siteId.trim() === '') {
    validationError.value = 'Please enter a Site ID'
    return
  }

  if (!/^[a-zA-Z0-9-_]+$/.test(formData.siteId)) {
    validationError.value = 'Site ID can only contain letters, numbers, hyphens, and underscores'
    return
  }

  testing.value = true
  validationError.value = ''
  connectionResult.value = null

  try {
    // Test connection
    const result = await settingsStore.testConnection(formData.siteId)

    // If successful, save settings
    if (result.success) {
      await settingsStore.updateSettings({
        site_id: formData.siteId,
        script_url: formData.scriptUrl,
        connected: true
      })

      connectionResult.value = { success: true, message: result.message }
      notificationsStore.success('Site connected successfully!')
    } else {
      connectionResult.value = { success: false, message: result.message }
    }

    // Move to completion step
    currentStep.value = 2
  } catch (error) {
    connectionResult.value = {
      success: false,
      message: error.message || 'Failed to test connection'
    }
    notificationsStore.error(`Connection test failed: ${error.message}`)
    currentStep.value = 2
  } finally {
    testing.value = false
  }
}

function finishSetup() {
  router.push('/')
}

async function skipSetup() {
  if (formData.siteId) {
    try {
      await settingsStore.updateSettings({
        site_id: formData.siteId,
        script_url: formData.scriptUrl,
        connected: false
      })
    } catch (error) {
      console.error('Failed to save settings:', error)
    }
  }

  router.push('/')
}
</script>

<style scoped>
.setup-wizard {
  @apply py-8;
}

.progress-steps {
  @apply flex items-center justify-center space-x-4;
}

.progress-step {
  @apply flex flex-col items-center;
}

.progress-step-circle {
  @apply w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm mb-2;
  @apply transition-all duration-300;
}

.progress-step-active .progress-step-circle {
  @apply bg-rybbit-primary text-white ring-4 ring-rybbit-lighter;
}

.progress-step-completed .progress-step-circle {
  @apply bg-green-500 text-white;
}

.progress-step-upcoming .progress-step-circle {
  @apply bg-gray-200 text-gray-500;
}

.progress-step-label {
  @apply text-sm font-medium text-gray-600;
}

.progress-step-active .progress-step-label {
  @apply text-rybbit-primary font-semibold;
}

.wizard-content {
  @apply bg-white rounded-lg border border-gray-200 shadow-sm p-8 min-h-[400px] mb-6;
}

.wizard-step {
  @apply animate-fadeIn;
}

.wizard-actions {
  @apply flex items-center justify-between gap-4;
}

.input {
  @apply w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rybbit-primary focus:border-transparent;
}

.input-error {
  @apply border-red-500 focus:ring-red-500;
}

.btn {
  @apply px-6 py-2.5 rounded-md font-medium transition-colors;
}

.btn-primary {
  @apply bg-rybbit-primary text-white hover:bg-rybbit-secondary;
}

.btn-primary:disabled {
  @apply opacity-50 cursor-not-allowed;
}

.btn-secondary {
  @apply bg-gray-100 text-gray-700 hover:bg-gray-200;
}

.btn-secondary:disabled {
  @apply opacity-50 cursor-not-allowed;
}

.btn-text {
  @apply text-gray-600 hover:text-gray-900;
}

.btn-text:disabled {
  @apply opacity-50 cursor-not-allowed;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}
</style>
