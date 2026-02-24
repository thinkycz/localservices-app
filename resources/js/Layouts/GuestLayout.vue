<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex">

        <!-- ── Left Brand Panel ─────────────────────────────────────── -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 to-blue-800 flex-col justify-between p-12 relative overflow-hidden">
            <!-- Background decoration circles -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-500 rounded-full opacity-20"></div>
            <div class="absolute -bottom-32 -right-16 w-80 h-80 bg-blue-400 rounded-full opacity-20"></div>

            <!-- Logo -->
            <Link :href="route('home')" class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                    </svg>
                </div>
                <span class="text-2xl font-bold text-white">LocalServices</span>
            </Link>

            <!-- Center content -->
            <div class="relative z-10">
                <h1 class="text-4xl font-bold text-white leading-tight mb-4">
                    Find Trusted Local Services
                </h1>
                <p class="text-blue-100 text-lg mb-10 leading-relaxed">
                    Connect with top-rated professionals in your area for any home service need.
                </p>

                <!-- Feature bullets -->
                <div class="space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">Verified Professionals</p>
                            <p class="text-blue-200 text-xs mt-0.5">All service providers are background-checked and reviewed</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">Easy Scheduling</p>
                            <p class="text-blue-200 text-xs mt-0.5">Book appointments at a time that works for you</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">4.8★ Average Rating</p>
                            <p class="text-blue-200 text-xs mt-0.5">Trusted by 50,000+ happy customers nationwide</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom stats -->
            <div class="flex items-center gap-8 relative z-10">
                <div>
                    <div class="text-2xl font-bold text-white">10k+</div>
                    <div class="text-blue-200 text-xs mt-0.5">Professionals</div>
                </div>
                <div class="w-px h-8 bg-white/20"></div>
                <div>
                    <div class="text-2xl font-bold text-white">50k+</div>
                    <div class="text-blue-200 text-xs mt-0.5">Customers</div>
                </div>
                <div class="w-px h-8 bg-white/20"></div>
                <div>
                    <div class="text-2xl font-bold text-white">100+</div>
                    <div class="text-blue-200 text-xs mt-0.5">Cities</div>
                </div>
            </div>
        </div>

        <!-- ── Right Form Panel ──────────────────────────────────────── -->
        <div class="flex-1 flex flex-col justify-center items-center bg-gray-50 px-6 py-12 lg:px-16">
            <!-- Mobile logo (shown only on small screens) -->
            <div class="lg:hidden mb-8">
                <Link :href="route('home')" class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">LocalServices</span>
                </Link>
            </div>

            <!-- Form card -->
            <div class="w-full max-w-md">
                <slot />
            </div>
        </div>

    </div>
</template>
