<template>
  <AppLayout>
    <Head title="Terms of Service" />
    
    <div class="bg-gray-50 min-h-screen py-12">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm p-8 md:p-12">
          <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ title }}</h1>
          <p class="text-gray-500 mb-8">Last updated: {{ lastUpdated }}</p>

          <div class="space-y-8">
            <div
              v-for="section in content.sections"
              :key="section.title"
              class="prose prose-indigo max-w-none"
            >
              <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ section.title }}</h2>
              <p class="text-gray-600 leading-relaxed">{{ section.content }}</p>
            </div>
          </div>

          <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-sm text-gray-500">
              By using Local Services, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.
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
