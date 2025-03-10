<script setup>
const props = defineProps({
  currentPage: {
    type: Number,
    required: true,
  },
  totalPages: {
    type: Number,
    required: true,
  },
  totalItems: {
    type: Number,
    required: true,
  },
  pageSize: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(['change-page']);

// Compute the range of visible page numbers
function getVisiblePages() {
  const pages = [];
  const maxVisible = 5;
  const halfRange = Math.floor(maxVisible / 2);

  let start = Math.max(1, props.currentPage - halfRange);
  let end = Math.min(props.totalPages, props.currentPage + halfRange);

  // Adjust the range when near the beginning or end of pages
  if (props.currentPage <= halfRange) {
    end = Math.min(props.totalPages, maxVisible);
  } else if (props.currentPage + halfRange >= props.totalPages) {
    start = Math.max(1, props.totalPages - maxVisible + 1);
  }

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
}

function goToPage(page) {
  if (page >= 1 && page <= props.totalPages) {
    emit('change-page', page);
  }
}

// Compute the item range to display (e.g., 1-10, 11-20)
function getItemRange() {
  const startItem = (props.currentPage - 1) * props.pageSize + 1;
  const endItem = Math.min(
    props.totalItems,
    props.currentPage * props.pageSize
  );
  return `${startItem}-${endItem}`;
}
</script>

<template>
  <div class="flex justify-between items-center w-full py-5">
    <!-- Pagination Section -->
    <nav class="flex space-x-2">
      <!-- Previous Button -->
      <button
        :disabled="props.currentPage === 1"
        @click="goToPage(props.currentPage - 1)"
        class="px-3 py-2 border rounded disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-600 hover:text-white"
      >
        Previous
      </button>

      <!-- Page Buttons -->
      <span
        v-for="page in getVisiblePages()"
        :key="page"
        @click="goToPage(page)"
        :class="[
          'px-3 py-2 border rounded cursor-pointer transition-colors duration-200',
          props.currentPage === page
            ? 'bg-blue-600 text-white dark:bg-blue-500'
            : 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300 hover:bg-blue-500 hover:text-white',
        ]"
      >
        {{ page }}
      </span>

      <!-- Next Button -->
      <button
        :disabled="props.currentPage === props.totalPages"
        @click="goToPage(props.currentPage + 1)"
        class="px-3 py-2 border rounded disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-600 hover:text-white"
      >
        Next
      </button>
    </nav>

    <!-- Total Items Summary Section -->
    <div class="text-gray-600 dark:text-gray-300">
      {{ getItemRange() }} of {{ props.totalItems }} items
    </div>
  </div>
</template>

<style scoped>
button {
  cursor: pointer;
}
button[disabled] {
  cursor: not-allowed;
}
</style>
