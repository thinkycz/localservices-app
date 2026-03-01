<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 pb-16">
      
      <!-- Dark Gradient Header -->
      <div class="bg-gradient-to-b from-gray-900 via-blue-900 to-indigo-900 pt-16 pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 class="text-4xl font-extrabold text-white tracking-tight">Admin Dashboard</h1>
          <p class="mt-3 text-lg text-blue-200 max-w-2xl font-medium">Platform overview, metrics, and real-time analytics.</p>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20">
        
        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
          <!-- Total Users -->
          <div class="bg-white rounded-2xl shadow-xl ring-1 ring-gray-900/5 p-6 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
            <div class="flex items-center">
              <div class="p-4 rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-600/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Users</p>
                <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ metrics.total_users }}</p>
              </div>
            </div>
          </div>

          <!-- Services -->
          <div class="bg-white rounded-2xl shadow-xl ring-1 ring-gray-900/5 p-6 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
            <div class="flex items-center">
              <div class="p-4 rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-600/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Services</p>
                <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ metrics.total_services }}</p>
              </div>
            </div>
          </div>

          <!-- Bookings -->
          <div class="bg-white rounded-2xl shadow-xl ring-1 ring-gray-900/5 p-6 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
            <div class="flex items-center">
              <div class="p-4 rounded-xl bg-purple-50 text-purple-600 ring-1 ring-purple-600/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Bookings</p>
                <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ metrics.total_bookings }}</p>
              </div>
            </div>
          </div>

          <!-- Revenue -->
          <div class="bg-white rounded-2xl shadow-xl ring-1 ring-gray-900/5 p-6 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
            <div class="flex items-center">
              <div class="p-4 rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-600/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Revenue</p>
                <p class="text-3xl font-extrabold text-gray-900 mt-1">${{ formatNumber(metrics.total_revenue) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
          <!-- Revenue Chart -->
          <div class="bg-white rounded-2xl shadow-xl ring-1 ring-gray-900/5 p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Revenue (Last 30 Days)</h3>
            <div class="h-72 flex items-end space-x-3">
              <div
                v-for="(value, index) in revenueData.data"
                :key="index"
                class="flex-1 bg-gradient-to-t from-blue-600 to-indigo-500 rounded-t-xl hover:from-blue-500 hover:to-indigo-400 transition-all duration-300 relative group cursor-pointer"
                :style="{ height: `${Math.max((value / maxRevenue) * 100, 5)}%` }"
              >
                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-3 px-3 py-1.5 bg-gray-900 text-white text-xs font-semibold rounded-lg opacity-0 group-hover:opacity-100 transition-opacity shadow-lg whitespace-nowrap">
                  ${{ value }}
                </div>
              </div>
            </div>
            <div class="flex justify-between mt-4 text-sm font-medium text-gray-500">
              <span>{{ revenueData.labels[0] }}</span>
              <span>{{ revenueData.labels[revenueData.labels.length - 1] }}</span>
            </div>
          </div>

          <!-- User Growth Chart -->
          <div class="bg-white rounded-2xl shadow-xl ring-1 ring-gray-900/5 p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6">User Growth</h3>
            <div class="h-72 flex items-end space-x-3">
              <div
                v-for="(value, index) in userGrowthData.data"
                :key="index"
                class="flex-1 bg-gradient-to-t from-emerald-500 to-teal-400 rounded-t-xl hover:from-emerald-400 hover:to-teal-300 transition-all duration-300 relative group cursor-pointer"
                :style="{ height: `${Math.max((value / maxUsers) * 100, 5)}%` }"
              >
                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-3 px-3 py-1.5 bg-gray-900 text-white text-xs font-semibold rounded-lg opacity-0 group-hover:opacity-100 transition-opacity shadow-lg whitespace-nowrap">
                  {{ value }}
                </div>
              </div>
            </div>
            <div class="flex justify-between mt-4 text-sm font-medium text-gray-500">
              <span>{{ userGrowthData.labels[0] }}</span>
              <span>{{ userGrowthData.labels[userGrowthData.labels.length - 1] }}</span>
            </div>
          </div>
        </div>

        <!-- Platform Health -->
        <div class="bg-white rounded-2xl shadow-xl ring-1 ring-gray-900/5 p-8 mb-10 overflow-hidden relative">
          <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full opacity-50 blur-3xl pointer-events-none"></div>
          <h3 class="text-xl font-bold text-gray-900 mb-8 relative z-10">Platform Health</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-8 relative z-10">
            <div class="flex flex-col items-center justify-center p-6 bg-gray-50 rounded-2xl ring-1 ring-gray-900/5 transition-transform hover:-translate-y-1">
              <p class="text-4xl font-extrabold text-emerald-600">{{ platformHealth.completion_rate }}%</p>
              <p class="text-sm font-medium text-gray-500 mt-2">Completion Rate</p>
            </div>
            <div class="flex flex-col items-center justify-center p-6 bg-gray-50 rounded-2xl ring-1 ring-gray-900/5 transition-transform hover:-translate-y-1">
              <p class="text-4xl font-extrabold text-blue-600">{{ platformHealth.satisfaction_rate }}%</p>
              <p class="text-sm font-medium text-gray-500 mt-2">Satisfaction</p>
            </div>
            <div class="flex flex-col items-center justify-center p-6 bg-gray-50 rounded-2xl ring-1 ring-gray-900/5 transition-transform hover:-translate-y-1">
              <p class="text-4xl font-extrabold text-indigo-600">{{ platformHealth.avg_response_time }}</p>
              <p class="text-sm font-medium text-gray-500 mt-2">Avg Response</p>
            </div>
            <div class="flex flex-col items-center justify-center p-6 bg-gray-50 rounded-2xl ring-1 ring-gray-900/5 transition-transform hover:-translate-y-1">
              <p class="text-4xl font-extrabold text-rose-600">{{ platformHealth.dispute_rate }}%</p>
              <p class="text-sm font-medium text-gray-500 mt-2">Dispute Rate</p>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Recent Bookings -->
          <div class="bg-white rounded-2xl shadow-xl ring-1 ring-gray-900/5 p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Recent Bookings</h3>
            <div class="space-y-4">
              <div
                v-for="booking in recentBookings"
                :key="booking.id"
                class="flex items-center justify-between p-4 bg-gray-50/80 rounded-xl hover:bg-gray-100 transition-colors"
              >
                <div>
                  <p class="font-bold text-gray-900">{{ booking.service.name }}</p>
                  <p class="text-sm text-gray-500 mt-0.5 font-medium">{{ booking.customer.name }} • {{ booking.booking_date }}</p>
                </div>
                <span
                  class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full shadow-sm"
                  :class="getStatusClass(booking.status)"
                >
                  {{ booking.status }}
                </span>
              </div>
            </div>
          </div>

          <!-- Recent Reviews -->
          <div class="bg-white rounded-2xl shadow-xl ring-1 ring-gray-900/5 p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Recent Reviews</h3>
            <div class="space-y-4">
              <div
                v-for="review in recentReviews"
                :key="review.id"
                class="p-4 bg-gray-50/80 rounded-xl hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center justify-between mb-2">
                  <p class="font-bold text-gray-900">{{ review.user.name }}</p>
                  <div class="flex text-amber-400 drop-shadow-sm">
                    <span v-for="i in 5" :key="i" class="text-sm">
                      <svg v-if="i <= review.rating" class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                      <svg v-else class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                    </span>
                  </div>
                </div>
                <p class="text-sm text-gray-700 line-clamp-2 leading-relaxed">{{ review.comment }}</p>
                <p class="text-xs text-gray-500 mt-2 font-medium">{{ review.service.name }}</p>
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
    pending: 'bg-yellow-100 text-yellow-800 ring-1 ring-yellow-600/20',
    confirmed: 'bg-blue-100 text-blue-800 ring-1 ring-blue-600/20',
    completed: 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-600/20',
    cancelled: 'bg-red-100 text-red-800 ring-1 ring-red-600/20',
  };
  return classes[status] || 'bg-gray-100 text-gray-800 ring-1 ring-gray-600/20';
};
</script>
