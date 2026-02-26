<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6">
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-2xl font-bold text-white">Service Details</h1>
                <p class="text-blue-100 mt-1">Step 2 of 3</p>
              </div>
              <div class="flex items-center space-x-1">
                <div class="w-3 h-3 rounded-full bg-blue-300"></div>
                <div class="w-3 h-3 rounded-full bg-white"></div>
                <div class="w-3 h-3 rounded-full bg-blue-300"></div>
              </div>
            </div>
          </div>

          <form @submit.prevent="submit" class="p-8">
            <div class="space-y-6">
              <div>
                <InputLabel for="category_id" value="Service Category" />
                <select
                  id="category_id"
                  v-model="form.category_id"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  required
                >
                  <option value="">Select a category</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
                <InputError :message="form.errors.category_id" class="mt-2" />
              </div>

              <div>
                <InputLabel for="service_name" value="Service Name" />
                <TextInput
                  id="service_name"
                  v-model="form.service_name"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  placeholder="e.g., Professional Plumbing Services"
                />
                <InputError :message="form.errors.service_name" class="mt-2" />
              </div>

              <div>
                <InputLabel for="description" value="Service Description" />
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="4"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  required
                  placeholder="Describe your services, experience, and what makes you unique..."
                ></textarea>
                <p class="mt-1 text-sm text-gray-500">{{ form.description.length }}/1000 characters</p>
                <InputError :message="form.errors.description" class="mt-2" />
              </div>

              <div>
                <InputLabel for="price_range" value="Price Range" />
                <select
                  id="price_range"
                  v-model="form.price_range"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  required
                >
                  <option value="">Select price range</option>
                  <option value="1">$ - Budget Friendly</option>
                  <option value="2">$$ - Moderate</option>
                  <option value="3">$$$ - Premium</option>
                  <option value="4">$$$$ - Luxury</option>
                </select>
                <InputError :message="form.errors.price_range" class="mt-2" />
              </div>
            </div>

            <div class="flex items-center justify-between mt-8">
              <Link
                :href="route('vendor.onboarding.step1')"
                class="text-gray-600 hover:text-gray-900 font-medium"
              >
                Back
              </Link>
              <PrimaryButton :disabled="form.processing">
                Continue
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </PrimaryButton>
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
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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
