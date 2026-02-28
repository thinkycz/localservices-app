<script setup>
import AppNavbar from '@/Components/AppNavbar.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <AppNavbar />
        <main class="flex-1">
            <slot />
        </main>
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                                </svg>
                            </div>
                            <span class="font-bold text-gray-900">LocalServices</span>
                        </div>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Connecting you with trusted local service providers since 2020. Quality, reliability, and community at every booking.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 mb-3">Quick Links</h4>
                        <ul class="space-y-2">
                            <li><Link :href="route('services.index')" class="text-sm text-gray-500 hover:text-blue-600 transition">Browse Services</Link></li>
                            <li><Link :href="route('bookings.index')" class="text-sm text-gray-500 hover:text-blue-600 transition">My Bookings</Link></li>
                            <li><Link :href="route('vendor.onboarding.index')" class="text-sm text-gray-500 hover:text-blue-600 transition">List Your Service</Link></li>
                            <li><Link :href="route('pages.faq')" class="text-sm text-gray-500 hover:text-blue-600 transition">Help &amp; Support</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 mb-3">Contact</h4>
                        <ul class="space-y-2">
                            <li class="text-sm text-gray-500">(555) 123-4567</li>
                            <li class="text-sm text-gray-500">hello@localservices.com</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-100 mt-8 pt-6 flex flex-col sm:flex-row items-center justify-between gap-2">
                    <p class="text-xs text-gray-400">© {{ new Date().getFullYear() }} LocalServices. All rights reserved.</p>
                    <div class="flex gap-4">
                        <a href="#" class="text-xs text-gray-400 hover:text-gray-600">Privacy Policy</a>
                        <a href="#" class="text-xs text-gray-400 hover:text-gray-600">Terms of Service</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
