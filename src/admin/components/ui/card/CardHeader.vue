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
  <div :class="cn('flex flex-col space-y-1.5 p-6 bg-muted/50 border-b', $props.class)">
    <slot />
  </div>
</template>
