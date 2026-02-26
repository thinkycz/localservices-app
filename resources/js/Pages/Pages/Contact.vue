<template>
  <AppLayout>
    <Head title="Contact Us" />
    
    <div class="bg-gray-50 min-h-screen py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h1 class="text-3xl font-bold text-gray-900">{{ title }}</h1>
          <p class="mt-4 text-lg text-gray-600">
            Have a question or need help? We're here to assist you.
          </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <!-- Contact Form -->
          <div class="bg-white rounded-lg shadow-sm p-8">
            <form @submit.prevent="submit">
              <div class="space-y-6">
                <!-- Name -->
                <div>
                  <InputLabel for="name" value="Your Name" />
                  <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                  />
                  <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <!-- Email -->
                <div>
                  <InputLabel for="email" value="Email Address" />
                  <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                  />
                  <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <!-- Type -->
                <div>
                  <InputLabel for="type" value="Inquiry Type" />
                  <select
                    id="type"
                    v-model="form.type"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required
                  >
                    <option value="general">General Inquiry</option>
                    <option value="support">Customer Support</option>
                    <option value="partnership">Business Partnership</option>
                    <option value="feedback">Feedback</option>
                  </select>
                  <InputError :message="form.errors.type" class="mt-2" />
                </div>

                <!-- Subject -->
                <div>
                  <InputLabel for="subject" value="Subject" />
                  <TextInput
                    id="subject"
                    v-model="form.subject"
                    type="text"
                    class="mt-1 block w-full"
                    required
                  />
                  <InputError :message="form.errors.subject" class="mt-2" />
                </div>

                <!-- Message -->
                <div>
                  <InputLabel for="message" value="Message" />
                  <textarea
                    id="message"
                    v-model="form.message"
                    rows="5"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required
                    minlength="10"
                  ></textarea>
                  <InputError :message="form.errors.message" class="mt-2" />
                </div>

                <!-- Submit -->
                <div>
                  <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    class="w-full justify-center"
                  >
                    {{ form.processing ? 'Sending...' : 'Send Message' }}
                  </PrimaryButton>
                </div>
              </div>
            </form>

            <!-- Success Message -->
            <div v-if="$page.props.flash?.success" class="mt-6 p-4 bg-green-50 rounded-lg">
              <p class="text-green-800">{{ $page.props.flash.success }}</p>
            </div>
          </div>

          <!-- Contact Info -->
          <div class="space-y-8">
            <div class="bg-white rounded-lg shadow-sm p-8">
              <h2 class="text-xl font-semibold text-gray-900 mb-6">Contact Information</h2>
              
              <div class="space-y-4">
                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Email</p>
                    <p class="text-gray-600">support@localservices.com</p>
                  </div>
                </div>

                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Phone</p>
                    <p class="text-gray-600">1-800-LOCAL-SRV</p>
                  </div>
                </div>

                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Address</p>
                    <p class="text-gray-600">123 Business Street<br>San Francisco, CA 94102</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-8">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Business Hours</h2>
              <div class="space-y-2 text-gray-600">
                <div class="flex justify-between">
                  <span>Monday - Friday</span>
                  <span>9:00 AM - 6:00 PM</span>
                </div>
                <div class="flex justify-between">
                  <span>Saturday</span>
                  <span>10:00 AM - 4:00 PM</span>
                </div>
                <div class="flex justify-between">
                  <span>Sunday</span>
                  <span>Closed</span>
                </div>
              </div>
            </div>

            <div class="bg-indigo-50 rounded-lg p-8">
              <h2 class="text-xl font-semibold text-indigo-900 mb-4">Need Immediate Help?</h2>
              <p class="text-indigo-700 mb-4">
                Check our FAQ page for quick answers to common questions.
              </p>
              <Link
                href="/faq"
                class="inline-flex items-center text-indigo-600 font-medium hover:text-indigo-800"
              >
                Visit FAQ
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </Link>
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
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

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
