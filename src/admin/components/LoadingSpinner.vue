<template>
  <div :class="['loading-spinner-container', sizeClass, { 'loading-spinner-overlay': overlay }]">
    <div :class="['loading-spinner', spinnerSizeClass]">
      <div class="spinner-circle"></div>
      <div class="spinner-circle"></div>
      <div class="spinner-circle"></div>
      <div class="spinner-circle"></div>
    </div>
    <p v-if="text" :class="['loading-text', textSizeClass]">
      {{ text }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  },
  text: {
    type: String,
    default: ''
  },
  overlay: {
    type: Boolean,
    default: false
  }
})

const sizeClass = computed(() => {
  const sizes = {
    small: 'loading-spinner-small',
    medium: 'loading-spinner-medium',
    large: 'loading-spinner-large'
  }
  return sizes[props.size]
})

const spinnerSizeClass = computed(() => {
  const sizes = {
    small: 'w-4 h-4',
    medium: 'w-8 h-8',
    large: 'w-12 h-12'
  }
  return sizes[props.size]
})

const textSizeClass = computed(() => {
  const sizes = {
    small: 'text-xs',
    medium: 'text-sm',
    large: 'text-base'
  }
  return sizes[props.size]
})
</script>

<style scoped>
.loading-spinner-container {
  @apply flex flex-col items-center justify-center;
}

.loading-spinner-overlay {
  @apply fixed inset-0 bg-white bg-opacity-90 z-50;
}

.loading-spinner {
  @apply relative inline-block;
}

.spinner-circle {
  @apply absolute border-2 border-solid rounded-full;
  animation: spinner 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  border-color: transparent;
}

.loading-spinner-small .spinner-circle {
  @apply border;
  width: 16px;
  height: 16px;
}

.loading-spinner-medium .spinner-circle {
  @apply border-2;
  width: 32px;
  height: 32px;
}

.loading-spinner-large .spinner-circle {
  @apply border-[3px];
  width: 48px;
  height: 48px;
}

.spinner-circle:nth-child(1) {
  animation-delay: -0.45s;
  border-top-color: #10b981;
}

.spinner-circle:nth-child(2) {
  animation-delay: -0.3s;
  border-top-color: #059669;
}

.spinner-circle:nth-child(3) {
  animation-delay: -0.15s;
  border-top-color: #047857;
}

.spinner-circle:nth-child(4) {
  border-top-color: #6ee7b7;
}

@keyframes spinner {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.loading-text {
  @apply mt-3 text-gray-600 font-medium;
}
</style>
