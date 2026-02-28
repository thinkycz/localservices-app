<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <AppLayout>
    <Head title="Categories" />

    <!-- Gradient Header -->
    <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-2xl font-bold text-white">Categories</h1>
        <p class="text-sm text-blue-200 mt-1">Browse and discover all available service categories</p>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="categories.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <Link
          v-for="category in categories"
          :key="category.id"
          :href="route('services.index', { categories: category.slug })"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-blue-100 transition-all group"
        >
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 group-hover:from-blue-100 group-hover:to-indigo-100 flex items-center justify-center shrink-0 transition-colors">
              <span class="text-blue-600 font-bold text-lg">
                {{ String(category.name ?? '').charAt(0).toUpperCase() }}
              </span>
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-base font-bold text-gray-900 truncate group-hover:text-blue-600 transition-colors">{{ category.name }}</p>
              <p class="text-xs text-gray-400 mt-0.5 font-medium">
                {{ category.services_count || 0 }} {{ category.services_count === 1 ? 'service' : 'services' }}
              </p>
            </div>
            <svg class="w-5 h-5 text-gray-300 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </div>
        </Link>
      </div>

      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">No categories found</h3>
        <p class="text-sm text-gray-500">There are currently no service categories available.</p>
      </div>
    </div>
  </AppLayout>
</template>
