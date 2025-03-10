<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Access the href prop correctly
const props = defineProps({
  href: {
    type: String,
    default: null, // Allow it to be optional for history-based navigation
  },
});

// Go back to the previous page if no href is provided
const goBack = () => {
  if (!props.href) {
    window.history.back();
  }
};
</script>

<template>
  <div>
    <template v-if="props.href">
      <!-- Use Inertia link if href is provided -->
      <Link
        :href="props.href"
        class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition ease-in-out hover:bg-gray-700 duration-300 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300"
      >
        <slot />
      </Link>
    </template>
    <template v-else>
      <!-- Use history.back() if no href is provided -->
      <button
        @click="goBack"
        class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition ease-in-out hover:bg-gray-700 duration-300 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300"
      >
        <slot />
      </button>
    </template>
  </div>
</template>
