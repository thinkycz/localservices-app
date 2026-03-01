<template>
  <AppLayout>
    <Head ::title="$t('title')" />
    
    <!-- Gradient Header -->
    <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16 text-center">
        <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight mb-3">{{ title }}</h1>
        <p class="text-lg text-blue-200 max-w-2xl mx-auto">{{ $t('Find answers to commonly asked questions about our services, booking process, and platform.') }}</p>
      </div>
    </div>

    <div class="bg-gray-50 min-h-screen py-10 md:py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="max-w-4xl mx-auto">
          <!-- Category Tabs -->
          <div class="flex flex-wrap justify-center gap-3 mb-10">
            <button
              v-for="category in faqs"
              :key="category.category"
              @click="activeCategory = category.category; openFAQ = null;"
              class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-200"
              :class="
                activeCategory === category.category 
                  ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md transform -translate-y-0.5' 
                  : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300 hover:text-blue-600 shadow-sm hover:shadow'
              "
            >
              {{ category.category }}
            </button>
          </div>

          <!-- FAQ Accordion -->
          <div class="space-y-4">
            <div
              v-for="(faq, index) in activeFAQs"
              :key="index"
              class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md hover:border-blue-100"
            >
              <button
                @click="toggleFAQ(index)"
                class="w-full px-6 py-5 text-left flex justify-between items-center focus:outline-none focus-visible:bg-gray-50 group"
              >
                <span class="font-bold text-gray-900 pr-6 group-hover:text-blue-600 transition-colors">{{ faq.question }}</span>
                <div 
                  class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center shrink-0 transition-colors group-hover:bg-blue-50"
                  :class="{ 'bg-blue-600 text-white group-hover:bg-blue-700': openFAQ === index }"
                >
                  <svg
                    class="w-5 h-5 transition-transform duration-300"
                    :class="[
                      openFAQ === index ? 'rotate-180 text-white' : 'text-gray-500 group-hover:text-blue-600'
                    ]"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </button>
              
              <div
                v-show="openFAQ === index"
                class="px-6 pb-6 text-gray-600 leading-relaxed text-sm sm:text-base border-t border-gray-50"
              >
                <div class="pt-4">
                  {{ faq.answer }}
                </div>
              </div>
            </div>
          </div>

          <!-- Still Need Help -->
          <div class="mt-16 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-8 md:p-10 text-center border border-blue-100/50 relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-600/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-indigo-600/5 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 mx-auto max-w-xl">
              <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
              </div>
              <h2 class="text-2xl font-bold text-gray-900 mb-3">{{ $t('Still have questions?') }}</h2>
              <p class="text-gray-600 mb-8">{{ $t('Can\'t find the answer you\'re looking for? Our support team is always here to help you out.') }}</p>
              <Link
                href="/contact"
                class="inline-flex items-center px-8 py-3.5 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition-all shadow-sm transform hover:-translate-y-0.5"
              >{{ $t('Contact Support') }}</Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  title: String,
  faqs: Array,
});

const activeCategory = ref('General');
const openFAQ = ref(null);

const activeFAQs = computed(() => {
  const category = props.faqs.find(f => f.category === activeCategory.value);
  return category ? category.questions : [];
});

const toggleFAQ = (index) => {
  openFAQ.value = openFAQ.value === index ? null : index;
};
</script>
