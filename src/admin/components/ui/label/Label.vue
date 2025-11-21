<script setup>
import { computed } from 'vue'
import { Label as LabelPrimitive } from 'radix-vue'
import { cn } from '@/admin/lib/utils'

const props = defineProps({
  for: {
    type: String,
    default: '',
  },
  class: {
    type: String,
    default: '',
  },
})

const labelClass = computed(() =>
  cn(
    'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
    props.class
  )
)
</script>

<template>
  <LabelPrimitive :for="$props.for" :class="labelClass">
    <slot />
  </LabelPrimitive>
</template>
