<script setup>
import { TooltipRoot, TooltipTrigger, TooltipContent, TooltipPortal, TooltipProvider } from 'radix-vue'
import { cn } from '@/admin/lib/utils'

defineProps({
  content: {
    type: String,
    required: true,
  },
  side: {
    type: String,
    default: 'top',
  },
  class: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <TooltipProvider>
    <TooltipRoot :delay-duration="200">
      <TooltipTrigger as-child>
        <slot />
      </TooltipTrigger>
      <TooltipPortal>
        <TooltipContent
          :side="side"
          :side-offset="5"
          :class="cn(
            'z-50 overflow-hidden rounded-md bg-primary px-3 py-1.5 text-xs text-primary-foreground animate-in fade-in-0 zoom-in-95 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
            $props.class
          )"
        >
          {{ content }}
        </TooltipContent>
      </TooltipPortal>
    </TooltipRoot>
  </TooltipProvider>
</template>
