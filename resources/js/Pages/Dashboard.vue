<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import StatsCard from '@/Components/StatsCard.vue';
import Layout from '@/Layouts/Layout.vue';
import ChartCard from '@/Components/ChartCard.vue';
import LineChartCard from '@/Components/LineChartCard.vue';
import axios from 'axios';
import dayjs from 'dayjs';

const statsData = ref([]);
const loading = ref(true);

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/stats');
    if (response.data.status === 'success') {
      statsData.value = response.data.stats;
    }
  } catch (error) {
    console.error('Failed to load stats:', error);
  } finally {
    loading.value = false;
  }
};

// Fetch Data Function
const fetchChartData = async (from, to) => {
  // ✅ Ensure valid `from` and `to` before logging a warning
  if (
    !from ||
    !to ||
    isNaN(new Date(from).getTime()) ||
    isNaN(new Date(to).getTime())
  ) {
    from = dayjs().subtract(30, 'day').format('YYYY-MM-DD');
    to = dayjs().format('YYYY-MM-DD');
  } else {
    from = dayjs(from).format('YYYY-MM-DD');
    to = dayjs(to).format('YYYY-MM-DD');
  }

  try {
    // ✅ Fetch Data Securely
    const response = await axios.get('/api/reports', {
      params: { from, to },
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    // ✅ Handle Unexpected API Response
    if (!response.data || response.data.status !== 'success') {
      return { labels: [], datasets: [{ data: [] }] };
    }

    return {
      labels: response.data.labels || [],
      datasets: [{ data: response.data.data || [] }],
    };
  } catch (error) {
    return { labels: [], datasets: [{ data: [] }] };
  }
};

onMounted(() => {
  fetchStats();
});
</script>

<template>
  <Head title="Dashboard" />
  <Layout>
    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
      >
        Dashboard
      </h2>
    </template>
    <StatsCard :stats="statsData" />
    <div class="py-12">
      <div
        class="mx-auto max-w-7xl px-6 lg:px-8 py-6 bg-white dark:bg-gray-700 rounded-lg shadow-md"
      >
        <h2
          class="flex justify-center pb-6 text-3xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
        >
          Type of Report
        </h2>

        <!-- ✅ Adjusted Grid Layout -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Pie Chart -->
          <ChartCard
            title=""
            :fetchData="fetchChartData"
            chartType="pie"
            class="p-6 bg-white dark:bg-gray-700 rounded-lg shadow-md"
          />

          <!-- Horizontal Bar Chart -->
          <ChartCard
            title=""
            :fetchData="fetchChartData"
            chartType="bar"
            class="p-6 bg-white dark:bg-gray-700 rounded-lg shadow-md"
          />

          <!-- Line Chart (Spanning Two Columns) -->
          <div class="col-span-1 md:col-span-2">
            <LineChartCard />
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>
