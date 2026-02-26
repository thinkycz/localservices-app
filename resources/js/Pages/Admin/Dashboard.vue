<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
          <p class="text-gray-600 mt-1">Platform overview and analytics</p>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Users</p>
                <p class="text-2xl font-bold text-gray-900">{{ metrics.total_users }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-green-100 text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Services</p>
                <p class="text-2xl font-bold text-gray-900">{{ metrics.total_services }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Bookings</p>
                <p class="text-2xl font-bold text-gray-900">{{ metrics.total_bookings }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Revenue</p>
                <p class="text-2xl font-bold text-gray-900">${{ formatNumber(metrics.total_revenue) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Revenue Chart -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue (Last 30 Days)</h3>
            <div class="h-64 flex items-end space-x-2">
              <div
                v-for="(value, index) in revenueData.data"
                :key="index"
                class="flex-1 bg-blue-500 rounded-t hover:bg-blue-600 transition-colors relative group"
                :style="{ height: `${(value / maxRevenue) * 100}%` }"
              >
                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity">
                  ${{ value }}
                </div>
              </div>
            </div>
            <div class="flex justify-between mt-2 text-xs text-gray-500">
              <span>{{ revenueData.labels[0] }}</span>
              <span>{{ revenueData.labels[revenueData.labels.length - 1] }}</span>
            </div>
          </div>

          <!-- User Growth Chart -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">User Growth</h3>
            <div class="h-64 flex items-end space-x-2">
              <div
                v-for="(value, index) in userGrowthData.data"
                :key="index"
                class="flex-1 bg-green-500 rounded-t hover:bg-green-600 transition-colors relative group"
                :style="{ height: `${(value / maxUsers) * 100}%` }"
              >
                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity">
                  {{ value }}
                </div>
              </div>
            </div>
            <div class="flex justify-between mt-2 text-xs text-gray-500">
              <span>{{ userGrowthData.labels[0] }}</span>
              <span>{{ userGrowthData.labels[userGrowthData.labels.length - 1] }}</span>
            </div>
          </div>
        </div>

        <!-- Platform Health -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Health</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
              <p class="text-3xl font-bold text-green-600">{{ platformHealth.completion_rate }}%</p>
              <p class="text-sm text-gray-600">Completion Rate</p>
            </div>
            <div class="text-center">
              <p class="text-3xl font-bold text-blue-600">{{ platformHealth.satisfaction_rate }}%</p>
              <p class="text-sm text-gray-600">Satisfaction</p>
            </div>
            <div class="text-center">
              <p class="text-3xl font-bold text-purple-600">{{ platformHealth.avg_response_time }}</p>
              <p class="text-sm text-gray-600">Avg Response</p>
            </div>
            <div class="text-center">
              <p class="text-3xl font-bold text-red-600">{{ platformHealth.dispute_rate }}%</p>
              <p class="text-sm text-gray-600">Dispute Rate</p>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Recent Bookings -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Bookings</h3>
            <div class="space-y-4">
              <div
                v-for="booking in recentBookings"
                :key="booking.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div>
                  <p class="font-medium text-gray-900">{{ booking.service.name }}</p>
                  <p class="text-sm text-gray-600">{{ booking.customer.name }} • {{ booking.booking_date }}</p>
                </div>
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="getStatusClass(booking.status)"
                >
                  {{ booking.status }}
                </span>
              </div>
            </div>
          </div>

          <!-- Recent Reviews -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Reviews</h3>
            <div class="space-y-4">
              <div
                v-for="review in recentReviews"
                :key="review.id"
                class="p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center justify-between mb-2">
                  <p class="font-medium text-gray-900">{{ review.user.name }}</p>
                  <div class="flex text-yellow-400">
                    <span v-for="i in 5" :key="i" class="text-sm">
                      {{ i <= review.rating ? '★' : '☆' }}
                    </span>
                  </div>
                </div>
                <p class="text-sm text-gray-600 line-clamp-2">{{ review.comment }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ review.service.name }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  metrics: Object,
  recentBookings: Array,
  revenueData: Object,
  userGrowthData: Object,
  topServices: Array,
  topCategories: Array,
  recentReviews: Array,
  platformHealth: Object,
});

const maxRevenue = Math.max(...props.revenueData.data, 1);
const maxUsers = Math.max(...props.userGrowthData.data, 1);

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num);
};

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
