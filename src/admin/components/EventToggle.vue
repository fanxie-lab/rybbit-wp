<template>
  <div class="event-toggle" :class="{ 'event-toggle-disabled': disabled }">
    <div class="event-toggle-header">
      <div class="event-toggle-main">
        <div class="event-toggle-switch">
          <ToggleSwitch
            :model-value="modelValue"
            :disabled="disabled"
            @update:model-value="$emit('update:modelValue', $event)"
          />
        </div>

        <div class="event-toggle-content">
          <div class="event-toggle-title-row">
            <h4 class="event-toggle-title" :class="{ 'text-gray-400': disabled }">
              {{ title }}
            </h4>
            <code class="event-toggle-code" :class="{ 'opacity-50': disabled }">
              {{ eventName }}
            </code>
          </div>
          <p class="event-toggle-description" :class="{ 'text-gray-400': disabled }">
            {{ description }}
          </p>
        </div>
      </div>

      <button
        type="button"
        class="event-toggle-expand-btn"
        :class="{ 'text-gray-400': disabled }"
        :disabled="disabled"
        @click="$emit('toggle-expand')"
        :aria-expanded="expanded"
        :aria-label="expanded ? 'Collapse sample data' : 'Expand sample data'"
      >
        <span class="text-xs mr-1">{{ expanded ? 'Hide' : 'Sample' }}</span>
        <svg
          class="w-4 h-4 transition-transform duration-200"
          :class="{ 'rotate-180': expanded }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
    </div>

    <!-- Expandable Sample Data Preview -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-1"
    >
      <div v-if="expanded && !disabled" class="event-toggle-preview">
        <div class="event-toggle-preview-header">
          <span class="event-toggle-preview-label">Sample Event Data</span>
          <button
            type="button"
            class="event-toggle-copy-btn"
            @click="copyToClipboard"
            :title="copied ? 'Copied!' : 'Copy to clipboard'"
          >
            <svg v-if="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <svg v-else class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </button>
        </div>
        <pre class="event-toggle-json"><code>{{ formattedJson }}</code></pre>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import ToggleSwitch from './ToggleSwitch.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  eventName: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  sampleData: {
    type: Object,
    default: () => ({})
  },
  disabled: {
    type: Boolean,
    default: false
  },
  expanded: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:modelValue', 'toggle-expand'])

const copied = ref(false)

const formattedJson = computed(() => {
  return JSON.stringify(props.sampleData, null, 2)
})

async function copyToClipboard() {
  try {
    await navigator.clipboard.writeText(formattedJson.value)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}
</script>

<style scoped>
.event-toggle {
  @apply rounded-lg border border-gray-200 bg-white transition-all duration-200;
}

.event-toggle:hover:not(.event-toggle-disabled) {
  @apply border-gray-300;
}

.event-toggle-disabled {
  @apply opacity-60;
}

.event-toggle-header {
  @apply flex items-start justify-between gap-4 p-4;
}

.event-toggle-main {
  @apply flex items-start gap-3 flex-1;
}

.event-toggle-switch {
  @apply flex-shrink-0 pt-0.5;
}

.event-toggle-content {
  @apply flex-1 min-w-0;
}

.event-toggle-title-row {
  @apply flex items-center gap-2 flex-wrap;
}

.event-toggle-title {
  @apply text-sm font-medium text-gray-900;
}

.event-toggle-code {
  @apply text-xs font-mono bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded;
}

.event-toggle-description {
  @apply text-sm text-gray-500 mt-0.5;
}

.event-toggle-expand-btn {
  @apply flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors flex-shrink-0;
}

.event-toggle-expand-btn:disabled {
  @apply cursor-not-allowed hover:text-gray-400;
}

.event-toggle-preview {
  @apply px-4 pb-4;
}

.event-toggle-preview-header {
  @apply flex items-center justify-between mb-2;
}

.event-toggle-preview-label {
  @apply text-xs font-medium text-gray-500 uppercase tracking-wider;
}

.event-toggle-copy-btn {
  @apply p-1 text-gray-400 hover:text-gray-600 transition-colors rounded;
}

.event-toggle-json {
  @apply bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-xs font-mono leading-relaxed;
  max-height: 300px;
}

.event-toggle-json code {
  @apply text-green-400;
}

/* Syntax highlighting for JSON */
.event-toggle-json :deep(.string) {
  @apply text-green-400;
}

.event-toggle-json :deep(.number) {
  @apply text-blue-400;
}

.event-toggle-json :deep(.boolean) {
  @apply text-purple-400;
}

.event-toggle-json :deep(.null) {
  @apply text-gray-500;
}

.event-toggle-json :deep(.key) {
  @apply text-cyan-400;
}
</style>
