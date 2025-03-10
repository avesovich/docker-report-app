<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import Layout from '@/Layouts/Layout.vue';
import { Head } from '@inertiajs/vue3';
import Pagination from '@/Components/NewsPagination.vue';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.tz.setDefault('Asia/Manila');

const articles = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const sources = ref([]);
const selectedSource = ref('');
const selectedSort = ref('newest');
const selectedDateRange = ref('all');
const dateFrom = ref(null);
const dateTo = ref(null);
const loading = ref(false);
const searchQuery = ref('');

const dateRanges = [
  { label: 'All Dates', value: 'all' },
  { label: 'Today', value: 'today' },
  { label: 'Yesterday', value: 'yesterday' },
  { label: 'Last 7 Days', value: 'last7days' },
  { label: 'Last 30 Days', value: 'last30days' },
  { label: 'Custom Range', value: 'custom' },
];

const fetchNews = async (page = 1) => {
  loading.value = true;

  setTimeout(async () => {
    try {
      const params = {
        page,
        source: selectedSource.value,
        sort: selectedSort.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        search: searchQuery.value,
      };

      const response = await axios.get('/news', { params });

      articles.value = response.data.articles || [];
      currentPage.value = response.data.currentPage || 1;
      totalPages.value = response.data.totalPages || 1;
      sources.value = response.data.sources || [];
    } catch (error) {
      console.error('❌ Error fetching news:', error);
    } finally {
      loading.value = false;
    }
  }, 1000);
};

onMounted(() => {
  fetchNews();
});

watch([selectedSource, selectedSort, selectedDateRange], ([newDateRange]) => {
  if (newDateRange !== 'custom') {
    setDateRange(newDateRange);
  }
  fetchNews(1);
});

watch([dateFrom, dateTo], ([newFrom, newTo]) => {
  if (selectedDateRange.value === 'custom' && newFrom && newTo) {
    fetchNews(1);
  }
});

watch(searchQuery, () => {
  fetchNews(1);
});

const setDateRange = (range) => {
  const today = dayjs().tz('Asia/Manila').format('YYYY-MM-DD');

  switch (range) {
    case 'today':
      dateFrom.value = today;
      dateTo.value = today;
      break;
    case 'yesterday':
      dateFrom.value = dayjs()
        .tz('Asia/Manila')
        .subtract(1, 'day')
        .format('YYYY-MM-DD');
      dateTo.value = dateFrom.value;
      break;
    case 'last7days':
      dateFrom.value = dayjs()
        .tz('Asia/Manila')
        .subtract(7, 'day')
        .format('YYYY-MM-DD');
      dateTo.value = today;
      break;
    case 'last30days':
      dateFrom.value = dayjs()
        .tz('Asia/Manila')
        .subtract(30, 'day')
        .format('YYYY-MM-DD');
      dateTo.value = today;
      break;
    case 'custom':
      dateFrom.value = null;
      dateTo.value = null;
      break;
    case 'all':
      dateFrom.value = '';
      dateTo.value = '';
      selectedDateRange.value = 'all';
      break;
  }

  fetchNews(1);
};

const updatePage = (page) => {
  if (page !== currentPage.value) {
    fetchNews(page);
  }
};
</script>

