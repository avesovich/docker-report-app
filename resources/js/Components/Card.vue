<script setup>
// Props to accept dynamic data
defineProps({
  title: {
    type: String,
    required: false,
    default: '',
  },
  actionText: {
    type: String,
    default: '', // Optional action text for buttons (if needed)
  },
});

const onAction = () => {
  alert('Action triggered!');
};
</script>

<template>
  <div
    class="p-4 border rounded-lg shadow-md bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700"
  >
    <!-- Card Header -->
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
        {{ title }}
      </h2>
      <!-- Optional action button or icon -->
      <button
        v-if="actionText"
        @click="onAction"
        class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
      >
        {{ actionText }}
      </button>
    </div>

    <!-- Card Content (default slot) -->
    <div>
      <slot />
    </div>
  </div>
</template>
