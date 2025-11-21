<template>
  <div class="post-selector">
    <div class="post-selector-search">
      <div class="post-selector-input-wrapper">
        <svg class="post-selector-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          ref="searchInput"
          v-model="searchQuery"
          type="text"
          class="post-selector-input"
          placeholder="Search posts and pages by title..."
          aria-label="Search posts and pages"
          @input="debouncedSearch"
          @focus="showDropdown = true"
          @keydown.down.prevent="navigateDown"
          @keydown.up.prevent="navigateUp"
          @keydown.enter.prevent="selectHighlighted"
          @keydown.escape="closeDropdown"
        />
        <LoadingSpinner v-if="loading" size="small" class="post-selector-loading" />
      </div>

      <!-- Dropdown results -->
      <transition name="dropdown">
        <div
          v-if="showDropdown && (searchResults.length || noResults)"
          class="post-selector-dropdown"
          role="listbox"
          :aria-label="'Search results'"
        >
          <div v-if="noResults" class="post-selector-no-results">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>No posts found matching "{{ searchQuery }}"</span>
          </div>

          <button
            v-for="(post, index) in searchResults"
            :key="post.id"
            type="button"
            class="post-selector-item"
            :class="{ 'post-selector-item-highlighted': highlightedIndex === index }"
            role="option"
            :aria-selected="highlightedIndex === index"
            @click="selectPost(post)"
            @mouseenter="highlightedIndex = index"
          >
            <div class="post-selector-item-content">
              <span class="post-selector-item-title">{{ post.title }}</span>
              <span class="post-selector-item-type" :class="getPostTypeClass(post.post_type)">
                {{ formatPostType(post.post_type) }}
              </span>
            </div>
            <span class="post-selector-item-url">{{ post.url }}</span>
          </button>
        </div>
      </transition>
    </div>

    <!-- Selected posts hint -->
    <p v-if="excludedIds.length" class="post-selector-hint">
      {{ excludedIds.length }} {{ excludedIds.length === 1 ? 'item' : 'items' }} already excluded
    </p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import api from '../services/api'
import LoadingSpinner from './LoadingSpinner.vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  excludedIds: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue', 'select'])

const searchInput = ref(null)
const searchQuery = ref('')
const searchResults = ref([])
const loading = ref(false)
const showDropdown = ref(false)
const highlightedIndex = ref(-1)
const searchTimeout = ref(null)

const noResults = computed(() => {
  return searchQuery.value.length >= 2 && !loading.value && searchResults.value.length === 0
})

/**
 * Debounced search function
 */
function debouncedSearch() {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  if (searchQuery.value.length < 2) {
    searchResults.value = []
    return
  }

  searchTimeout.value = setTimeout(async () => {
    await performSearch()
  }, 300)
}

/**
 * Perform the API search
 */
async function performSearch() {
  if (searchQuery.value.length < 2) return

  loading.value = true
  highlightedIndex.value = -1

  try {
    const response = await api.getPosts({
      search: searchQuery.value,
      per_page: 10
    })

    // Filter out already excluded posts
    searchResults.value = (response || []).filter(
      post => !props.excludedIds.includes(post.id)
    )
  } catch (error) {
    console.error('Error searching posts:', error)
    searchResults.value = []
  } finally {
    loading.value = false
  }
}

/**
 * Select a post from the dropdown
 */
function selectPost(post) {
  const exclusion = {
    type: 'post',
    post_type: post.post_type,
    post_id: post.id,
    title: post.title,
    url: post.url
  }

  const newExclusions = [...props.modelValue, exclusion]
  emit('update:modelValue', newExclusions)
  emit('select', exclusion)

  // Clear search
  searchQuery.value = ''
  searchResults.value = []
  showDropdown.value = false
  highlightedIndex.value = -1
}

/**
 * Navigate down in dropdown
 */
function navigateDown() {
  if (searchResults.value.length === 0) return

  if (highlightedIndex.value < searchResults.value.length - 1) {
    highlightedIndex.value++
  } else {
    highlightedIndex.value = 0
  }
}

/**
 * Navigate up in dropdown
 */
function navigateUp() {
  if (searchResults.value.length === 0) return

  if (highlightedIndex.value > 0) {
    highlightedIndex.value--
  } else {
    highlightedIndex.value = searchResults.value.length - 1
  }
}

/**
 * Select highlighted item
 */
function selectHighlighted() {
  if (highlightedIndex.value >= 0 && highlightedIndex.value < searchResults.value.length) {
    selectPost(searchResults.value[highlightedIndex.value])
  }
}

/**
 * Close dropdown
 */
function closeDropdown() {
  showDropdown.value = false
  highlightedIndex.value = -1
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

/**
 * Handle click outside to close dropdown
 */
function handleClickOutside(event) {
  const selector = document.querySelector('.post-selector')
  if (selector && !selector.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
})
</script>

<style scoped>
.post-selector {
  @apply relative;
}

.post-selector-search {
  @apply relative;
}

.post-selector-input-wrapper {
  @apply relative;
}

.post-selector-search-icon {
  @apply absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400;
}

.post-selector-input {
  @apply w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-md text-sm;
  @apply focus:outline-none focus:ring-2 focus:ring-rybbit-primary focus:border-transparent;
  @apply transition-colors;
}

.post-selector-loading {
  @apply absolute right-3 top-1/2 transform -translate-y-1/2;
}

.post-selector-dropdown {
  @apply absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg;
  @apply max-h-80 overflow-y-auto;
}

.post-selector-no-results {
  @apply flex items-center gap-2 px-4 py-3 text-sm text-gray-500;
}

.post-selector-item {
  @apply w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors;
  @apply focus:outline-none focus:bg-gray-50;
  @apply border-b border-gray-100 last:border-b-0;
}

.post-selector-item-highlighted {
  @apply bg-rybbit-lighter;
}

.post-selector-item-content {
  @apply flex items-center justify-between gap-2;
}

.post-selector-item-title {
  @apply text-sm font-medium text-gray-900 truncate;
}

.post-selector-item-type {
  @apply text-xs px-2 py-0.5 rounded-full font-medium flex-shrink-0;
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

.post-selector-item-url {
  @apply block text-xs text-gray-500 mt-0.5 truncate;
}

.post-selector-hint {
  @apply mt-2 text-xs text-gray-500;
}

/* Dropdown transition */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
