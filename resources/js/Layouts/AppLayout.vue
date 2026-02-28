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
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 4.5A2.5 2.5 0 016.5 2H12v9l-3-2.5L6 11V4.5z" fill="currentColor" opacity="0.5"/>
                                    <path d="M12 2h5.5A2.5 2.5 0 0120 4.5v15a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 19.5V11l5-2.5L12 11V2z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                </svg>
                            </div>
                            <span class="text-gray-900" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; letter-spacing: -0.03em;">Bookable</span>
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
                            <li><Link :href="route('pages.contact')" class="text-sm text-gray-500 hover:text-blue-600 transition">Contact Support</Link></li>
                            <li class="text-sm text-gray-500">(555) 123-4567</li>
                            <li class="text-sm text-gray-500">hello@bookable.com</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-100 mt-8 pt-6 flex flex-col sm:flex-row items-center justify-between gap-2">
                    <p class="text-xs text-gray-400">© {{ new Date().getFullYear() }} Bookable. All rights reserved.</p>
                    <div class="flex gap-4">
                        <Link :href="route('pages.privacy')" class="text-xs text-gray-400 hover:text-gray-600">Privacy Policy</Link>
                        <Link :href="route('pages.terms')" class="text-xs text-gray-400 hover:text-gray-600">Terms of Service</Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
