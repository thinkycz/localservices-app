<template>
  <div ref="dropdownRef" class="relative">
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span
        v-if="unreadCount > 0"
        class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
    >
      <div class="p-4 border-b border-gray-200 flex items-center justify-between">
        <h3 class="font-semibold text-gray-900">{{ $t('Notifications') }}</h3>
        <button
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="text-sm text-blue-600 hover:text-blue-800"
        >{{ $t('Mark all read') }}</button>
      </div>

      <div class="max-h-96 overflow-y-auto">
        <div v-if="notifications.length === 0" class="p-4 text-center text-gray-500">{{ $t('No notifications yet') }}</div>

        <div
          v-for="notification in notifications"
          :key="notification.id"
          @click="handleNotificationClick(notification)"
          class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
          :class="{ 'bg-blue-50': !notification.read_at }"
        >
          <div class="flex items-start space-x-3">
            <div
              class="w-2 h-2 rounded-full mt-2 flex-shrink-0"
              :class="getTypeColor(notification.type)"
            ></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ notification.title }}
              </p>
              <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                {{ notification.message }}
              </p>
              <p class="text-xs text-gray-400 mt-1">
                {{ formatTime(notification.created_at) }}
              </p>
            </div>
            <button
              @click.stop="deleteNotification(notification)"
              class="text-gray-400 hover:text-red-500 flex-shrink-0"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>


    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const isOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const dropdownRef = ref(null);

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    fetchNotifications();
  }
};

const fetchNotifications = async () => {
  try {
    const response = await fetch('/api/notifications/recent', {
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
    });
    const data = await response.json();
    notifications.value = data.notifications;
    unreadCount.value = data.unread_count;
  } catch (error) {
    console.error('Failed to fetch notifications:', error);
  }
};

const markAllAsRead = async () => {
  try {
    await fetch('/api/notifications/mark-all-read', {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
    });
    notifications.value.forEach(n => n.read_at = new Date().toISOString());
    unreadCount.value = 0;
  } catch (error) {
    console.error('Failed to mark all as read:', error);
  }
};

const handleNotificationClick = async (notification) => {
  if (!notification.read_at) {
    try {
      await fetch(`/api/notifications/${notification.id}/read`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
      });
      notification.read_at = new Date().toISOString();
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch (error) {
      console.error('Failed to mark as read:', error);
    }
  }

  if (notification.action_url) {
    router.visit(notification.action_url);
  }
  isOpen.value = false;
};

const deleteNotification = async (notification) => {
  try {
    await fetch(`/api/notifications/${notification.id}`, {
      method: 'DELETE',
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
    });
    notifications.value = notifications.value.filter(n => n.id !== notification.id);
    if (!notification.read_at) {
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    }
  } catch (error) {
    console.error('Failed to delete notification:', error);
  }
};

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

const formatTime = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);

  if (diff < 60) return 'Just now';
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
  return date.toLocaleDateString();
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  // Fetch initial count
  fetchNotifications();
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
