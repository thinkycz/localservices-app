<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900 px-6 py-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <h1 class="text-2xl font-bold text-white">{{ $t('Service Details') }}</h1>
                <p class="text-blue-200 mt-1 text-sm">{{ $t('Step 2 of 3: What do you offer?') }}</p>
              </div>
              <div class="flex items-center space-x-2 shrink-0">
                <Link :href="route('vendor.onboarding.step1')" class="w-3 h-3 rounded-full bg-white/40 hover:bg-white/60 transition-colors" :title="$t('Back to Step 1')"></Link>
                <div class="w-8 h-2 rounded-full bg-white shadow-sm"></div>
                <div class="w-2 h-2 rounded-full bg-white/30"></div>
              </div>
            </div>
          </div>

          <form @submit.prevent="submit" class="p-6 md:p-8">
            <div class="space-y-6">
              <div>
                <label for="category_id" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $t('Service Category') }}</label>
                <div class="relative">
                  <select
                    id="category_id"
                    v-model="form.category_id"
                    required
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                  >
                    <option value="" disabled>{{ $t('Select a category') }}</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                  </div>
                </div>
                <p v-if="form.errors.category_id" class="mt-1.5 text-xs text-red-500">{{ form.errors.category_id }}</p>
              </div>

              <div>
                <label for="service_name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $t('Service Name') }}</label>
                <input
                  id="service_name"
                  v-model="form.service_name"
                  type="text"
                  required
                  class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                  :placeholder="$t('e.g., Professional Plumbing Repairs')"
                />
                <p v-if="form.errors.service_name" class="mt-1.5 text-xs text-red-500">{{ form.errors.service_name }}</p>
              </div>

              <div>
                <label for="description" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $t('Service Description') }}</label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="4"
                  required
                  class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200 resize-none"
                  :placeholder="$t('Describe your services, experience, and what makes you unique...')"
                ></textarea>
                <div class="flex items-center justify-between mt-1">
                  <p v-if="form.errors.description" class="text-xs text-red-500">{{ form.errors.description }}</p>
                  <p class="text-[10px] text-gray-400 ml-auto">{{ form.description.length }}/1000</p>
                </div>
              </div>

              <div>
                <label for="price_range" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $t('Overall Price Range') }}</label>
                <div class="relative">
                  <select
                    id="price_range"
                    v-model="form.price_range"
                    required
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                  >
                    <option value="" disabled>{{ $t('Select price range') }}</option>
                    <option value="1">{{ $t('$ - Budget Friendly') }}</option>
                    <option value="2">{{ $t('$ - Moderate') }}</option>
                    <option value="3">{{ $t('$ - Premium') }}</option>
                    <option value="4">{{ $t('$ - Luxury') }}</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                  </div>
                </div>
                <p v-if="form.errors.price_range" class="mt-1.5 text-xs text-red-500">{{ form.errors.price_range }}</p>
              </div>
            </div>

            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
              <Link
                :href="route('vendor.onboarding.step1')"
                class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-xl transition-colors"
              >{{ $t('Back') }}</Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-sm transition-all text-sm"
              >{{ $t('Continue to Step 3') }}<svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  categories: Array,
});

const form = useForm({
  category_id: '',
  service_name: '',
  description: '',
  price_range: '',
});

const submit = () => {
  form.post(route('vendor.onboarding.step2.store'));
};
</script>
