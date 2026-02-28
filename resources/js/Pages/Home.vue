<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

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

const searchQuery = ref('');

function doSearch() {
    if (searchQuery.value.trim()) {
        router.get(route('services.index'), { q: searchQuery.value.trim() });
    }
}

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

function formatReviews(n) {
    if (n >= 1000) return (n / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
    return n?.toLocaleString() ?? '0';
}
</script>

<template>
    <AppLayout>
        <!-- Hero -->
        <section class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900 relative overflow-hidden">
            <!-- Subtle glow -->
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 relative">
                <div class="max-w-2xl">
                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 leading-tight">
                        Find & Book Local Services
                    </h1>
                    <p class="text-blue-200 text-lg mb-8">Browse top-rated professionals and book appointments instantly.</p>

                    <!-- Search bar -->
                    <form @submit.prevent="doSearch" class="flex gap-2">
                        <div class="flex-1 relative">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search services..."
                                class="w-full pl-12 pr-4 py-3.5 bg-white/10 backdrop-blur-sm border-2 border-white/20 rounded-xl text-white placeholder-blue-200/60 focus:outline-none focus:border-white/40 focus:bg-white/15 transition-all text-sm"
                            />
                        </div>
                        <button
                            type="submit"
                            class="px-6 py-3.5 bg-white text-gray-900 font-bold rounded-xl hover:bg-blue-50 transition-colors text-sm shrink-0"
                        >
                            Search
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Categories -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-gray-900">Browse by Category</h2>
                <Link :href="route('categories.index')" class="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                    All categories
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </Link>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                <button
                    v-for="category in categories"
                    :key="category.id"
                    @click="browseCategory(category.slug)"
                    class="flex flex-col items-center gap-2 p-4 bg-white rounded-2xl border border-gray-100 hover:border-blue-200 hover:shadow-md transition-all group text-center"
                >
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                        {{ getCategoryIcon(category.name) }}
                    </div>
                    <span class="text-xs font-semibold text-gray-700 group-hover:text-blue-600 line-clamp-1 transition-colors">{{ category.name }}</span>
                    <span class="text-[10px] text-gray-400 font-medium">{{ category.services_count }}</span>
                </button>
            </div>
        </section>

        <!-- Featured Services -->
        <section class="bg-gray-50/50 py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Featured Services</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Top-rated professionals ready to help</p>
                    </div>
                    <Link :href="route('services.index')" class="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                        View all
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                </div>

                <div v-if="featuredServices.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Link
                        v-for="service in featuredServices"
                        :key="service.id"
                        :href="route('services.show', service.slug)"
                        class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-lg hover:border-gray-200 transition-all duration-300 group"
                    >
                        <!-- Image -->
                        <div class="h-36 bg-gray-100 overflow-hidden relative">
                            <img
                                v-if="service.image"
                                :src="service.image"
                                :alt="service.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
                                <svg class="w-10 h-10 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                                </svg>
                            </div>
                            <!-- Price overlay -->
                            <div class="absolute top-2.5 right-2.5">
                                <span class="bg-white/90 backdrop-blur-sm text-gray-700 text-[10px] font-bold px-1.5 py-0.5 rounded-md shadow-sm">{{ '$'.repeat(service.price_range) }}</span>
                            </div>
                            <!-- Badge -->
                            <div v-if="service.badge" class="absolute top-2.5 left-2.5">
                                <span
                                    class="text-[10px] font-bold px-1.5 py-0.5 rounded-md uppercase tracking-wide"
                                    :class="{
                                        'bg-blue-500 text-white': service.badge_color === 'blue',
                                        'bg-green-500 text-white': service.badge_color === 'green',
                                        'bg-gray-700 text-white': !service.badge_color || service.badge_color === 'gray'
                                    }"
                                >{{ service.badge }}</span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-4">
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="text-xs font-bold text-gray-800">{{ service.rating }}</span>
                                <span class="text-[10px] text-gray-400">({{ formatReviews(service.reviews_count) }})</span>
                            </div>

                            <h3 class="text-sm font-bold text-gray-900 mb-1 group-hover:text-blue-700 transition-colors line-clamp-1">{{ service.name }}</h3>
                            <p class="text-[11px] text-gray-400 font-medium mb-2">{{ service.category?.name }}</p>
                            <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ service.description }}</p>

                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50">
                                <span v-if="service.available_at" class="text-[11px] font-medium text-green-600 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ service.available_at }}
                                </span>
                                <span v-else class="text-[11px] text-gray-400">Check availability</span>
                                <span class="text-[11px] font-semibold text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-0.5">
                                    View
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="bg-white rounded-2xl border border-gray-100 p-16 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">No services available yet. Check back soon!</p>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-lg font-bold text-gray-900 mb-2 text-center">How It Works</h2>
                <p class="text-sm text-gray-500 text-center mb-8">Get started in three simple steps</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 text-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <div class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-2">Step 1</div>
                        <h3 class="font-bold text-gray-900 mb-1.5">Find</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Search and browse services by category or rating.</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 text-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-green-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-[10px] font-bold text-green-600 uppercase tracking-widest mb-2">Step 2</div>
                        <h3 class="font-bold text-gray-900 mb-1.5">Book</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Select your preferred time and book instantly.</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 text-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-gradient-to-br from-amber-100 to-amber-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-[10px] font-bold text-amber-600 uppercase tracking-widest mb-2">Step 3</div>
                        <h3 class="font-bold text-gray-900 mb-1.5">Enjoy</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Get quality service from verified professionals.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Service Provider CTA -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900 rounded-2xl p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl"></div>
                <div class="text-center md:text-left relative">
                    <h2 class="text-xl font-bold text-white mb-2">Are you a service professional?</h2>
                    <p class="text-blue-200">Join our network and grow your business with new customers.</p>
                </div>
                <Link
                    :href="route('vendor.onboarding.index')"
                    class="bg-white text-gray-900 font-bold px-6 py-3 rounded-xl hover:bg-blue-50 transition-colors shrink-0 text-sm relative"
                >
                    Become a Provider
                </Link>
            </div>
        </section>

        <!-- Trust Indicators -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 border-t border-gray-100">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div>
                    <div class="text-2xl font-bold text-gray-900 mb-0.5">2,500+</div>
                    <div class="text-xs text-gray-400 font-medium">Verified Providers</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 mb-0.5">50,000+</div>
                    <div class="text-xs text-gray-400 font-medium">Services Completed</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 mb-0.5">4.8★</div>
                    <div class="text-xs text-gray-400 font-medium">Average Rating</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900 mb-0.5">24/7</div>
                    <div class="text-xs text-gray-400 font-medium">Customer Support</div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
