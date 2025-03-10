<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { Chart, registerables } from 'chart.js';
import 'chartjs-adapter-date-fns';
import axios from 'axios';
import dayjs from 'dayjs';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

Chart.register(...registerables);

// State Variables
const chartCanvas = ref(null);
const chartInstance = ref(null);
const chartData = ref({ labels: [], datasets: [] });
const isLoading = ref(false);
const selectedType = ref('Breach'); // Default to Breach
const selectedFilter = ref('Last 30 Days'); // Default filter
const showCustomRange = ref(false);
const customDateRange = ref({ from: null, to: null });

const filters = [
  'Today',
  'Yesterday',
  'Last 7 Days',
  'Last 30 Days',
  'This Month',
  'Last Month',
  'Custom Range',
];

// ✅ Debounce setup
let fetchTimeout = null;

// ✅ Debounced API Request with 1-Second Delay
const fetchLineData = () => {
  clearTimeout(fetchTimeout); // Cancel previous request

  fetchTimeout = setTimeout(async () => {
    if (
      selectedFilter.value === 'Custom Range' &&
      (!customDateRange.value.from || !customDateRange.value.to)
    ) {
      return; // Prevent API call if dates are not selected
    }

    isLoading.value = true;

    try {
      await new Promise((resolve) => setTimeout(resolve, 1000)); // ✅ 1-second delay before request

      const params = {
        type_of_report: selectedType.value,
        date_filter: selectedFilter.value,
      };

      if (
        selectedFilter.value === 'Custom Range' &&
        customDateRange.value.from &&
        customDateRange.value.to
      ) {
        params.start_date = dayjs(customDateRange.value.from).format(
          'YYYY-MM-DD'
        );
        params.end_date = dayjs(customDateRange.value.to).format('YYYY-MM-DD');
      }

      const response = await axios.get('/api/line-data', { params });

      chartData.value = {
        labels: response.data.labels,
        datasets: [
          {
            label: `${selectedType.value} Reports (${selectedFilter.value})`,
            data: response.data.data,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true,
            tension: 0.3,
          },
        ],
      };

      await nextTick();
      renderChart();
    } catch (error) {
      console.error('Error fetching line chart data:', error);
    } finally {
      isLoading.value = false;
    }
  }, 500); // ✅ 500ms debounce before starting the delay
};

// ✅ Watch for filter change and trigger API request
const handleFilterChange = () => {
  showCustomRange.value = selectedFilter.value === 'Custom Range';
  fetchLineData();
};

// ✅ Watch for date range selection
const handleDateChange = (newRange) => {
  if (newRange && newRange[0] && newRange[1]) {
    customDateRange.value = { from: newRange[0], to: newRange[1] };
    fetchLineData();
  }
};

// Watch `selectedFilter` to trigger `fetchLineData()`
watch(selectedFilter, (newFilter) => {
  if (newFilter !== 'Custom Range') {
    fetchLineData();
  }
});

// Render Chart
const renderChart = () => {
  if (!chartCanvas.value) return;

  if (chartInstance.value) {
    chartInstance.value.destroy();
  }

  chartInstance.value = new Chart(chartCanvas.value, {
    type: 'line',
    data: chartData.value,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          type: 'time',
          time: {
            unit: 'day',
            tooltipFormat: 'MMM dd',
            displayFormats: {
              day: 'MMM dd',
            },
          },
          title: { display: true, text: 'Date' },
          ticks: {
            autoSkip: true,
            maxTicksLimit: 10,
          },
        },
        y: {
          title: { display: true, text: 'Number of Reports' },
          beginAtZero: true,
        },
      },
      plugins: {
        legend: { display: true, position: 'top' },
      },
    },
  });
};

// Lifecycle Hooks
onMounted(fetchLineData);
onUnmounted(() => {
  if (chartInstance.value) {
    chartInstance.value.destroy();
    chartInstance.value = null;
  }
});
</script>

<template>
  <div
    class="p-6 bg-white dark:bg-gray-800 shadow-lg rounded-2xl w-full relative"
  >
    <h2
      class="text-2xl font-semibold text-gray-900 dark:text-gray-200 text-center mb-6"
    >
      {{ selectedType }} Reports ({{ selectedFilter }})
    </h2>

    <!-- ✅ Filter Selection -->
    <div class="flex justify-center mb-4 gap-2">
      <select
        v-model="selectedType"
        @change="fetchLineData"
        class="px-4 py-2 border rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600"
      >
        <option
          v-for="type in [
            'Breach',
            'Data Leak',
            'Malware Information',
            'Threat Actors Updates',
            'Cyber Awareness',
            'Vulnerability Exploitation',
            'Phishing',
            'Ransomware',
            'Social Engineering',
            'Illegal Access',
          ]"
          :key="type"
          :value="type"
        >
          {{ type }}
        </option>
      </select>

      <select
        v-model="selectedFilter"
        @change="handleFilterChange"
        class="px-4 py-2 mx-4 border rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600"
      >
        <option v-for="filter in filters" :key="filter" :value="filter">
          {{ filter }}
        </option>
      </select>
    </div>

    <!-- ✅ Custom Date Range Picker -->
    <div v-if="showCustomRange" class="flex justify-center mb-4 gap-4">
      <VueDatePicker
        v-model="customDateRange"
        range
        @update:modelValue="handleDateChange"
        class="px-4 py-2 border rounded-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-white"
      />
    </div>

    <!-- ✅ Chart Container -->
    <div class="relative w-full min-h-[400px] flex items-center justify-center">
      <!-- Loading Spinner -->
      <div
        v-if="isLoading"
        class="absolute inset-0 flex justify-center items-center bg-white bg-opacity-75 dark:bg-gray-800 dark:bg-opacity-75 z-10"
      >
        <div
          class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"
        ></div>
      </div>

      <!-- No Data Available Message -->
      <div
        v-if="!isLoading && (!chartData || chartData.labels.length === 0)"
        class="absolute inset-0 flex items-center justify-center text-gray-500 dark:text-gray-400 text-lg"
      >
        No data available.
      </div>

      <canvas ref="chartCanvas" class="w-full h-full"></canvas>
    </div>
  </div>
</template>
