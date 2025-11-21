<template>
  <div class="toggle-switch-container">
    <button
      type="button"
      :class="['toggle-switch', { 'toggle-switch-on': modelValue, 'toggle-switch-off': !modelValue }]"
      role="switch"
      :aria-checked="modelValue"
      :aria-label="label"
      :disabled="disabled"
      @click="toggle"
    >
      <span
        :class="['toggle-switch-thumb', { 'toggle-switch-thumb-on': modelValue, 'toggle-switch-thumb-off': !modelValue }]"
        aria-hidden="true"
      />
    </button>
    <label v-if="label" class="toggle-switch-label" @click="toggle">
      {{ label }}
      <HelpTooltip v-if="helpText" :text="helpText" />
    </label>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import HelpTooltip from './HelpTooltip.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

function toggle() {
  if (!props.disabled) {
    emit('update:modelValue', !props.modelValue)
  }
}
</script>

<style scoped>
.toggle-switch-container {
  @apply flex items-center gap-3;
}

.toggle-switch {
  @apply relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out;
  @apply focus:outline-none focus:ring-2 focus:ring-rybbit-primary focus:ring-offset-2;
}

.toggle-switch-on {
  @apply bg-rybbit-primary;
}

.toggle-switch-off {
  @apply bg-gray-200;
}

.toggle-switch:disabled {
  @apply opacity-50 cursor-not-allowed;
}

.toggle-switch-thumb {
  @apply pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out;
}

.toggle-switch-thumb-on {
  @apply translate-x-5;
}

.toggle-switch-thumb-off {
  @apply translate-x-0;
}

.toggle-switch-label {
  @apply text-sm font-medium text-gray-700 cursor-pointer select-none flex items-center gap-1;
}

.toggle-switch:disabled + .toggle-switch-label {
  @apply opacity-50 cursor-not-allowed;
}
</style>
