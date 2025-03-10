<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';
import Datepicker from '@vuepic/vue-datepicker';
import Dropdown from '@/Components/Dropdown.vue';
import '@vuepic/vue-datepicker/dist/main.css';
import dayjs from 'dayjs';
import ChartDataLabels from 'chartjs-plugin-datalabels';

Chart.register(...registerables, ChartDataLabels);

// Props
const props = defineProps({
  title: String,
  chartType: { type: String, default: 'bar' },
  fetchData: Function,
});

// State Variables
const selectedFilter = ref('Custom Range');
const showDatepickers = ref(true);
const chartCanvas = ref(null);
const chartInstance = ref(null);
const isLoading = ref(false);
const chartData = ref({ labels: [], datasets: [] });
const filters = ref({
  from: dayjs().subtract(30, 'day').format('YYYY-MM-DD'), // ✅ Default to last 30 days
  to: dayjs().format('YYYY-MM-DD'),
});
const dropdownRef = ref(null); // ✅ Reference to the Dropdown component

let chartTimeout = null; // ✅ Stores timeout reference

// **✅ Apply Predefined Filters & Close Dropdown**
const applyFilter = (filter) => {
  selectedFilter.value = filter;

  if (dropdownRef.value) {
    dropdownRef.value.closeDropdown();
  }

  let now = dayjs();
  let from = null,
    to = null;

  switch (filter) {
    case 'Today':
      from = to = now.format('YYYY-MM-DD');
      break;
    case 'Yesterday':
      from = to = now.subtract(1, 'day').format('YYYY-MM-DD');
      break;
    case 'Last 7 Days':
      from = now.subtract(6, 'day').format('YYYY-MM-DD');
      to = now.format('YYYY-MM-DD');
      break;
    case 'Last 30 Days':
      from = now.subtract(29, 'day').format('YYYY-MM-DD');
      to = now.format('YYYY-MM-DD');
      break;
    case 'This Month':
      from = now.startOf('month').format('YYYY-MM-DD');
      to = now.endOf('month').format('YYYY-MM-DD');
      break;
    case 'Last Month':
      from = now.subtract(1, 'month').startOf('month').format('YYYY-MM-DD');
      to = now.subtract(1, 'month').endOf('month').format('YYYY-MM-DD');
      break;
    default:
      from = to = null;
  }

  filters.value = { from, to };
  showDatepickers.value = filter === 'Custom Range';
  fetchChartData(from, to);
};

// **✅ Fetch Chart Data with Debounce**
const fetchChartData = async () => {
  isLoading.value = true;
  const { from, to } = filters.value;

  if (chartTimeout) {
    clearTimeout(chartTimeout);
    chartTimeout = null;
  }

  const data = await props.fetchData(from, to);
  chartData.value = data;

  await nextTick(); // Ensure DOM updates before rendering

  chartTimeout = setTimeout(() => {
    isLoading.value = false;
    renderChart();
  }, 1000);
};

// Generate Color

const generateUniqueColors = (count) => {
  const colors = [];
  for (let i = 0; i < count; i++) {
    const hue = (i * 360) / count;
    colors.push(`hsl(${hue}, 50%, 55%)`); // ✅ Softer but vibrant
  }
  return colors;
};

// **✅ Ensure Canvas Exists Before Rendering**
// **✅ Ensure `chartData.value.datasets[0]` Exists Before Using It**
const renderChart = () => {
  if (chartInstance.value) {
    chartInstance.value.destroy();
  }

  chartInstance.value = new Chart(chartCanvas.value, {
    type: props.chartType,
    data: {
      labels: chartData.value.labels || [],
      datasets: [
        {
          data: chartData.value.datasets[0]?.data || [], // ✅ Prevents `undefined` error
          backgroundColor: generateUniqueColors(chartData.value.labels.length),
          borderColor: generateUniqueColors(chartData.value.labels.length).map(
            (color) => color.replace('70%', '40%')
          ),
          borderWidth: 2,
        },
      ],
    },
    options:
      props.chartType === 'pie'
        ? {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true,
                position: 'bottom',
                labels: {
                  boxWidth: 12,
                  padding: 20,
                  font: {
                    size: 12,
                  },
                },
              },
              tooltip: {
                callbacks: {
                  label: (tooltipItem) => {
                    let dataset = tooltipItem.dataset.data;
                    let total = dataset.reduce((acc, val) => acc + val, 0);
                    let value = dataset[tooltipItem.dataIndex];
                    let percentage = ((value / total) * 100).toFixed(1);
                    return `${tooltipItem.label}: ${percentage}%`;
                  },
                },
              },
              datalabels: {
                color: '#fff',
                font: { size: 14, weight: 'bold' },
                formatter: (value, context) => {
                  let total = context.dataset.data.reduce(
                    (acc, val) => acc + val,
                    0
                  );
                  let percentage = ((value / total) * 100).toFixed(1);
                  return `${percentage}%`;
                },
              },
            },
          }
        : {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
              x: {
                beginAtZero: true,
                ticks: {
                  stepSize: 5,
                },
              },
            },
            plugins: {
              legend: {
                display: false,
              },
            },
          },
  });
};