<template>
  <Head title="Cybersecurity News" />

  <Layout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          Cybersecurity News
        </h2>
      </div>
    </template>

    <div v-if="loading" class="loading-overlay">
      <span class="spinner"></span>
    </div>

    <div class="max-w-6xl mx-auto p-6">
      <div class="flex flex-wrap gap-4 mb-6 items-center">
        <div class="flex gap-4 flex-wrap flex-grow">
          <select
            v-model="selectedSource"
            class="border pl-4 py-3 rounded-md transition duration-300 bg-white text-gray-900 border-gray-300 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 focus:ring focus:ring-blue-400 dark:focus:ring-blue-600"
          >
            <option value="">All Sources</option>
            <option v-for="source in sources" :key="source" :value="source">
              {{ source }}
            </option>
          </select>

          <!-- ✅ Sorting Filter -->
          <select
            v-model="selectedSort"
            class="border pl-4 py-3 rounded-md transition duration-300 bg-white text-gray-900 border-gray-300 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 focus:ring focus:ring-blue-400 dark:focus:ring-blue-600"
          >
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
          </select>

          <!-- ✅ Date Filter -->
          <select
            v-model="selectedDateRange"
            @change="setDateRange(selectedDateRange)"
            class="border pl-4 py-3 rounded-md transition duration-300 bg-white text-gray-900 border-gray-300 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 focus:ring focus:ring-blue-400 dark:focus:ring-blue-600"
          >
            <option
              v-for="range in dateRanges"
              :key="range.value"
              :value="range.value"
            >
              {{ range.label }}
            </option>
          </select>

          <!-- ✅ Custom Date Range -->
          <div v-if="selectedDateRange === 'custom'" class="flex gap-2">
            <input
              type="date"
              v-model="dateFrom"
              class="border px-4 py-3 rounded-md transition duration-300 bg-white text-gray-900 border-gray-300 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 focus:ring focus:ring-blue-400 dark:focus:ring-blue-600"
            />
            <input
              type="date"
              v-model="dateTo"
              class="border px-4 py-3 rounded-md transition duration-300 bg-white text-gray-900 border-gray-300 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 focus:ring focus:ring-blue-400 dark:focus:ring-blue-600"
            />
          </div>
        </div>

        <!-- ✅ Search Input (Aligned to Right) -->
        <div class="ml-auto">
          <input
            v-model="searchQuery"
            placeholder="Search articles..."
            class="border px-4 py-3 rounded-md transition duration-300 bg-white text-gray-900 border-gray-300 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 focus:ring focus:ring-blue-400 dark:focus:ring-blue-600"
          />
        </div>
      </div>

      <!-- ✅ Main Content (Only shows when not loading) -->
      <div>
        <!-- ✅ News Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="article in articles"
            :key="article.link"
            class="p-6 bg-white dark:bg-gray-900 shadow-md rounded-lg border border-gray-300 dark:border-gray-700 hover:shadow-xl hover:scale-105 transition-transform duration-300"
          >
            <span
              class="block text-xs font-bold uppercase px-3 py-1 bg-gray-200 text-gray-900 dark:bg-gray-800 dark:text-gray-200 rounded-md mb-2"
            >
              {{ article.source }}
            </span>

            <img
              :src="article.image || '/images/fallback-image.png'"
              alt="Article Image"
              class="w-full h-40 object-cover rounded-md"
            />

            <h2 class="text-lg font-semibold mt-3">
              <a
                href="article.link"
                target="_blank"
                class="text-blue-600 hover:underline dark:text-blue-400"
              >
                {{ article.title }}
              </a>
            </h2>

            <p
              class="text-gray-700 dark:text-gray-300 text-sm mt-2 line-clamp-3"
              v-html="article.description"
            ></p>

            <div class="flex items-center text-gray-500 text-xs space-x-4 mt-3">
              <div class="flex items-center space-x-1">
                <span>👤</span>
                <span>{{ article.author || 'Unknown Author' }}</span>
              </div>
              <div class="flex items-center space-x-1">
                <span>📅</span>
                <span>{{
                  new Date(article.pubDate).toLocaleDateString()
                }}</span>
              </div>
            </div>

            <div class="mt-4">
              <a
                :href="article.link"
                target="_blank"
                class="block w-full text-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition dark:bg-blue-500 dark:hover:bg-blue-400"
              >
                Read Full Article →
              </a>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="articles.length === 0 && !loading"
        class="text-center text-gray-600 dark:text-gray-400 text-lg mt-6"
      >
        No articles found.
      </div>

      <!-- ✅ Pagination -->
      <div v-if="totalPages > 1" class="mt-6 flex justify-center">
        <Pagination
          :current-page="currentPage"
          :total-pages="totalPages"
          @pageChange="updatePage"
        />
      </div>
    </div>
  </Layout>
</template>

<style scoped>
input[type='date']::-webkit-calendar-picker-indicator {
  transition: filter 0.3s ease;
}

.dark input[type='date']::-webkit-calendar-picker-indicator {
  filter: invert(1);
}

.loading-overlay {
  position: fixed;
  top: 65px;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 50;
  pointer-events: none;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid transparent;
  border-top: 4px solid #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
