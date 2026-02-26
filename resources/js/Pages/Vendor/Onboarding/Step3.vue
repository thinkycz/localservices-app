<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6">
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-2xl font-bold text-white">Service Offerings</h1>
                <p class="text-blue-100 mt-1">Step 3 of 3</p>
              </div>
              <div class="flex items-center space-x-1">
                <div class="w-3 h-3 rounded-full bg-blue-300"></div>
                <div class="w-3 h-3 rounded-full bg-blue-300"></div>
                <div class="w-3 h-3 rounded-full bg-white"></div>
              </div>
            </div>
          </div>

          <form @submit.prevent="submit" class="p-8">
            <div class="mb-6">
              <p class="text-gray-600 mb-4">Add at least one service offering. You can add more later from your dashboard.</p>
              
              <div v-for="(offering, index) in form.offerings" :key="index" class="bg-gray-50 rounded-lg p-6 mb-4 relative">
                <button
                  v-if="form.offerings.length > 1"
                  type="button"
                  @click="removeOffering(index)"
                  class="absolute top-4 right-4 text-red-500 hover:text-red-700"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>

                <h4 class="font-semibold text-gray-900 mb-4">Offering #{{ index + 1 }}</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="md:col-span-2">
                    <InputLabel :for="'name-' + index" value="Service Name" />
                    <TextInput
                      :id="'name-' + index"
                      v-model="offering.name"
                      type="text"
                      class="mt-1 block w-full"
                      required
                      placeholder="e.g., Basic Plumbing Repair"
                    />
                    <InputError :message="form.errors['offerings.' + index + '.name']" class="mt-2" />
                  </div>

                  <div class="md:col-span-2">
                    <InputLabel :for="'description-' + index" value="Description" />
                    <textarea
                      :id="'description-' + index"
                      v-model="offering.description"
                      rows="2"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      required
                      placeholder="Brief description of what's included..."
                    ></textarea>
                    <InputError :message="form.errors['offerings.' + index + '.description']" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel :for="'price-' + index" value="Price ($)" />
                    <TextInput
                      :id="'price-' + index"
                      v-model="offering.price"
                      type="number"
                      min="0"
                      step="0.01"
                      class="mt-1 block w-full"
                      required
                      placeholder="99.99"
                    />
                    <InputError :message="form.errors['offerings.' + index + '.price']" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel :for="'duration-' + index" value="Duration (minutes)" />
                    <select
                      :id="'duration-' + index"
                      v-model="offering.duration_minutes"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      required
                    >
                      <option value="">Select duration</option>
                      <option value="30">30 minutes</option>
                      <option value="45">45 minutes</option>
                      <option value="60">1 hour</option>
                      <option value="90">1.5 hours</option>
                      <option value="120">2 hours</option>
                      <option value="180">3 hours</option>
                      <option value="240">4 hours</option>
                    </select>
                    <InputError :message="form.errors['offerings.' + index + '.duration_minutes']" class="mt-2" />
                  </div>
                </div>
              </div>

              <button
                type="button"
                @click="addOffering"
                class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors flex items-center justify-center"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Another Offering
              </button>
            </div>

            <div class="flex items-center justify-between mt-8">
              <Link
                :href="route('vendor.onboarding.step2')"
                class="text-gray-600 hover:text-gray-900 font-medium"
              >
                Back
              </Link>
              <PrimaryButton :disabled="form.processing" class="bg-green-600 hover:bg-green-700">
                Complete Setup
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
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
  offerings: [
    {
      name: '',
      description: '',
      price: '',
      duration_minutes: '',
    },
  ],
});

const addOffering = () => {
  form.offerings.push({
    name: '',
    description: '',
    price: '',
    duration_minutes: '',
  });
};

const removeOffering = (index) => {
  form.offerings.splice(index, 1);
};

const submit = () => {
  form.post(route('vendor.onboarding.step3.store'));
};
</script>
