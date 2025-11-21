<template>
  <div class="exclusions">
    <div class="mb-6">
      <h2 class="text-2xl font-semibold text-gray-800">Exclusions</h2>
      <p class="text-gray-600 mt-1">
        Control which URLs and pages are excluded from or masked in analytics tracking
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- URL Pattern Exclusions -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            URL Pattern Exclusions
            <HelpTooltip
              text="Pages matching these patterns will not be tracked. Use * for single path segment and ** for multiple segments."
            />
          </h3>
        </div>

        <div class="card-body space-y-4">
          <p class="text-sm text-gray-600">
            Add URL patterns to exclude specific pages or sections from tracking.
            Visitors to these URLs will not appear in your analytics.
          </p>

          <PatternInput
            v-model="localSettings.skip_patterns"
            placeholder="/example/* or /admin/**"
            label="Skip pattern"
            :examples="skipPatternExamples"
            pattern-type="url"
            @add="onPatternAdded('skip')"
          />

          <ExclusionList
            :items="localSettings.skip_patterns"
            empty-message="No URL patterns added"
            item-label="pattern"
            item-label-plural="patterns"
            @remove="removeSkipPattern"
          />
        </div>
      </div>

      <!-- URL Masking Patterns -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            URL Masking Patterns
            <HelpTooltip
              text="URLs matching these patterns will have dynamic segments replaced with placeholders in your analytics, protecting sensitive data."
            />
          </h3>
        </div>

        <div class="card-body space-y-4">
          <p class="text-sm text-gray-600">
            Mask dynamic URL segments to aggregate similar pages. For example,
            <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">/user/123/profile</code> becomes
            <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">/user/*/profile</code>.
          </p>

          <PatternInput
            v-model="localSettings.mask_patterns"
            placeholder="/user/*/profile"
            label="Mask pattern"
            :examples="maskPatternExamples"
            pattern-type="url"
            @add="onPatternAdded('mask')"
          />

          <ExclusionList
            :items="localSettings.mask_patterns"
            empty-message="No masking patterns added"
            item-label="pattern"
            item-label-plural="patterns"
            @remove="removeMaskPattern"
          />
        </div>
      </div>

      <!-- Individual Post/Page Exclusions - Full width -->
      <div class="card md:col-span-2">
        <div class="card-header">
          <h3 class="card-title">
            Individual Post/Page Exclusions
            <HelpTooltip
              text="Exclude specific posts or pages from tracking by searching and selecting them below."
            />
          </h3>
        </div>

        <div class="card-body">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Search section -->
            <div class="space-y-4">
              <p class="text-sm text-gray-600">
                Search for specific posts or pages to exclude from analytics tracking.
                These pages will not appear in your Rybbit dashboard.
              </p>

              <PostSelector
                v-model="localSettings.exclusions"
                :excluded-ids="excludedPostIds"
                @select="onPostExcluded"
              />
            </div>

            <!-- List section -->
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-700">Excluded Content</h4>
                <button
                  v-if="localSettings.exclusions.length > 0"
                  type="button"
                  class="text-xs text-red-600 hover:text-red-700"
                  @click="clearAllExclusions"
                >
                  Clear all
                </button>
              </div>

              <ExclusionList
                :items="localSettings.exclusions"
                empty-message="No posts or pages excluded"
                item-label="exclusion"
                item-label-plural="exclusions"
                @remove="removeExclusion"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Wildcard Reference - Full width -->
      <div class="card md:col-span-2">
        <div class="card-header bg-blue-50">
          <h3 class="card-title text-blue-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Pattern Wildcard Reference
          </h3>
        </div>

        <div class="card-body">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <h4 class="text-sm font-semibold text-gray-800 mb-2">Single Segment Wildcard: <code class="bg-gray-100 px-1.5 py-0.5 rounded">*</code></h4>
              <p class="text-sm text-gray-600 mb-2">
                Matches any single path segment (text between slashes).
              </p>
              <ul class="text-sm text-gray-600 space-y-1">
                <li><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">/blog/*</code> matches <code class="text-xs">/blog/post-1</code>, <code class="text-xs">/blog/my-article</code></li>
                <li><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">/user/*/edit</code> matches <code class="text-xs">/user/123/edit</code>, <code class="text-xs">/user/john/edit</code></li>
              </ul>
            </div>

            <div>
              <h4 class="text-sm font-semibold text-gray-800 mb-2">Multi-Segment Wildcard: <code class="bg-gray-100 px-1.5 py-0.5 rounded">**</code></h4>
              <p class="text-sm text-gray-600 mb-2">
                Matches any number of path segments (including none).
              </p>
              <ul class="text-sm text-gray-600 space-y-1">
                <li><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">/admin/**</code> matches <code class="text-xs">/admin/settings</code>, <code class="text-xs">/admin/users/edit/5</code></li>
                <li><code class="bg-gray-100 px-1 py-0.5 rounded text-xs">/docs/**</code> matches all documentation pages</li>
              </ul>
            </div>
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
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useSettingsStore } from '../stores/settings'
import { useNotificationsStore } from '../stores/notifications'
import HelpTooltip from '../components/HelpTooltip.vue'
import LoadingSpinner from '../components/LoadingSpinner.vue'
import PatternInput from '../components/PatternInput.vue'
import PostSelector from '../components/PostSelector.vue'
import ExclusionList from '../components/ExclusionList.vue'

const settingsStore = useSettingsStore()
const notificationsStore = useNotificationsStore()
const { settings, loading } = storeToRefs(settingsStore)

// Local state for form
const localSettings = ref({
  skip_patterns: [],
  mask_patterns: [],
  exclusions: []
})

// Example patterns for quick add
const skipPatternExamples = ['/wp-admin/**', '/wp-login.php', '/checkout', '/cart']
const maskPatternExamples = ['/user/*', '/order/*', '/product/*/reviews']

