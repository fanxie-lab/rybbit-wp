<template>
  <div class="pattern-input">
    <div class="pattern-input-wrapper">
      <input
        ref="inputRef"
        v-model="inputValue"
        type="text"
        class="pattern-input-field"
        :class="{ 'pattern-input-error': hasError }"
        :placeholder="placeholder"
        :aria-label="label"
        @keydown.enter.prevent="addPattern"
        @input="validateInput"
        @blur="clearValidationOnBlur"
      />
      <button
        type="button"
        class="pattern-input-button"
        :disabled="!canAdd"
        @click="addPattern"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span class="sr-only">Add pattern</span>
      </button>
    </div>

    <!-- Validation feedback -->
    <div v-if="hasError" class="pattern-input-feedback pattern-input-feedback-error">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      {{ errorMessage }}
    </div>

    <div v-else-if="inputValue && isValid" class="pattern-input-feedback pattern-input-feedback-success">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      Valid pattern
    </div>

    <!-- Examples -->
    <div v-if="showExamples && examples.length" class="pattern-input-examples">
      <span class="pattern-input-examples-label">Examples:</span>
      <button
        v-for="example in examples"
        :key="example"
        type="button"
        class="pattern-input-example"
        @click="useExample(example)"
      >
        {{ example }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  placeholder: {
    type: String,
    default: 'Enter a URL pattern'
  },
  label: {
    type: String,
    default: 'URL pattern'
  },
  examples: {
    type: Array,
    default: () => []
  },
  showExamples: {
    type: Boolean,
    default: true
  },
  allowDuplicates: {
    type: Boolean,
    default: false
  },
  patternType: {
    type: String,
    default: 'url', // 'url' or 'selector'
    validator: (value) => ['url', 'selector'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'add'])

const inputRef = ref(null)
const inputValue = ref('')
const errorMessage = ref('')
const isValid = ref(false)

const hasError = computed(() => !!errorMessage.value)

const canAdd = computed(() => {
  return inputValue.value.trim() && isValid.value && !hasError.value
})

/**
 * Validate URL pattern
 * Supports wildcards: * (single segment) and ** (multiple segments)
 */
function validateUrlPattern(pattern) {
  const trimmed = pattern.trim()

  if (!trimmed) {
    return { valid: false, error: '' }
  }

  // Must start with / for URL patterns
  if (!trimmed.startsWith('/')) {
    return { valid: false, error: 'URL pattern must start with /' }
  }

  // Check for invalid characters
  const invalidChars = /[<>"|{}^`\\]/
  if (invalidChars.test(trimmed)) {
    return { valid: false, error: 'Pattern contains invalid characters' }
  }

  // Check for consecutive slashes (except after protocol)
  if (/\/\/+/.test(trimmed.replace(/^https?:/, ''))) {
    return { valid: false, error: 'Pattern cannot contain consecutive slashes' }
  }

  // Check for valid wildcard usage
  const segments = trimmed.split('/').filter(Boolean)
  for (const segment of segments) {
    // ** must be alone in a segment
    if (segment.includes('**') && segment !== '**') {
      return { valid: false, error: '** wildcard must be the only content in its path segment' }
    }
  }

  // Check if already exists
  if (!props.allowDuplicates && props.modelValue.includes(trimmed)) {
    return { valid: false, error: 'This pattern already exists' }
  }

  return { valid: true, error: '' }
}

/**
 * Validate CSS selector pattern
 */
function validateSelectorPattern(pattern) {
  const trimmed = pattern.trim()

  if (!trimmed) {
    return { valid: false, error: '' }
  }

  // Basic selector validation - must start with . # or tag name
  if (!/^[.#\[\w]/.test(trimmed)) {
    return { valid: false, error: 'Selector must start with ., #, [, or a tag name' }
  }

  // Check for invalid characters
  const invalidChars = /[<>"'|{}^`\\]/
  if (invalidChars.test(trimmed)) {
    return { valid: false, error: 'Selector contains invalid characters' }
  }

  // Check if already exists
  if (!props.allowDuplicates && props.modelValue.includes(trimmed)) {
    return { valid: false, error: 'This selector already exists' }
  }

  return { valid: true, error: '' }
}

function validateInput() {
  const value = inputValue.value.trim()

  if (!value) {
    errorMessage.value = ''
    isValid.value = false
    return
  }

  const result = props.patternType === 'url'
    ? validateUrlPattern(value)
    : validateSelectorPattern(value)

  errorMessage.value = result.error
  isValid.value = result.valid
}

function clearValidationOnBlur() {
  if (!inputValue.value.trim()) {
    errorMessage.value = ''
    isValid.value = false
  }
}

function addPattern() {
  if (!canAdd.value) return

  const pattern = inputValue.value.trim()
  const newPatterns = [...props.modelValue, pattern]

  emit('update:modelValue', newPatterns)
  emit('add', pattern)

  inputValue.value = ''
  errorMessage.value = ''
  isValid.value = false

  // Focus back to input for quick successive entries
  inputRef.value?.focus()
}

function useExample(example) {
  inputValue.value = example
  validateInput()
  inputRef.value?.focus()
}

// Re-validate when patterns change (for duplicate detection)
watch(() => props.modelValue, () => {
  if (inputValue.value) {
    validateInput()
  }
}, { deep: true })
</script>

<style scoped>
.pattern-input {
  @apply space-y-2;
}

.pattern-input-wrapper {
  @apply flex gap-2;
}

.pattern-input-field {
  @apply flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm;
  @apply focus:outline-none focus:ring-2 focus:ring-rybbit-primary focus:border-transparent;
  @apply transition-colors;
  font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
}

.pattern-input-error {
  @apply border-red-500 focus:ring-red-500;
}

.pattern-input-button {
  @apply px-3 py-2 bg-rybbit-primary text-white rounded-md;
  @apply hover:bg-rybbit-secondary transition-colors;
  @apply focus:outline-none focus:ring-2 focus:ring-rybbit-primary focus:ring-offset-2;
  @apply disabled:opacity-50 disabled:cursor-not-allowed;
}

.pattern-input-feedback {
  @apply flex items-center gap-1.5 text-sm;
}

.pattern-input-feedback-error {
  @apply text-red-600;
}

.pattern-input-feedback-success {
  @apply text-green-600;
}

.pattern-input-examples {
  @apply flex flex-wrap items-center gap-2 text-sm;
}

.pattern-input-examples-label {
  @apply text-gray-500;
}

.pattern-input-example {
  @apply px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs;
  @apply hover:bg-gray-200 transition-colors;
  @apply focus:outline-none focus:ring-2 focus:ring-rybbit-primary focus:ring-offset-1;
  font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
}

.sr-only {
  @apply absolute w-px h-px p-0 -m-px overflow-hidden whitespace-nowrap border-0;
  clip: rect(0, 0, 0, 0);
}
</style>