// **✅ Handle Custom Date Selection**
const updateDate = () => {
  fetchChartData();
};

// **Destroy Chart on Unmount**
onUnmounted(() => {
  if (chartInstance.value) {
    chartInstance.value.destroy();
    chartInstance.value = null;
  }

  if (chartTimeout) {
    clearTimeout(chartTimeout);
    chartTimeout = null;
  }
});

onMounted(() => {
  if (filters.value.from && filters.value.to) {
    fetchChartData(filters.value.from, filters.value.to);
  }
});
</script>

<template>
  <div class="p-6 bg-white dark:bg-gray-800 shadow-lg rounded-2xl w-full">
    <h2
      class="text-2xl font-semibold text-gray-900 dark:text-gray-200 text-center mb-6"
    >
      {{ title }}
    </h2>

    <!-- ✅ Replace Filter Buttons with Dropdown -->
    <div class="flex justify-center mb-4">
      <Dropdown ref="dropdownRef">
        <template #trigger>
          <button
            @click="dropdownOpen = !dropdownOpen"
            class="px-4 py-2 text-black dark:text-white border border-black/50 dark:border-white/50 rounded-lg bg-transparent hover:bg-white/10 backdrop-blur-md transition duration-300 font-semibold"
          >
            {{ selectedFilter }}
          </button>
        </template>

        <template #content>
          <div>
            <button
              v-for="filter in [
                'Today',
                'Yesterday',
                'Last 7 Days',
                'Last 30 Days',
                'This Month',
                'Last Month',
                'Custom Range',
              ]"
              :key="filter"
              @click="applyFilter(filter)"
              class="block px-4 py-2 text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600 w-full text-left"
            >
              {{ filter }}
            </button>
          </div>
        </template>
      </Dropdown>
    </div>

    <!-- ✅ Custom Date Pickers -->
    <div
      v-if="showDatepickers"
      class="flex flex-col md:flex-row justify-center gap-4 items-center mb-4"
    >
      <Datepicker
        v-model="filters.from"
        @update:modelValue="updateDate"
        placeholder="From"
        format="yyyy-MM-dd"
      />
      <span class="text-gray-500 dark:text-gray-300">to</span>
      <Datepicker
        v-model="filters.to"
        @update:modelValue="updateDate"
        placeholder="To"
        format="yyyy-MM-dd"
      />
    </div>

    <!-- ✅ Chart Display -->
    <div class="relative w-full min-h-[400px] flex items-center justify-center">
      <div
        v-if="isLoading"
        class="absolute inset-0 flex justify-center items-center bg-white bg-opacity-75 dark:bg-gray-800 dark:bg-opacity-75 z-10"
      >
        <div
          class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"
        ></div>
      </div>

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

<style>
/* Dark Mode Fix for Vue Datepicker */
.custom-datepicker {
  background-color: white;
  color: black;
  border: 1px solid #ccc;
  padding: 0.5rem;
  border-radius: 5px;
}

/* Dark Mode Support */
.dark .custom-datepicker {
  background-color: #1f2937;
  color: white;
  border: 1px solid #374151;
}

/* Datepicker Popup Styling */
.dp__theme_dark {
  --dp-background-color: #1f2937 !important;
  --dp-text-color: white !important;
  --dp-border-color: #374151 !important;
}

/* Ensure input text is readable */
.dp__input {
  background-color: transparent !important;
  color: black !important;
}

/* Dark mode text color */
.dark .dp__input {
  color: white !important;
}
</style>
