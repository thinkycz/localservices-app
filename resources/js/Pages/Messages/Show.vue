<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col h-[calc(100vh-200px)]">
          <!-- Header -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <button
                @click="goBack"
                class="text-white hover:text-blue-100"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <div>
                <h1 class="text-lg font-bold text-white">{{ otherUser.name }}</h1>
                <p v-if="conversation.service" class="text-blue-100 text-sm">
                  {{ conversation.service.name }}
                </p>
              </div>
            </div>
            <div v-if="conversation.booking" class="text-right">
              <span class="text-blue-100 text-sm">Booking #{{ conversation.booking.id }}</span>
            </div>
          </div>

          <!-- Messages -->
          <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
            <div
              v-for="message in messages.data"
              :key="message.id"
              class="flex"
              :class="message.sender_id === currentUserId ? 'justify-end' : 'justify-start'"
            >
              <div
                class="max-w-[70%] rounded-lg px-4 py-2"
                :class="message.sender_id === currentUserId 
                  ? 'bg-blue-600 text-white' 
                  : 'bg-gray-100 text-gray-900'"
              >
                <p v-if="message.type === 'system'" class="text-sm italic opacity-75">
                  {{ message.content }}
                </p>
                <p v-else>{{ message.content }}</p>
                
                <div 
                  v-if="message.attachments?.length" 
                  class="mt-2 space-y-1"
                >
                  <a
                    v-for="(attachment, index) in message.attachments"
                    :key="index"
                    :href="attachment"
                    target="_blank"
                    class="text-sm underline flex items-center space-x-1"
                    :class="message.sender_id === currentUserId ? 'text-blue-100' : 'text-blue-600'"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    <span>Attachment {{ index + 1 }}</span>
                  </a>
                </div>
                
                <p 
                  class="text-xs mt-1"
                  :class="message.sender_id === currentUserId ? 'text-blue-200' : 'text-gray-500'"
                >
                  {{ formatTime(message.created_at) }}
                  <span v-if="message.sender_id === currentUserId && message.read_at" class="ml-1">
                    • Read
                  </span>
                </p>
              </div>
            </div>
          </div>

          <!-- Input -->
          <div class="border-t border-gray-200 p-4">
            <form @submit.prevent="sendMessage" class="flex space-x-4">
              <div class="flex-1">
                <input
                  v-model="newMessage"
                  type="text"
                  placeholder="Type a message..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :disabled="sending"
                />
              </div>
              <button
                type="submit"
                :disabled="!newMessage.trim() || sending"
                class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-medium shadow-md transform hover:-translate-y-0.5 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center space-x-2 transition-all duration-200"
              >
                <span v-if="sending">Sending...</span>
                <span v-else>Send</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
  conversation: Object,
  messages: Object,
});

const currentUserId = computed(() => {
  return parseInt(document.querySelector('meta[name="user-id"]')?.content || '0');
});

const otherUser = computed(() => {
  return props.conversation.customer_id === currentUserId.value 
    ? props.conversation.provider 
    : props.conversation.customer;
});

const messagesContainer = ref(null);
const newMessage = ref('');
const sending = ref(false);
const lastMessageId = ref(0);

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

const formatTime = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value) return;
  
  sending.value = true;
  
  try {
    const response = await fetch(`/api/messages/${props.conversation.id}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      body: JSON.stringify({ content: newMessage.value }),
    });
    
    const data = await response.json();
    
    if (data.success) {
      props.messages.data.push(data.message);
      newMessage.value = '';
      lastMessageId.value = data.message.id;
      scrollToBottom();
    }
  } catch (error) {
    console.error('Failed to send message:', error);
  } finally {
    sending.value = false;
  }
};

// Polling for new messages
let pollInterval = null;

const pollMessages = async () => {
  try {
    const response = await fetch(`/api/messages/${props.conversation.id}/poll?after_id=${lastMessageId.value}`, {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
    });
    
    const data = await response.json();
    
    if (data.messages?.length > 0) {
      props.messages.data.push(...data.messages);
      lastMessageId.value = Math.max(...data.messages.map(m => m.id), lastMessageId.value);
      scrollToBottom();
    }
  } catch (error) {
    console.error('Polling error:', error);
  }
};

const goBack = () => {
  router.visit(route('messages.index'));
};

onMounted(() => {
  scrollToBottom();
  if (props.messages.data.length > 0) {
    lastMessageId.value = Math.max(...props.messages.data.map(m => m.id));
  }
  
  // Poll every 3 seconds
  pollInterval = setInterval(pollMessages, 3000);
});

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval);
  }
});
</script>
