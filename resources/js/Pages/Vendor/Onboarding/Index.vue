<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 flex flex-col">
      <!-- Gradient Hero -->
      <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900 pt-16 pb-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/20 text-blue-200 text-xs font-bold uppercase tracking-wider mb-6">
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Provider Network
          </div>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight mb-4">
            Grow your business with <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400">Bookable</span>
          </h1>
          <p class="text-lg text-blue-100 max-w-2xl mx-auto mb-8 leading-relaxed">
            Join our marketplace to connect with new customers, manage your bookings easily, and get paid securely. Setup takes less than 5 minutes.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link
              :href="route('vendor.onboarding.step1')"
              class="w-full sm:w-auto px-8 py-3.5 bg-white text-blue-900 font-bold rounded-xl shadow-lg hover:bg-gray-50 transition-all flex items-center justify-center gap-2"
            >
              Get Started Now
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </Link>
          </div>
        </div>
      </div>

      <!-- Feature Section -->
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 pb-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center hover:shadow-md transition-shadow">
            <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Reach More Customers</h3>
            <p class="text-sm text-gray-500 leading-relaxed">List your services on our platform and get discovered by clients in your area actively looking to book.</p>
          </div>

          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center hover:shadow-md transition-shadow">
            <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Easy Scheduling</h3>
            <p class="text-sm text-gray-500 leading-relaxed">Manage your availability, appointments, and customer communications all from one beautiful dashboard.</p>
          </div>

          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center hover:shadow-md transition-shadow">
            <div class="w-14 h-14 bg-gradient-to-br from-amber-100 to-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Secure Payments</h3>
            <p class="text-sm text-gray-500 leading-relaxed">Get paid directly and securely for your services. We handle the transactions so you can focus on the work.</p>
          </div>
        </div>

        <!-- Requirements -->
        <div class="mt-12 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              Setup Checklist
            </h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
              <div class="flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">1</div>
                <div>
                  <h4 class="text-sm font-bold text-gray-900">Business Details</h4>
                  <p class="text-xs text-gray-500 mt-1">Basic contact and profile information</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">2</div>
                <div>
                  <h4 class="text-sm font-bold text-gray-900">Service Profile</h4>
                  <p class="text-xs text-gray-500 mt-1">Category, name, and description</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">3</div>
                <div>
                  <h4 class="text-sm font-bold text-gray-900">Offerings</h4>
                  <p class="text-xs text-gray-500 mt-1">Packages, duration, and pricing</p>
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
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>
