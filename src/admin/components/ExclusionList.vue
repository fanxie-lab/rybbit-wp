<template>
  <div class="exclusion-list">
    <!-- Empty state -->
    <div v-if="!items.length" class="exclusion-list-empty">
      <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
      <p class="text-gray-500 text-sm">{{ emptyMessage }}</p>
    </div>

    <!-- Items list -->
    <transition-group v-else name="list" tag="ul" class="exclusion-list-items">
      <li
        v-for="(item, index) in items"
        :key="getItemKey(item, index)"
        class="exclusion-list-item"
      >
        <div class="exclusion-list-item-content">
          <!-- Pattern type (string) -->
          <template v-if="typeof item === 'string'">
            <code class="exclusion-list-pattern">{{ item }}</code>
          </template>

          <!-- Post/Page type (object) -->
          <template v-else-if="item.type === 'post'">
            <div class="exclusion-list-post">
              <span class="exclusion-list-post-title">{{ item.title || `Post #${item.post_id}` }}</span>
              <span class="exclusion-list-post-type" :class="getPostTypeClass(item.post_type)">
                {{ formatPostType(item.post_type) }}
              </span>
            </div>
            <span v-if="item.url" class="exclusion-list-post-url">{{ item.url }}</span>
          </template>
        </div>

        <button
          type="button"
          class="exclusion-list-delete"
          :aria-label="`Remove ${typeof item === 'string' ? item : item.title}`"
          @click="removeItem(index)"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </li>
    </transition-group>

    <!-- Item count -->
    <p v-if="items.length" class="exclusion-list-count">
      {{ items.length }} {{ items.length === 1 ? itemLabel : itemLabelPlural }}
    </p>
  </div>
</template>

<script setup>
const props = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  emptyMessage: {
    type: String,
    default: 'No items added yet'
  },
  itemLabel: {
    type: String,
    default: 'item'
  },
  itemLabelPlural: {
    type: String,
    default: 'items'
  }
})

const emit = defineEmits(['remove'])

/**
 * Get unique key for item
 */
function getItemKey(item, index) {
  if (typeof item === 'string') {
    return `pattern-${item}-${index}`
  }
  return `post-${item.post_id || index}`
}

/**
 * Remove item at index
 */
function removeItem(index) {
  emit('remove', index)
}

/**
 * Format post type for display
 */
function formatPostType(postType) {
  const types = {
    post: 'Post',
    page: 'Page',
    product: 'Product',
    attachment: 'Media'
  }
  return types[postType] || postType.charAt(0).toUpperCase() + postType.slice(1)
}

/**
 * Get CSS class for post type badge
 */
function getPostTypeClass(postType) {
  const classes = {
    post: 'post-type-post',
    page: 'post-type-page',
    product: 'post-type-product'
  }
  return classes[postType] || 'post-type-default'
}
</script>

<style scoped>
.exclusion-list {
  @apply space-y-3;
}

.exclusion-list-empty {
  @apply flex flex-col items-center justify-center py-8 text-center;
  @apply border-2 border-dashed border-gray-200 rounded-lg;
}

.exclusion-list-items {
  @apply space-y-2;
}

.exclusion-list-item {
  @apply flex items-start justify-between gap-3 px-3 py-2.5;
  @apply bg-gray-50 border border-gray-200 rounded-md;
  @apply transition-colors hover:bg-gray-100;
}

.exclusion-list-item-content {
  @apply flex-1 min-w-0;
}

.exclusion-list-pattern {
  @apply text-sm text-gray-800 bg-gray-200 px-2 py-0.5 rounded;
  font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
}

.exclusion-list-post {
  @apply flex items-center gap-2 flex-wrap;
}

.exclusion-list-post-title {
  @apply text-sm font-medium text-gray-800;
}

.exclusion-list-post-type {
  @apply text-xs px-2 py-0.5 rounded-full font-medium;
}

.post-type-post {
  @apply bg-blue-100 text-blue-700;
}

.post-type-page {
  @apply bg-purple-100 text-purple-700;
}

.post-type-product {
  @apply bg-amber-100 text-amber-700;
}

.post-type-default {
  @apply bg-gray-100 text-gray-700;
}

.exclusion-list-post-url {
  @apply block text-xs text-gray-500 mt-0.5 truncate;
}

.exclusion-list-delete {
  @apply p-1.5 text-gray-400 hover:text-red-600 rounded;
  @apply focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1;
  @apply transition-colors flex-shrink-0;
}

.exclusion-list-count {
  @apply text-xs text-gray-500 text-right;
}

/* List transitions */
.list-enter-active,
.list-leave-active {
  transition: all 0.2s ease;
}

.list-enter-from {
  opacity: 0;
  transform: translateX(-10px);
}

.list-leave-to {
  opacity: 0;
  transform: translateX(10px);
}

.list-move {
  transition: transform 0.2s ease;
}
</style>
