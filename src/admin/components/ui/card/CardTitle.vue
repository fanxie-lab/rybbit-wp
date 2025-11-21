<script setup>
import { cn } from '@/admin/lib/utils'

defineProps({
  class: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <h3 :class="cn('font-semibold leading-none tracking-tight flex items-center gap-2', $props.class)">
    <slot />
  </h3>
</template>
