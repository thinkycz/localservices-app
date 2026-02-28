<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900 px-6 py-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <h1 class="text-2xl font-bold text-white">Service Offerings</h1>
                <p class="text-blue-200 mt-1 text-sm">Step 3 of 3: Define your pricing and duration</p>
              </div>
              <div class="flex items-center space-x-2 shrink-0">
                <Link :href="route('vendor.onboarding.step2')" class="w-3 h-3 rounded-full bg-white/40 hover:bg-white/60 transition-colors" title="Back to Step 2"></Link>
                <Link :href="route('vendor.onboarding.step1')" class="w-3 h-3 rounded-full bg-white/40 hover:bg-white/60 transition-colors" title="Back to Step 1"></Link>
                <div class="w-8 h-2 rounded-full bg-white shadow-sm"></div>
              </div>
            </div>
          </div>

          <form @submit.prevent="submit" class="p-6 md:p-8">
            <div class="mb-8">
              <p class="text-gray-500 text-sm mb-6">Add at least one service offering. You can always add more later from your dashboard.</p>
              
              <div class="space-y-6">
                <div v-for="(offering, index) in form.offerings" :key="index" class="bg-white border border-gray-200 rounded-2xl p-6 relative shadow-sm hover:shadow-md transition-shadow">
                  <button
                    v-if="form.offerings.length > 1"
                    type="button"
                    @click="removeOffering(index)"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 p-2 rounded-xl hover:bg-red-50 transition-colors"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>

                  <h4 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">#{{ index + 1 }}</span>
                    Offering Details
                  </h4>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                      <label :for="'name-' + index" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Service Name</label>
                      <input
                        :id="'name-' + index"
                        v-model="offering.name"
                        type="text"
                        required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                        placeholder="e.g., Basic Plumbing Repair"
                      />
                      <p v-if="form.errors['offerings.' + index + '.name']" class="mt-1.5 text-xs text-red-500">{{ form.errors['offerings.' + index + '.name'] }}</p>
                    </div>

                    <div class="md:col-span-2">
                      <label :for="'description-' + index" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Description</label>
                      <textarea
                        :id="'description-' + index"
                        v-model="offering.description"
                        rows="2"
                        required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200 resize-none"
                        placeholder="Brief description of what's included..."
                      ></textarea>
                      <p v-if="form.errors['offerings.' + index + '.description']" class="mt-1.5 text-xs text-red-500">{{ form.errors['offerings.' + index + '.description'] }}</p>
                    </div>

                    <div>
                      <label :for="'price-' + index" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Price ($)</label>
                      <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                          <span class="text-gray-500">$</span>
                        </div>
                        <input
                          :id="'price-' + index"
                          v-model="offering.price"
                          type="number"
                          min="0"
                          step="0.01"
                          required
                          class="w-full pl-8 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                          placeholder="99.99"
                        />
                      </div>
                      <p v-if="form.errors['offerings.' + index + '.price']" class="mt-1.5 text-xs text-red-500">{{ form.errors['offerings.' + index + '.price'] }}</p>
                    </div>

                    <div>
                      <label :for="'duration-' + index" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Duration <span class="text-gray-400 font-normal lowercase">(minutes)</span></label>
                      <div class="relative">
                        <select
                          :id="'duration-' + index"
                          v-model="offering.duration_minutes"
                          required
                          class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                        >
                          <option value="" disabled>Select duration</option>
                          <option value="30">30 minutes</option>
                          <option value="45">45 minutes</option>
                          <option value="60">1 hour</option>
                          <option value="90">1.5 hours</option>
                          <option value="120">2 hours</option>
                          <option value="180">3 hours</option>
                          <option value="240">4 hours</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                      </div>
                      <p v-if="form.errors['offerings.' + index + '.duration_minutes']" class="mt-1.5 text-xs text-red-500">{{ form.errors['offerings.' + index + '.duration_minutes'] }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <button
                type="button"
                @click="addOffering"
                class="w-full mt-6 py-4 border-2 border-dashed border-blue-200 bg-blue-50/50 rounded-2xl text-blue-600 font-semibold hover:bg-blue-50 hover:border-blue-300 transition-all flex items-center justify-center gap-2 group"
              >
                <svg class="w-5 h-5 text-blue-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Another Offering
              </button>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-gray-100">
              <Link
                :href="route('vendor.onboarding.step2')"
                class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-xl transition-colors"
              >
                Back
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-sm transition-all text-sm"
              >
                Complete Setup
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
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
