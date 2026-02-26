<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6">
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-2xl font-bold text-white">Notifications</h1>
                <p class="text-blue-100 mt-1">Stay updated on your bookings and activities</p>
              </div>
              <button
                v-if="notifications.data.some(n => !n.read_at)"
                @click="markAllAsRead"
                class="px-4 py-2 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition-colors"
              >
                Mark all as read
              </button>
            </div>
          </div>

          <div class="divide-y divide-gray-200">
            <div
              v-for="notification in notifications.data"
              :key="notification.id"
              class="p-6 hover:bg-gray-50 transition-colors"
              :class="{ 'bg-blue-50': !notification.read_at }"
            >
              <div class="flex items-start space-x-4">
                <div
                  class="w-3 h-3 rounded-full mt-2 flex-shrink-0"
                  :class="getTypeColor(notification.type)"
                ></div>
                
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">
                      {{ notification.title }}
                    </h3>
                    <span class="text-sm text-gray-500">
                      {{ formatDate(notification.created_at) }}
                    </span>
                  </div>
                  
                  <p class="text-gray-600 mt-1">
                    {{ notification.message }}
                  </p>
                  
                  <div class="flex items-center mt-4 space-x-4">
                    <Link
                      v-if="notification.action_url"
                      :href="notification.action_url"
                      class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                    >
                      View Details →
                    </Link>
                    
                    <button
                      v-if="!notification.read_at"
                      @click="markAsRead(notification)"
                      class="text-gray-500 hover:text-gray-700 text-sm"
                    >
                      Mark as read
                    </button>
                    
                    <button
                      @click="deleteNotification(notification)"
                      class="text-red-500 hover:text-red-700 text-sm"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="notifications.links" class="px-6 py-4 border-t border-gray-200">
            <AppPagination :links="notifications.links" />
          </div>

          <!-- Empty State -->
          <div v-if="notifications.data.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications yet</h3>
            <p class="text-gray-500">You'll see notifications about your bookings, payments, and reviews here.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppPagination from '@/Components/AppPagination.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  notifications: Object,
});

const getTypeColor = (type) => {
  const colors = {
    booking: 'bg-blue-500',
    payment: 'bg-green-500',
    review: 'bg-yellow-500',
    reminder: 'bg-purple-500',
    system: 'bg-gray-500',
  };
  return colors[type] || 'bg-gray-500';
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const markAsRead = async (notification) => {
  try {
    await fetch(`/api/notifications/${notification.id}/read`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
    });
    notification.read_at = new Date().toISOString();
  } catch (error) {
    console.error('Failed to mark as read:', error);
  }
};

const markAllAsRead = async () => {
  try {
    await fetch('/api/notifications/mark-all-read', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
    });
    props.notifications.data.forEach(n => n.read_at = new Date().toISOString());
  } catch (error) {
    console.error('Failed to mark all as read:', error);
  }
};

const deleteNotification = async (notification) => {
  if (!confirm('Are you sure you want to delete this notification?')) return;
  
  try {
    await fetch(`/api/notifications/${notification.id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
    });
    const index = props.notifications.data.findIndex(n => n.id === notification.id);
    if (index > -1) {
      props.notifications.data.splice(index, 1);
    }
  } catch (error) {
    console.error('Failed to delete notification:', error);
  }
};
</script>
