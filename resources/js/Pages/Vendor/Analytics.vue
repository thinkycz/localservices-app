<template>
  <VendorLayout>
    <Head title="Analytics Dashboard" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Track your business performance and growth</p>
          </div>
          
          <!-- Period Selector -->
          <div class="mt-4 sm:mt-0">
            <select
              v-model="selectedPeriod"
              @change="loadAnalytics"
              class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
            >
              <option value="7_days">Last 7 Days</option>
              <option value="30_days">Last 30 Days</option>
              <option value="90_days">Last 90 Days</option>
              <option value="this_month">This Month</option>
              <option value="last_month">Last Month</option>
              <option value="this_year">This Year</option>
            </select>
          </div>
        </div>

        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Revenue -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-3 rounded-md bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Revenue</p>
                <p class="text-2xl font-bold text-gray-900">${{ formatNumber(analytics.overview.revenue.current) }}</p>
                <p :class="revenueChangeClass" class="text-sm">
                  {{ analytics.overview.revenue.change > 0 ? '+' : '' }}{{ analytics.overview.revenue.change }}%
                  <span class="text-gray-500">vs last period</span>
                </p>
              </div>
            </div>
          </div>

          <!-- Bookings -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-3 rounded-md bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Bookings</p>
                <p class="text-2xl font-bold text-gray-900">{{ analytics.overview.bookings.current }}</p>
                <p :class="bookingsChangeClass" class="text-sm">
                  {{ analytics.overview.bookings.change > 0 ? '+' : '' }}{{ analytics.overview.bookings.change }}%
                  <span class="text-gray-500">vs last period</span>
                </p>
              </div>
            </div>
          </div>

          <!-- Services -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-3 rounded-md bg-purple-100">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Active Services</p>
                <p class="text-2xl font-bold text-gray-900">{{ analytics.overview.services }}</p>
              </div>
            </div>
          </div>

          <!-- Rating -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 p-3 rounded-md bg-yellow-100">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Average Rating</p>
                <p class="text-2xl font-bold text-gray-900">{{ analytics.overview.rating.toFixed(1) }}</p>
                <p class="text-sm text-gray-500">out of 5 stars</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          <!-- Revenue Chart -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Revenue Trend</h3>
            <div class="h-64">
              <canvas ref="revenueChart"></canvas>
            </div>
          </div>

          <!-- Bookings by Status -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Bookings by Status</h3>
            <div class="h-64 flex items-center justify-center">
              <canvas ref="bookingsChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Bottom Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Top Services -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Top Performing Services</h3>
            <div class="space-y-4">
              <div
                v-for="(service, index) in analytics.services"
                :key="service.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center">
                  <span class="w-8 h-8 flex items-center justify-center bg-indigo-100 text-indigo-600 rounded-full font-medium text-sm mr-3">
                    {{ index + 1 }}
                  </span>
                  <div>
                    <p class="font-medium text-gray-900">{{ service.name }}</p>
                    <p class="text-sm text-gray-500">{{ service.bookings }} bookings</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-medium text-gray-900">${{ formatNumber(service.revenue) }}</p>
                  <div class="flex items-center text-sm text-gray-500">
                    <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    {{ service.rating.toFixed(1) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Customer Stats -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Insights</h3>
            
            <div class="grid grid-cols-3 gap-4 mb-6">
              <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold text-gray-900">{{ analytics.customers.total }}</p>
                <p class="text-sm text-gray-500">Total Customers</p>
              </div>
              <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600">{{ analytics.customers.new }}</p>
                <p class="text-sm text-gray-500">New Customers</p>
              </div>
              <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">{{ analytics.customers.returning }}</p>
                <p class="text-sm text-gray-500">Returning</p>
              </div>
            </div>

            <!-- Rating Distribution -->
            <div>
              <h4 class="text-sm font-medium text-gray-700 mb-3">Rating Distribution</h4>
              <div class="space-y-2">
                <div
                  v-for="item in analytics.ratings.distribution"
                  :key="item.stars"
                  class="flex items-center"
                >
                  <span class="w-12 text-sm text-gray-600">{{ item.stars }} stars</span>
                  <div class="flex-1 mx-3">
                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                      <div
                        class="h-full bg-yellow-400 rounded-full"
                        :style="{ width: ratingPercentage(item.count) + '%' }"
                      ></div>
                    </div>
                  </div>
                  <span class="w-8 text-sm text-gray-500 text-right">{{ item.count }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </VendorLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import VendorLayout from '@/Layouts/VendorLayout.vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  analytics: Object,
});

const selectedPeriod = ref('30_days');
const revenueChart = ref(null);
const bookingsChart = ref(null);

let revenueChartInstance = null;
let bookingsChartInstance = null;

const revenueChangeClass = computed(() => ({
  'text-green-600': props.analytics.overview.revenue.change > 0,
  'text-red-600': props.analytics.overview.revenue.change < 0,
  'text-gray-600': props.analytics.overview.revenue.change === 0,
}));

const bookingsChangeClass = computed(() => ({
  'text-green-600': props.analytics.overview.bookings.change > 0,
  'text-red-600': props.analytics.overview.bookings.change < 0,
  'text-gray-600': props.analytics.overview.bookings.change === 0,
}));

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num);
};

const ratingPercentage = (count) => {
  const total = props.analytics.ratings.total;
  return total > 0 ? (count / total) * 100 : 0;
};

const initCharts = () => {
  // Revenue Chart
  if (revenueChart.value) {
    revenueChartInstance = new Chart(revenueChart.value, {
      type: 'line',
      data: {
        labels: props.analytics.revenue.labels,
        datasets: [{
          label: 'Revenue ($)',
          data: props.analytics.revenue.revenue,
          borderColor: '#4F46E5',
          backgroundColor: 'rgba(79, 70, 229, 0.1)',
          fill: true,
          tension: 0.4,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value) => '$' + value,
            },
          },
        },
      },
    });
  }

  // Bookings Chart
  if (bookingsChart.value) {
    bookingsChartInstance = new Chart(bookingsChart.value, {
      type: 'doughnut',
      data: {
        labels: props.analytics.bookings.labels,
        datasets: [{
          data: props.analytics.bookings.data,
          backgroundColor: props.analytics.bookings.colors,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
          },
        },
      },
    });
  }
};

const loadAnalytics = () => {
  router.get(route('vendor.analytics'), {
    period: selectedPeriod.value,
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['analytics'],
  });
};

onMounted(() => {
  initCharts();
});

watch(() => props.analytics, () => {
  if (revenueChartInstance) {
    revenueChartInstance.destroy();
  }
  if (bookingsChartInstance) {
    bookingsChartInstance.destroy();
  }
  initCharts();
}, { deep: true });
</script>
