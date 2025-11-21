<template>
  <div class="status-indicator">
    <div :class="['status-badge', `status-badge-${status}`]">
      <span class="status-dot" aria-hidden="true"></span>
      <span class="status-text">{{ statusText }}</span>
    </div>
    <p v-if="message" class="status-message">
      {{ message }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
    validator: (value) => ['connected', 'disconnected', 'checking', 'error'].includes(value)
  },
  message: {
    type: String,
    default: ''
  },
  customText: {
    type: String,
    default: ''
  }
})

const statusText = computed(() => {
  if (props.customText) return props.customText

  const statusMap = {
    connected: 'Connected',
    disconnected: 'Not Connected',
    checking: 'Checking...',
    error: 'Connection Error'
  }

  return statusMap[props.status] || props.status
})
</script>

<style scoped>
.status-indicator {
  @apply space-y-2;
}

.status-badge {
  @apply inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium;
}

.status-badge-connected {
  @apply bg-green-100 text-green-800;
}

.status-badge-disconnected {
  @apply bg-gray-100 text-gray-600;
}

.status-badge-checking {
  @apply bg-blue-100 text-blue-800;
}

.status-badge-error {
  @apply bg-red-100 text-red-800;
}

.status-dot {
  @apply inline-block h-2 w-2 rounded-full;
}

.status-badge-connected .status-dot {
  @apply bg-green-500 animate-pulse;
}

.status-badge-disconnected .status-dot {
  @apply bg-gray-400;
}

.status-badge-checking .status-dot {
  @apply bg-blue-500 animate-pulse;
}

.status-badge-error .status-dot {
  @apply bg-red-500;
}

.status-text {
  @apply font-medium;
}

.status-message {
  @apply text-sm text-gray-600 mt-1;
}
</style>
