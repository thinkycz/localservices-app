<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900 px-6 py-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <h1 class="text-2xl font-bold text-white">{{ $t('Business Information') }}</h1>
                <p class="text-blue-200 mt-1 text-sm">{{ $t('Step 1 of 3: Let\'s start with the basics') }}</p>
              </div>
              <div class="flex items-center space-x-2 shrink-0">
                <div class="w-8 h-2 rounded-full bg-white shadow-sm"></div>
                <div class="w-2 h-2 rounded-full bg-white/30"></div>
                <div class="w-2 h-2 rounded-full bg-white/30"></div>
              </div>
            </div>
          </div>

          <form @submit.prevent="submit" class="p-6 md:p-8">
            <div class="space-y-6">
              <div>
                <label for="business_name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $t('Business Name') }}</label>
                <input
                  id="business_name"
                  v-model="form.business_name"
                  type="text"
                  required
                  class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                  :placeholder="$t('e.g., Smith\'s Plumbing Services')"
                />
                <p v-if="form.errors.business_name" class="mt-1.5 text-xs text-red-500">{{ form.errors.business_name }}</p>
              </div>

              <div>
                <label for="business_phone" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $t('Business Phone') }}</label>
                <input
                  id="business_phone"
                  v-model="form.business_phone"
                  type="tel"
                  required
                  class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                  placeholder="(555) 123-4567"
                />
                <p v-if="form.errors.business_phone" class="mt-1.5 text-xs text-red-500">{{ form.errors.business_phone }}</p>
              </div>

              <div>
                <label for="business_email" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $t('Business Email') }}</label>
                <input
                  id="business_email"
                  v-model="form.business_email"
                  type="email"
                  required
                  class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                  :placeholder="$t('contact@yourbusiness.com')"
                />
                <p v-if="form.errors.business_email" class="mt-1.5 text-xs text-red-500">{{ form.errors.business_email }}</p>
              </div>
            </div>

            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
              <Link
                :href="route('vendor.onboarding.index')"
                class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-xl transition-colors"
              >{{ $t('Cancel') }}</Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-sm transition-all text-sm"
              >{{ $t('Continue to Step 2') }}<svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

const form = useForm({
  business_name: '',
  business_phone: '',
  business_email: '',
});

const submit = () => {
  form.post(route('vendor.onboarding.step1.store'));
};
</script>