// Computed properties
const hasUnsavedChanges = computed(() => {
  return JSON.stringify({
    skip_patterns: localSettings.value.skip_patterns,
    mask_patterns: localSettings.value.mask_patterns,
    exclusions: localSettings.value.exclusions
  }) !== JSON.stringify({
    skip_patterns: settings.value.skip_patterns || [],
    mask_patterns: settings.value.mask_patterns || [],
    exclusions: settings.value.exclusions || []
  })
})

const excludedPostIds = computed(() => {
  return (localSettings.value.exclusions || [])
    .filter(e => e.type === 'post')
    .map(e => e.post_id)
})

// Pattern management
function onPatternAdded(type) {
  // Pattern was added via PatternInput v-model
  // Could show notification or perform additional logic
}

function removeSkipPattern(index) {
  localSettings.value.skip_patterns = localSettings.value.skip_patterns.filter((_, i) => i !== index)
}

function removeMaskPattern(index) {
  localSettings.value.mask_patterns = localSettings.value.mask_patterns.filter((_, i) => i !== index)
}

// Post exclusion management
function onPostExcluded(exclusion) {
  notificationsStore.info(`Added "${exclusion.title}" to exclusions`)
}

function removeExclusion(index) {
  const removed = localSettings.value.exclusions[index]
  localSettings.value.exclusions = localSettings.value.exclusions.filter((_, i) => i !== index)
  if (removed?.title) {
    notificationsStore.info(`Removed "${removed.title}" from exclusions`)
  }
}

function clearAllExclusions() {
  if (confirm('Are you sure you want to remove all excluded posts and pages?')) {
    localSettings.value.exclusions = []
    notificationsStore.info('All post/page exclusions cleared')
  }
}

// Save and reset
async function saveSettings() {
  try {
    await settingsStore.updateSettings({
      ...settings.value,
      skip_patterns: localSettings.value.skip_patterns,
      mask_patterns: localSettings.value.mask_patterns,
      exclusions: localSettings.value.exclusions
    })
    notificationsStore.success('Exclusion settings saved successfully!')
  } catch (error) {
    notificationsStore.error(`Error saving settings: ${error.message}`)
  }
}

function resetChanges() {
  localSettings.value = {
    skip_patterns: [...(settings.value.skip_patterns || [])],
    mask_patterns: [...(settings.value.mask_patterns || [])],
    exclusions: JSON.parse(JSON.stringify(settings.value.exclusions || []))
  }
  notificationsStore.info('Changes reset to last saved values')
}

// Initialize
onMounted(async () => {
  await settingsStore.fetchSettings()
  localSettings.value = {
    skip_patterns: [...(settings.value.skip_patterns || [])],
    mask_patterns: [...(settings.value.mask_patterns || [])],
    exclusions: JSON.parse(JSON.stringify(settings.value.exclusions || []))
  }
})

// Watch for external settings changes
watch(settings, (newSettings) => {
  if (!hasUnsavedChanges.value) {
    localSettings.value = {
      skip_patterns: [...(newSettings.skip_patterns || [])],
      mask_patterns: [...(newSettings.mask_patterns || [])],
      exclusions: JSON.parse(JSON.stringify(newSettings.exclusions || []))
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
  @apply text-lg font-medium text-gray-800 flex items-center gap-2;
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
</style>
