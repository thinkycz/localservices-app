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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Categories</h1>
        <p class="text-gray-600 mt-2">Browse services by category</p>
      </div>

      <div v-if="categories.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <Link
          v-for="category in categories"
          :key="category.id"
          :href="route('services.index', { categories: category.slug })"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow transition"
        >
          <div class="flex items-center justify-between gap-4">
            <div class="min-w-0">
              <p class="text-lg font-semibold text-gray-900 truncate">{{ category.name }}</p>
              <p class="text-sm text-gray-500 mt-1">
                {{ category.services_count }} {{ category.services_count === 1 ? 'service' : 'services' }}
              </p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
              <span class="text-blue-700 font-semibold">
                {{ String(category.name ?? '').slice(0, 1).toUpperCase() }}
              </span>
            </div>
          </div>
        </Link>
      </div>

      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No categories found</h3>
        <p class="text-gray-600">Add categories in the database to get started.</p>
      </div>
    </div>
  </AppLayout>
</template>
