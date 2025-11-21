<template>
  <div class="help-tooltip-container">
    <button
      type="button"
      class="help-tooltip-trigger"
      :aria-label="ariaLabel"
      @mouseenter="show"
      @mouseleave="hide"
      @focus="show"
      @blur="hide"
    >
      <svg
        class="w-4 h-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
        />
      </svg>
    </button>
    <transition name="tooltip">
      <div
        v-if="visible"
        :class="['help-tooltip-content', `help-tooltip-${position}`]"
        role="tooltip"
      >
        <div class="help-tooltip-arrow"></div>
        <div class="help-tooltip-text">
          {{ text }}
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  text: {
    type: String,
    required: true
  },
  position: {
    type: String,
    default: 'top',
    validator: (value) => ['top', 'bottom', 'left', 'right'].includes(value)
  },
  ariaLabel: {
    type: String,
    default: 'Help information'
  }
})

const visible = ref(false)

function show() {
  visible.value = true
}

function hide() {
  visible.value = false
}
</script>

<style scoped>
.help-tooltip-container {
  @apply relative inline-block;
}

.help-tooltip-trigger {
  @apply inline-flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors;
  @apply focus:outline-none focus:ring-2 focus:ring-rybbit-primary focus:ring-offset-1 rounded-full;
}

.help-tooltip-content {
  @apply absolute z-50 px-3 py-2 text-sm text-white bg-gray-900 rounded-md shadow-lg;
  width: 280px;
  max-width: 280px;
  pointer-events: none;
}

.help-tooltip-top {
  @apply bottom-full left-1/2 -translate-x-1/2 mb-2;
}

.help-tooltip-bottom {
  @apply top-full left-1/2 -translate-x-1/2 mt-2;
}

.help-tooltip-left {
  @apply right-full top-1/2 -translate-y-1/2 mr-2;
}

.help-tooltip-right {
  @apply left-full top-1/2 -translate-y-1/2 ml-2;
}

.help-tooltip-arrow {
  @apply absolute w-2 h-2 bg-gray-900 transform rotate-45;
}

.help-tooltip-top .help-tooltip-arrow {
  @apply bottom-[-4px] left-1/2 -translate-x-1/2;
}

.help-tooltip-bottom .help-tooltip-arrow {
  @apply top-[-4px] left-1/2 -translate-x-1/2;
}

.help-tooltip-left .help-tooltip-arrow {
  @apply right-[-4px] top-1/2 -translate-y-1/2;
}

.help-tooltip-right .help-tooltip-arrow {
  @apply left-[-4px] top-1/2 -translate-y-1/2;
}

.help-tooltip-text {
  @apply relative z-10;
}

/* Tooltip transition */
.tooltip-enter-active,
.tooltip-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.tooltip-enter-from,
.tooltip-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
