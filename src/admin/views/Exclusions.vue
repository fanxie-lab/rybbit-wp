<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-semibold tracking-tight">Exclusions</h2>
      <p class="text-muted-foreground">
        Control which URLs and pages are excluded from or masked in analytics tracking
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- URL Pattern Exclusions Card -->
      <Card>
        <CardHeader>
          <CardTitle>
            URL Pattern Exclusions
            <Tooltip content="Pages matching these patterns will not be tracked. Use * for single path segment and ** for multiple segments.">
              <button type="button" class="inline-flex items-center justify-center text-muted-foreground hover:text-foreground">
                <CircleHelp class="h-4 w-4" />
              </button>
            </Tooltip>
          </CardTitle>
        </CardHeader>
        <CardContent class="pt-6 space-y-4">
          <p class="text-sm text-muted-foreground">
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
        </CardContent>
      </Card>

      <!-- URL Masking Patterns Card -->
      <Card>
        <CardHeader>
          <CardTitle>
            URL Masking Patterns
            <Tooltip content="URLs matching these patterns will have dynamic segments replaced with placeholders in your analytics, protecting sensitive data.">
              <button type="button" class="inline-flex items-center justify-center text-muted-foreground hover:text-foreground">
                <CircleHelp class="h-4 w-4" />
              </button>
            </Tooltip>
          </CardTitle>
        </CardHeader>
        <CardContent class="pt-6 space-y-4">
          <p class="text-sm text-muted-foreground">
            Mask dynamic URL segments to aggregate similar pages. For example,
            <code class="text-xs bg-muted px-1 py-0.5 rounded">/user/123/profile</code> becomes
            <code class="text-xs bg-muted px-1 py-0.5 rounded">/user/*/profile</code>.
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
        </CardContent>
      </Card>

      <!-- Individual Post/Page Exclusions - Full width -->
      <Card class="md:col-span-2">
        <CardHeader>
          <CardTitle>
            Individual Post/Page Exclusions
            <Tooltip content="Exclude specific posts or pages from tracking by searching and selecting them below.">
              <button type="button" class="inline-flex items-center justify-center text-muted-foreground hover:text-foreground">
                <CircleHelp class="h-4 w-4" />
              </button>
            </Tooltip>
          </CardTitle>
        </CardHeader>
        <CardContent class="pt-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
              <p class="text-sm text-muted-foreground">
                Search for specific posts or pages to exclude from analytics tracking.
                These pages will not appear in your Rybbit dashboard.
              </p>

              <PostSelector
                v-model="localSettings.exclusions"
                :excluded-ids="excludedPostIds"
                @select="onPostExcluded"
              />
            </div>

            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium">Excluded Content</h4>
                <Button
                  v-if="localSettings.exclusions.length > 0"
                  variant="ghost"
                  size="sm"
                  class="text-destructive hover:text-destructive"
                  @click="clearAllExclusions"
                >
                  <Trash2 class="h-4 w-4 mr-1" />
                  Clear all
                </Button>
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
        </CardContent>
      </Card>

      <!-- Wildcard Reference Card - Full width -->
      <Card class="md:col-span-2">
        <CardHeader class="bg-blue-50/50">
          <CardTitle class="text-blue-900">
            <Info class="h-5 w-5 mr-2 text-blue-600" />
            Pattern Wildcard Reference
          </CardTitle>
        </CardHeader>
        <CardContent class="pt-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="text-sm font-semibold mb-2">
                Single Segment Wildcard: <code class="bg-muted px-1.5 py-0.5 rounded">*</code>
              </h4>
              <p class="text-sm text-muted-foreground mb-2">
                Matches any single path segment (text between slashes).
              </p>
              <ul class="text-sm text-muted-foreground space-y-1">
                <li>
                  <code class="bg-muted px-1 py-0.5 rounded text-xs">/blog/*</code> matches
                  <code class="text-xs">/blog/post-1</code>, <code class="text-xs">/blog/my-article</code>
                </li>
                <li>
                  <code class="bg-muted px-1 py-0.5 rounded text-xs">/user/*/edit</code> matches
                  <code class="text-xs">/user/123/edit</code>, <code class="text-xs">/user/john/edit</code>
                </li>
              </ul>
            </div>

            <div>
              <h4 class="text-sm font-semibold mb-2">
                Multi-Segment Wildcard: <code class="bg-muted px-1.5 py-0.5 rounded">**</code>
              </h4>
              <p class="text-sm text-muted-foreground mb-2">
                Matches any number of path segments (including none).
              </p>
              <ul class="text-sm text-muted-foreground space-y-1">
                <li>
                  <code class="bg-muted px-1 py-0.5 rounded text-xs">/admin/**</code> matches
                  <code class="text-xs">/admin/settings</code>, <code class="text-xs">/admin/users/edit/5</code>
                </li>
                <li>
                  <code class="bg-muted px-1 py-0.5 rounded text-xs">/docs/**</code> matches all documentation pages
                </li>
              </ul>
            </div>
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
  Tooltip
} from '@components/ui'
import { CircleHelp, Loader2, Check, Trash2, Info } from 'lucide-vue-next'
import PatternInput from '../components/PatternInput.vue'
import PostSelector from '../components/PostSelector.vue'
import ExclusionList from '../components/ExclusionList.vue'

const settingsStore = useSettingsStore()
const notificationsStore = useNotificationsStore()
const { settings, loading } = storeToRefs(settingsStore)

const localSettings = ref({
  skip_patterns: [],
  mask_patterns: [],
  exclusions: []
})

const skipPatternExamples = ['/wp-admin/**', '/wp-login.php', '/checkout', '/cart']
const maskPatternExamples = ['/user/*', '/order/*', '/product/*/reviews']

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

function onPatternAdded(type) {}

function removeSkipPattern(index) {
  localSettings.value.skip_patterns = localSettings.value.skip_patterns.filter((_, i) => i !== index)
}

function removeMaskPattern(index) {
  localSettings.value.mask_patterns = localSettings.value.mask_patterns.filter((_, i) => i !== index)
}

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

onMounted(async () => {
  await settingsStore.fetchSettings()
  localSettings.value = {
    skip_patterns: [...(settings.value.skip_patterns || [])],
    mask_patterns: [...(settings.value.mask_patterns || [])],
    exclusions: JSON.parse(JSON.stringify(settings.value.exclusions || []))
  }
})

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
