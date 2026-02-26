<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6">
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-2xl font-bold text-white">Business Information</h1>
                <p class="text-blue-100 mt-1">Step 1 of 3</p>
              </div>
              <div class="flex items-center space-x-1">
                <div class="w-3 h-3 rounded-full bg-white"></div>
                <div class="w-3 h-3 rounded-full bg-blue-300"></div>
                <div class="w-3 h-3 rounded-full bg-blue-300"></div>
              </div>
            </div>
          </div>

          <form @submit.prevent="submit" class="p-8">
            <div class="space-y-6">
              <div>
                <InputLabel for="business_name" value="Business Name" />
                <TextInput
                  id="business_name"
                  v-model="form.business_name"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  placeholder="e.g., Smith's Plumbing Services"
                />
                <InputError :message="form.errors.business_name" class="mt-2" />
              </div>

              <div>
                <InputLabel for="business_phone" value="Business Phone" />
                <TextInput
                  id="business_phone"
                  v-model="form.business_phone"
                  type="tel"
                  class="mt-1 block w-full"
                  required
                  placeholder="(555) 123-4567"
                />
                <InputError :message="form.errors.business_phone" class="mt-2" />
              </div>

              <div>
                <InputLabel for="business_email" value="Business Email" />
                <TextInput
                  id="business_email"
                  v-model="form.business_email"
                  type="email"
                  class="mt-1 block w-full"
                  required
                  placeholder="contact@yourbusiness.com"
                />
                <InputError :message="form.errors.business_email" class="mt-2" />
              </div>
            </div>

            <div class="flex items-center justify-between mt-8">
              <Link
                :href="route('vendor.onboarding.index')"
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

const form = useForm({
  business_name: '',
  business_phone: '',
  business_email: '',
});

const submit = () => {
  form.post(route('vendor.onboarding.step1.store'));
};
</script>
