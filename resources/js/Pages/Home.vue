<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ServiceCard from '@/Components/ServiceCard.vue';

defineProps({
    featuredServices: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

function browseCategory(slug) {
    router.get(route('services.index'), { categories: [slug] });
}

function getCategoryIcon(name) {
    const icons = {
        'Barbershops': '💇',
        'Nail Salons': '💅',
        'Restaurants': '🍽️',
        'Coffee Shops': '☕',
        'Pet Grooming': '🐕',
        'Fitness & Gyms': '💪',
        'Spa & Massage': '💆',
        'Beauty Salons': '🎀',
    };
    return icons[name] || '📌';
}
</script>

<template>
    <AppLayout>
        <!-- Welcome Header -->
        <section class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Find Local Services</h1>
                        <p class="text-gray-600 mt-1">Browse top-rated professionals in your area</p>
                    </div>
                    <Link
                        :href="route('services.index')"
                        class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium"
                    >
                        View All Services
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Categories -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Browse by Category</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                <button
                    v-for="category in categories"
                    :key="category.id"
                    @click="browseCategory(category.slug)"
                    class="flex flex-col items-center gap-2 p-4 bg-white rounded-lg border border-gray-200 hover:border-blue-400 hover:shadow-sm transition-all group text-center"
                >
                    <span class="text-2xl">{{ getCategoryIcon(category.name) }}</span>
                    <span class="text-xs font-medium text-gray-700 group-hover:text-blue-600 line-clamp-1">{{ category.name }}</span>
                    <span class="text-xs text-gray-400">{{ category.services_count }} services</span>
                </button>
            </div>
        </section>

        <!-- Featured Services -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Featured Services</h2>
                    <p class="text-sm text-gray-500">Top-rated professionals ready to help</p>
                </div>
                <Link
                    :href="route('services.index')"
                    class="text-sm font-medium text-blue-600 hover:text-blue-700"
                >
                    See all
                </Link>
            </div>

            <div v-if="featuredServices.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link
                    v-for="service in featuredServices"
                    :key="service.id"
                    :href="route('services.show', service.slug)"
                    class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:border-blue-400 hover:shadow-md transition-all group"
                >
                    <!-- Image -->
                    <div class="h-40 bg-gray-100 overflow-hidden">
                        <img
                            v-if="service.image"
                            :src="service.image"
                            :alt="service.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100">
                            <svg class="w-12 h-12 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <span
                                v-if="service.badge"
                                :class="{
                                    'bg-blue-100 text-blue-700': service.badge_color === 'blue',
                                    'bg-green-100 text-green-700': service.badge_color === 'green',
                                    'bg-gray-100 text-gray-700': !service.badge_color || service.badge_color === 'gray'
                                }"
                                class="text-xs font-semibold px-2 py-0.5 rounded uppercase"
                            >
                                {{ service.badge }}
                            </span>
                            <span class="text-sm font-medium text-gray-500">{{ '$'.repeat(service.price_range) }}</span>
                        </div>
                        
                        <h3 class="font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">{{ service.name }}</h3>
                        
                        <div class="flex items-center gap-2 mb-2">
                            <div class="flex items-center gap-0.5">
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="text-sm font-semibold text-gray-800">{{ service.rating }}</span>
                            </div>
                            <span class="text-sm text-gray-500">({{ service.reviews_count }} reviews)</span>
                            <span class="text-gray-300">•</span>
                            <span class="text-sm text-gray-500">{{ service.category?.name }}</span>
                        </div>
                        
                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ service.description }}</p>
                        
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                            <span v-if="service.available_at" class="text-sm text-green-600 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ service.available_at }}
                            </span>
                            <span v-else class="text-sm text-gray-400">Check availability</span>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-else class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                </svg>
                <p class="text-gray-500">No services available yet. Check back soon!</p>
            </div>
        </section>

        <!-- How It Works -->
        <section class="bg-gray-50 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 text-center">How It Works</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">1. Find</h3>
                        <p class="text-sm text-gray-600">Search and browse services by category, location, or rating.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">2. Book</h3>
                        <p class="text-sm text-gray-600">Select your preferred time and book instantly with secure payment.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">3. Enjoy</h3>
                        <p class="text-sm text-gray-600">Get quality service from verified professionals. Rate and review.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Service Provider CTA -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-blue-600 rounded-xl p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="text-center md:text-left">
                    <h2 class="text-xl font-bold text-white mb-2">Are you a service professional?</h2>
                    <p class="text-blue-100">Join our network and grow your business with new customers.</p>
                </div>
                <Link
                    :href="route('vendor.onboarding.index')"
                    class="bg-white text-blue-600 font-semibold px-6 py-3 rounded-lg hover:bg-blue-50 transition shrink-0"
                >
                    Become a Provider
                </Link>
            </div>
        </section>

        <!-- Trust Indicators -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 border-t border-gray-200">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div>
                    <div class="text-2xl font-bold text-gray-900 mb-1">2,500+</div>
                    <div class="text-sm text-gray-500">Verified Providers</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 mb-1">50,000+</div>
                    <div class="text-sm text-gray-500">Services Completed</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 mb-1">4.8★</div>
                    <div class="text-sm text-gray-500">Average Rating</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 mb-1">24/7</div>
                    <div class="text-sm text-gray-500">Customer Support</div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
