<script setup>
defineProps({
  headers: {
    type: Array,
    required: true,
    default: () => [],
  },
  rows: {
    type: Array,
    required: true,
    default: () => [],
  },
  page: {
    type: Number,
    required: true,
    default: 1,
  },
  pageSize: {
    type: Number,
    required: true,
    default: 10,
  },
});
</script>

<template>
  <div
    class="border border-gray-600 rounded-md shadow-md bg-gray-100 dark:bg-gray-800 overflow-auto"
  >
    <table class="w-full table-fixed border-collapse border-spacing-0">
      <!-- Table Header -->
      <thead class="bg-gray-200 dark:bg-gray-700">
        <tr>
          <th
            class="p-3 text-left font-semibold text-gray-800 dark:text-gray-200"
          ></th>
          <th
            v-for="header in headers"
            :key="header"
            class="p-3 text-left font-semibold text-gray-800 dark:text-gray-200"
          >
            {{ header }}
          </th>
        </tr>
      </thead>

      <!-- Table Body -->
      <tbody>
        <tr
          v-for="(row, index) in rows"
          :key="index"
          class="hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-300"
        >
          <!-- Dynamic Row Number -->
          <th
            class="p-3 border-b border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300"
          >
            {{ (page - 1) * pageSize + index + 1 }}
          </th>

          <!-- Render text or component with proper styling -->
          <td
            v-for="(cell, colIndex) in row"
            :key="colIndex"
            class="p-3 border-b border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 max-w-0 truncate break-words"
          >
            <component
              v-if="cell.type === 'component'"
              :is="cell.component"
              v-bind="cell.props"
            >
              {{ cell.slot }}
            </component>
            <span
              v-else
              class="block overflow-hidden text-ellipsis whitespace-normal break-words"
            >
              {{ cell }}
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.table-fixed {
  width: 100%;
  table-layout: fixed; /* Prevents columns from expanding */
}

td,
th {
  word-wrap: break-word; /* Ensures text wraps properly */
  white-space: normal; /* Allows multiple lines */
}

.max-w-0 {
  max-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
