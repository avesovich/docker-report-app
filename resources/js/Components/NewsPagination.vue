<template>
  <nav class="flex justify-center space-x-2 mt-6">
    <!-- Previous Button -->
    <button
      :disabled="currentPage === 1"
      @click="changePage(currentPage - 1)"
      :class="[
        'px-3 py-1 border rounded transition duration-300',
        'disabled:opacity-50 disabled:cursor-not-allowed',
        'border-gray-300 text-gray-700 hover:bg-gray-200',
        'dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700',
      ]"
    >
      ← Prev
    </button>

    <!-- Page Numbers (Always show 5 pages) -->
    <span v-for="page in pagesToShow" :key="page">
      <button
        @click="changePage(page)"
        :class="[
          'px-3 py-1 border rounded transition duration-300',
          page === currentPage
            ? 'bg-blue-600 text-white dark:bg-blue-500 dark:text-gray-100'
            : 'border-gray-300 text-gray-700 hover:bg-gray-200 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700',
        ]"
      >
        {{ page }}
      </button>
    </span>

    <!-- Next Button -->
    <button
      :disabled="currentPage === totalPages"
      @click="changePage(currentPage + 1)"
      :class="[
        'px-3 py-1 border rounded transition duration-300',
        'disabled:opacity-50 disabled:cursor-not-allowed',
        'border-gray-300 text-gray-700 hover:bg-gray-200',
        'dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700',
      ]"
    >
      Next →
    </button>
  </nav>
</template>

<script setup>
import { computed, defineProps, defineEmits } from 'vue';

const props = defineProps({
  currentPage: Number,
  totalPages: Number,
});
const emit = defineEmits(['pageChange']);

// ✅ Compute visible pages dynamically
const pagesToShow = computed(() => {
  let pages = [];
  let startPage = Math.max(1, props.currentPage - 2);
  let endPage = Math.min(props.totalPages, startPage + 4);

  // Adjust start page if we're near the last page
  if (endPage - startPage < 4) {
    startPage = Math.max(1, endPage - 4);
  }

  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }
  return pages;
});

// ✅ Emit page change event to parent component
const changePage = (page) => {
  if (page >= 1 && page <= props.totalPages) {
    emit('pageChange', page);
  }
};
</script>
