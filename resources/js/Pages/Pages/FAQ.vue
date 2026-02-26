<template>
  <AppLayout>
    <Head title="FAQ" />
    
    <div class="bg-gray-50 min-h-screen py-12">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h1 class="text-3xl font-bold text-gray-900">{{ title }}</h1>
          <p class="mt-4 text-lg text-gray-600">
            Find answers to commonly asked questions
          </p>
        </div>

        <!-- Category Tabs -->
        <div class="flex flex-wrap justify-center gap-2 mb-8">
          <button
            v-for="category in faqs"
            :key="category.category"
            @click="activeCategory = category.category"
            class="px-4 py-2 rounded-full text-sm font-medium transition"
            :class="activeCategory === category.category ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
          >
            {{ category.category }}
          </button>
        </div>

        <!-- FAQ Accordion -->
        <div class="space-y-4">
          <div
            v-for="(faq, index) in activeFAQs"
            :key="index"
            class="bg-white rounded-lg shadow-sm overflow-hidden"
          >
            <button
              @click="toggleFAQ(index)"
              class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition"
            >
              <span class="font-medium text-gray-900">{{ faq.question }}</span>
              <svg
                class="w-5 h-5 text-gray-500 transition-transform"
                :class="{ 'rotate-180': openFAQ === index }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div
              v-show="openFAQ === index"
              class="px-6 pb-4 text-gray-600 leading-relaxed"
            >
              {{ faq.answer }}
            </div>
          </div>
        </div>

        <!-- Still Need Help -->
        <div class="mt-12 bg-indigo-50 rounded-lg p-8 text-center">
          <h2 class="text-xl font-semibold text-indigo-900 mb-2">Still have questions?</h2>
          <p class="text-indigo-700 mb-4">
            Can't find the answer you're looking for? Please contact our support team.
          </p>
          <Link
            href="/contact"
            class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition"
          >
            Contact Support
          </Link>
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
