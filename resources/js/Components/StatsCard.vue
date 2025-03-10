<template>
  <div>
    <!-- Toggle Button -->
    <button
      @click="toggleVisibility"
      class="fixed left-2 top-16 px-3 py-1 my-2 bg-gray-800 text-white text-sm rounded-lg shadow-md z-50 transition-all duration-300 dark:bg-gray-700"
    >
      {{ isVisible ? 'Hide Stats' : 'Show Stats' }}
    </button>

    <!-- Stats Card -->
    <div
      class="fixed left-0 top-20 flex flex-col gap-4 my-9 p-4 w-64 shadow-lg rounded-xl z-50 bg-white text-gray-900 dark:bg-gray-900 dark:text-white transition-all duration-300"
      :class="
        isVisible
          ? 'opacity-100 translate-x-0'
          : 'opacity-0 -translate-x-full pointer-events-none'
      "
    >
      <div v-if="loading" class="text-center text-gray-500">Loading...</div>

      <!-- ✅ Fix: Ensure stats is defined before checking its length -->
      <div
        v-else-if="stats && stats.length === 0"
        class="text-center text-gray-500"
      >
        No statistics available.
      </div>

      <div
        v-for="(stat, index) in stats"
        :key="index"
        class="p-4 rounded-lg text-center transition-all bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-white"
      >
        <div class="text-sm font-semibold text-gray-600 dark:text-gray-400">
          {{ stat.title }}
        </div>
        <div class="text-2xl font-bold">
          {{ stat.value }}
        </div>
        <div
          v-if="stat.description"
          class="text-xs text-gray-500 dark:text-gray-400 mt-1"
        >
          <span
            :class="stat.trend === 'up' ? 'text-green-400' : 'text-red-400'"
          >
            {{ stat.trend === 'up' ? '↗︎' : '↘︎' }} {{ stat.change }}
          </span>
          {{ stat.description }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// ✅ Fix: Initialize stats as an empty array to prevent "undefined" errors
const stats = ref([]);
const loading = ref(true);
const isVisible = ref(true);

// Toggle visibility with persistence
const toggleVisibility = () => {
  isVisible.value = !isVisible.value;
  localStorage.setItem('statsVisibility', isVisible.value);
};

// Fetch stats from API
const fetchStats = async () => {
  try {
    const response = await axios.get('/api/stats');
    if (response.data.status === 'success') {
      stats.value = response.data.stats || []; // ✅ Ensure it is an array
    }
  } catch (error) {
    console.error('Failed to load stats:', error);
  } finally {
    loading.value = false;
  }
};

// Load stored visibility state and fetch stats
onMounted(() => {
  const savedState = localStorage.getItem('statsVisibility');
  isVisible.value = savedState === 'true';

  fetchStats();
});
</script>
