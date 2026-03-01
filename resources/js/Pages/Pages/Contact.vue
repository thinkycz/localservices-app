<template>
  <AppLayout>
    <Head ::title="$t('title')" />
    
    <!-- Gradient Header -->
    <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16 text-center">
        <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight mb-3">{{ title }}</h1>
        <p class="text-lg text-blue-200 max-w-2xl mx-auto">{{ $t('Have a question or need help? We\'re here to assist you with anything you need.') }}</p>
      </div>
    </div>

    <div class="bg-gray-50 min-h-screen py-10 md:py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
          
          <!-- Contact Form -->
          <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-10 relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -top-32 -right-32 w-64 h-64 bg-blue-600/5 rounded-full blur-3xl text-sm"></div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-8 relative z-10">{{ $t('Send us a message') }}</h2>
            
            <!-- Success Message -->
            <div v-if="$page.props.flash?.success" class="mb-8 p-5 bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-100 rounded-2xl flex items-start gap-4 transition-all relative z-10">
              <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              </div>
              <p class="text-emerald-800 font-medium text-sm pt-1.5">{{ $page.props.flash.success }}</p>
            </div>

            <form @submit.prevent="submit" class="relative z-10">
              <div class="space-y-6">
                <!-- Name & Email Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label for="name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $t('Your Name') }}</label>
                    <input
                      id="name"
                      v-model="form.name"
                      type="text"
                      class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                      required
                    />
                    <InputError :message="form.errors.name" class="mt-1.5 text-xs text-red-500" />
                  </div>

                  <div>
                    <label for="email" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $t('Email Address') }}</label>
                    <input
                      id="email"
                      v-model="form.email"
                      type="email"
                      class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                      required
                    />
                    <InputError :message="form.errors.email" class="mt-1.5 text-xs text-red-500" />
                  </div>
                </div>

                <!-- Type -->
                <div>
                  <label for="type" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $t('Inquiry Type') }}</label>
                  <div class="relative">
                    <select
                      id="type"
                      v-model="form.type"
                      class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                      required
                    >
                      <option value="general">{{ $t('General Inquiry') }}</option>
                      <option value="support">{{ $t('Customer Support') }}</option>
                      <option value="partnership">{{ $t('Business Partnership') }}</option>
                      <option value="feedback">{{ $t('Feedback') }}</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                  </div>
                  <InputError :message="form.errors.type" class="mt-1.5 text-xs text-red-500" />
                </div>

                <!-- Subject -->
                <div>
                  <label for="subject" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $t('Subject') }}</label>
                  <input
                    id="subject"
                    v-model="form.subject"
                    type="text"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200"
                    required
                  />
                  <InputError :message="form.errors.subject" class="mt-1.5 text-xs text-red-500" />
                </div>

                <!-- Message -->
                <div>
                  <label for="message" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $t('Message') }}</label>
                  <textarea
                    id="message"
                    v-model="form.message"
                    rows="5"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200 resize-none"
                    required
                    minlength="10"
                  ></textarea>
                  <InputError :message="form.errors.message" class="mt-1.5 text-xs text-red-500" />
                </div>

                <!-- Submit -->
                <div class="pt-4">
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex items-center justify-center py-3.5 px-4 rounded-xl text-white font-bold bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md transform transition-all duration-200 hover:-translate-y-0.5 disabled:opacity-50 disabled:transform-none"
                  >
                    {{ form.processing ? 'Sending...' : 'Send Message' }}
                    <svg v-if="!form.processing" class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                  </button>
                </div>
              </div>
            </form>
          </div>

          <!-- Contact Sidebar -->
          <div class="space-y-6">
            
            <!-- Contact Info -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 hover:shadow-md transition-shadow">
              <h2 class="text-xl font-bold text-gray-900 mb-8">{{ $t('Contact Information') }}</h2>
              
              <div class="space-y-6">
                <!-- Email -->
                <div class="flex items-start group cursor-pointer">
                  <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center shrink-0 group-hover:from-blue-100 group-hover:to-indigo-100 transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div class="ml-4 pt-1">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">{{ $t('Email') }}</p>
                    <a href="mailto:support@localservices.com" class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors">{{ $t('support@localservices.com') }}</a>
                  </div>
                </div>

                <!-- Phone -->
                <div class="flex items-start group cursor-pointer">
                  <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center shrink-0 group-hover:from-blue-100 group-hover:to-indigo-100 transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                  </div>
                  <div class="ml-4 pt-1">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">{{ $t('Phone') }}</p>
                    <a href="tel:1-800-LOCAL-SRV" class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors">{{ $t('1-800-LOCAL-SRV') }}</a>
                  </div>
                </div>

                <!-- Address -->
                <div class="flex items-start group cursor-pointer">
                  <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center shrink-0 group-hover:from-blue-100 group-hover:to-indigo-100 transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </div>
                  <div class="ml-4 pt-1">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">{{ $t('Office') }}</p>
                    <p class="text-sm font-medium text-gray-900 leading-relaxed">{{ $t('123 Business Street') }}<br>{{ $t('San Francisco, CA 94102') }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Business Hours & FAQ -->
            <div class="bg-gradient-to-br from-gray-900 to-blue-900 rounded-3xl shadow-md p-8 text-white relative overflow-hidden">
              <!-- Decorative lines -->
              <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full border border-white/10"></div>
              <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 rounded-full border border-white/5"></div>
              
              <div class="relative z-10">
                <h2 class="text-lg font-bold text-white mb-6">{{ $t('Business Hours') }}</h2>
                <div class="space-y-3 mb-8">
                  <div class="flex justify-between items-center text-sm border-b border-white/10 pb-3">
                    <span class="text-blue-200">{{ $t('Monday - Friday') }}</span>
                    <span class="font-bold">{{ $t('9:00 AM - 6:00 PM') }}</span>
                  </div>
                  <div class="flex justify-between items-center text-sm border-b border-white/10 pb-3">
                    <span class="text-blue-200">{{ $t('Saturday') }}</span>
                    <span class="font-bold">{{ $t('10:00 AM - 4:00 PM') }}</span>
                  </div>
                  <div class="flex justify-between items-center text-sm pb-1">
                    <span class="text-blue-200">{{ $t('Sunday') }}</span>
                    <span class="font-bold text-yellow-400">{{ $t('Closed') }}</span>
                  </div>
                </div>

                <div class="bg-white/10 rounded-2xl p-5 backdrop-blur-sm border border-white/10">
                  <h3 class="text-sm font-bold text-white mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $t('Need quick answers?') }}</h3>
                  <p class="text-sm text-blue-200 mb-4 leading-relaxed">{{ $t('Check our Frequently Asked Questions page for immediate answers to common issues.') }}</p>
                  <Link
                    href="/faq"
                    class="inline-flex items-center text-sm font-bold text-blue-300 hover:text-white transition-colors"
                  >{{ $t('View FAQ') }}<svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </Link>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
  title: String,
});

const form = useForm({
  name: '',
  email: '',
  type: 'general',
  subject: '',
  message: '',
});

const submit = () => {
  form.post(route('pages.contact.submit'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};
</script>
