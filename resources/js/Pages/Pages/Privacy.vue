<template>
  <AppLayout>
    <Head :title="title" />
    
    <!-- Gradient Header -->
    <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16 text-center">
        <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight mb-3">{{ title }}</h1>
        <p class="text-lg text-blue-200">
          Last updated: {{ lastUpdated }}
        </p>
      </div>
    </div>

    <div class="bg-gray-50 min-h-screen py-10 md:py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-12">

          <div class="space-y-10">
            <div
              v-for="section in content.sections"
              :key="section.title"
              class="prose prose-blue max-w-none"
            >
              <h2 class="text-xl font-bold text-gray-900 mb-3">{{ section.title }}</h2>
              <p class="text-gray-600 leading-relaxed">{{ section.content }}</p>
            </div>
          </div>

          <div class="mt-12 pt-8 border-t border-gray-100 text-center">
            <p class="text-sm font-medium text-gray-500 bg-gray-50 inline-block px-4 py-2 rounded-full">
              If you have any questions about this Privacy Policy, please contact us at <a href="mailto:privacy@localservices.com" class="text-blue-600 hover:text-blue-700">privacy@localservices.com</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  title: String,
  content: Object,
  lastUpdated: String,
});
</script>
