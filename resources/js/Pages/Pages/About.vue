<template>
  <AppLayout>
    <Head title="About Us" />
    
    <div class="bg-white">
      <!-- Hero Section -->
      <div class="bg-indigo-600 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h1 class="text-4xl font-bold text-white mb-4">{{ title }}</h1>
          <p class="text-xl text-indigo-100 max-w-3xl mx-auto">
            {{ content.mission }}
          </p>
        </div>
      </div>

      <!-- Story Section -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="max-w-3xl mx-auto text-center">
          <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Story</h2>
          <p class="text-lg text-gray-600 leading-relaxed">
            {{ content.story }}
          </p>
        </div>
      </div>

      <!-- Values Section -->
      <div class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Our Values</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div
              v-for="value in content.values"
              :key="value.title"
              class="bg-white rounded-lg p-6 shadow-sm text-center"
            >
              <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    v-if="value.icon === 'shield-check'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                  />
                  <path
                    v-else-if="value.icon === 'clock'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                  <path
                    v-else-if="value.icon === 'star'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                  />
                  <path
                    v-else-if="value.icon === 'users'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                  />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ value.title }}</h3>
              <p class="text-gray-600">{{ value.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Section -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div v-for="(value, key) in content.stats" :key="key">
            <div class="text-4xl font-bold text-indigo-600 mb-2">{{ value }}</div>
            <div class="text-gray-600 capitalize">{{ key.replace('_', ' ') }}</div>
          </div>
        </div>
      </div>

      <!-- CTA Section -->
      <div class="bg-indigo-600 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h2 class="text-3xl font-bold text-white mb-4">Ready to Get Started?</h2>
          <p class="text-xl text-indigo-100 mb-8">
            Join thousands of satisfied customers and service providers today.
          </p>
          <div class="flex justify-center gap-4">
            <Link
              href="/services"
              class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition"
            >
              Browse Services
            </Link>
            <Link
              href="/become-vendor"
              class="bg-indigo-500 text-white px-8 py-3 rounded-lg font-semibold hover:bg-indigo-400 transition"
            >
              Become a Provider
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  title: String,
  content: Object,
});
</script>
