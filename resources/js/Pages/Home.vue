<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const searchQuery = ref('');

const popularCategories = [
    { name: 'Plumbing',    icon: '🔧', slug: 'plumbing' },
    { name: 'Electrical',  icon: '⚡', slug: 'electrical' },
    { name: 'HVAC Repair', icon: '❄️', slug: 'hvac-repair' },
    { name: 'Cleaning',    icon: '🧹', slug: 'cleaning' },
    { name: 'Landscaping', icon: '🌿', slug: 'landscaping' },
    { name: 'Painting',    icon: '🎨', slug: 'painting' },
    { name: 'Carpentry',   icon: '🪚', slug: 'carpentry' },
    { name: 'Moving',      icon: '📦', slug: 'moving' },
];

function handleSearch() {
    router.get(route('services.index'), {
        q: searchQuery.value,
    });
}

function browseCategory(slug) {
    router.get(route('services.index'), { categories: [slug] });
}
</script>

<template>
    <AppLayout>
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-blue-600 to-blue-800 text-white py-20 px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">
                    Find Trusted Local Services
                </h1>
                <p class="text-blue-100 text-lg mb-10">
                    Connect with top-rated professionals in your area for any home service need.
                </p>

                <!-- Search Box -->
                <div class="bg-white rounded-2xl shadow-xl p-2 flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 flex items-center gap-3 px-4">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="What service do you need?"
                            class="flex-1 py-3 text-gray-800 placeholder-gray-400 focus:outline-none text-base border-0"
                            @keyup.enter="handleSearch"
                        />
                    </div>
                    <button
                        @click="handleSearch"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-xl transition-colors"
                    >
                        Search
                    </button>
                </div>
            </div>
        </section>

        <!-- Popular Categories -->
        <section class="max-w-6xl mx-auto px-4 py-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Popular Categories</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <button
                    v-for="cat in popularCategories"
                    :key="cat.slug"
                    @click="browseCategory(cat.slug)"
                    class="flex flex-col items-center gap-3 p-6 bg-white rounded-xl border border-gray-200 hover:border-blue-400 hover:shadow-md transition-all group"
                >
                    <span class="text-4xl">{{ cat.icon }}</span>
                    <span class="text-sm font-semibold text-gray-700 group-hover:text-blue-600">{{ cat.name }}</span>
                </button>
            </div>
        </section>

        <!-- How It Works -->
        <section class="bg-gray-100 py-16 px-4">
            <div class="max-w-5xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-900 mb-12 text-center">How It Works</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">Search</h3>
                        <p class="text-sm text-gray-600">Find the service you need from hundreds of local professionals.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">Compare</h3>
                        <p class="text-sm text-gray-600">Read reviews, compare prices, and choose the best fit for your needs.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">Book</h3>
                        <p class="text-sm text-gray-600">Schedule your service at a time that works for you.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats -->
        <section class="max-w-5xl mx-auto px-4 py-16">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div>
                    <div class="text-3xl font-bold text-blue-600 mb-1">10k+</div>
                    <div class="text-sm text-gray-600">Service Providers</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-blue-600 mb-1">50k+</div>
                    <div class="text-sm text-gray-600">Happy Customers</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-blue-600 mb-1">4.8★</div>
                    <div class="text-sm text-gray-600">Average Rating</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-blue-600 mb-1">100+</div>
                    <div class="text-sm text-gray-600">Cities Covered</div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
