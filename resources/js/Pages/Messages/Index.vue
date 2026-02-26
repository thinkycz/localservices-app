<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6">
            <h1 class="text-2xl font-bold text-white">Messages</h1>
            <p class="text-blue-100 mt-1">Communicate with service providers and customers</p>
          </div>

          <div class="divide-y divide-gray-200">
            <div
              v-for="conversation in conversations.data"
              :key="conversation.id"
              @click="openConversation(conversation.id)"
              class="p-6 hover:bg-gray-50 cursor-pointer transition-colors"
              :class="{ 'bg-blue-50': conversation.unread_count > 0 }"
            >
              <div class="flex items-start space-x-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                  <span class="text-blue-600 font-semibold text-lg">
                    {{ getInitials(getOtherUser(conversation).name) }}
                  </span>
                </div>
                
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">
                      {{ getOtherUser(conversation).name }}
                    </h3>
                    <span class="text-sm text-gray-500">
                      {{ formatTime(conversation.last_message_at) }}
                    </span>
                  </div>
                  
                  <p class="text-sm text-gray-600 mt-1" v-if="conversation.service">
                    Re: {{ conversation.service.name }}
                  </p>
                  
                  <p class="text-gray-700 mt-2 line-clamp-2">
                    {{ conversation.messages[0]?.content || 'No messages yet' }}
                  </p>
                  
                  <div v-if="conversation.unread_count > 0" class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ conversation.unread_count }} new
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="conversations.links" class="px-6 py-4 border-t border-gray-200">
            <AppPagination :links="conversations.links" />
          </div>

          <!-- Empty State -->
          <div v-if="conversations.data.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No conversations yet</h3>
            <p class="text-gray-500">Start messaging by booking a service or contacting a provider.</p>
            <Link
              :href="route('services.index')"
              class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Browse Services
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppPagination from '@/Components/AppPagination.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  conversations: Object,
});

const getOtherUser = (conversation) => {
  const currentUserId = document.querySelector('meta[name="user-id"]')?.content;
  return conversation.customer_id == currentUserId ? conversation.provider : conversation.customer;
};

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);

  if (diff < 60) return 'Just now';
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
  return date.toLocaleDateString();
};

const openConversation = (id) => {
  router.visit(route('messages.show', id));
};
</script>
