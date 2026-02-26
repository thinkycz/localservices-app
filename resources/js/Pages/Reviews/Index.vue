<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import StarRating from '@/Components/StarRating.vue';

const props = defineProps({
    service: Object,
    reviews: Object,
    ratingDistribution: Array,
});

const averageRating = computed(() => props.service.rating || 0);
const totalReviews = computed(() => props.service.reviews_count || 0);

function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <AppLayout>
        <Head :title="`Reviews for ${service.name}`" />

        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                    <Link :href="route('home')" class="hover:text-blue-600">Home</Link>
                    <span>/</span>
                    <Link :href="route('services.index')" class="hover:text-blue-600">Services</Link>
                    <span>/</span>
                    <Link :href="route('services.show', service.slug)" class="hover:text-blue-600">{{ service.name }}</Link>
                    <span>/</span>
                    <span class="text-gray-900">Reviews</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Rating Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-sm p-6 sticky top-8">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Rating Overview</h2>
                            
                            <!-- Average Rating -->
                            <div class="text-center mb-6">
                                <div class="text-5xl font-bold text-gray-900 mb-2">{{ averageRating.toFixed(1) }}</div>
                                <div class="flex justify-center mb-2">
                                    <StarRating :rating="Math.round(averageRating)" size="lg" />
                                </div>
                                <p class="text-gray-600">{{ totalReviews }} reviews</p>
                            </div>

                            <!-- Rating Distribution -->
                            <div class="space-y-3">
                                <div
                                    v-for="dist in ratingDistribution"
                                    :key="dist.stars"
                                    class="flex items-center gap-3"
                                >
                                    <span class="text-sm font-medium text-gray-700 w-12">{{ dist.stars }} stars</span>
                                    <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-yellow-400 rounded-full"
                                            :style="{ width: `${dist.percentage}%` }"
                                        ></div>
                                    </div>
                                    <span class="text-sm text-gray-600 w-10 text-right">{{ dist.count }}</span>
                                </div>
                            </div>

                            <!-- Book Button -->
                            <Link
                                :href="route('services.book', service.slug)"
                                class="block w-full mt-6 bg-blue-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                            >
                                Book This Service
                            </Link>
                        </div>
                    </div>

                    <!-- Right Column - Reviews List -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-bold text-gray-900">Customer Reviews</h2>
                                <div class="flex gap-2">
                                    <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        <option>Most Recent</option>
                                        <option>Highest Rated</option>
                                        <option>Lowest Rated</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Reviews -->
                            <div v-if="reviews.data.length > 0" class="space-y-6">
                                <div
                                    v-for="review in reviews.data"
                                    :key="review.id"
                                    class="border-b border-gray-100 last:border-0 pb-6 last:pb-0"
                                >
                                    <div class="flex items-start gap-4">
                                        <!-- Avatar -->
                                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-blue-700 font-semibold">{{ getInitials(review.user.name) }}</span>
                                        </div>
                                        
                                        <div class="flex-1">
                                            <!-- Header -->
                                            <div class="flex items-center justify-between mb-2">
                                                <div>
                                                    <h4 class="font-semibold text-gray-900">{{ review.user.name }}</h4>
                                                    <p class="text-sm text-gray-500">{{ formatDate(review.reviewed_at) }}</p>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <StarRating :rating="review.rating" size="sm" />
                                                </div>
                                            </div>

                                            <!-- Tags -->
                                            <div v-if="review.tags && review.tags.length > 0" class="flex flex-wrap gap-2 mb-3">
                                                <span
                                                    v-for="tag in review.tags"
                                                    :key="tag"
                                                    class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full"
                                                >
                                                    {{ tag }}
                                                </span>
                                            </div>

                                            <!-- Comment -->
                                            <p class="text-gray-700 leading-relaxed">{{ review.comment }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No reviews yet</h3>
                                <p class="text-gray-600 mb-4">Be the first to review this service!</p>
                                <Link
                                    :href="route('services.show', service.slug)"
                                    class="text-blue-600 hover:text-blue-800 font-medium"
                                >
                                    Book now to leave a review →
                                </Link>
                            </div>

                            <!-- Pagination -->
                            <div v-if="reviews.links && reviews.links.length > 3" class="mt-6 flex justify-center">
                                <div class="flex gap-2">
                                    <Link
                                        v-for="(link, index) in reviews.links"
                                        :key="index"
                                        :href="link.url"
                                        :class="[
                                            'px-4 py-2 rounded-lg text-sm font-medium',
                                            link.active
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                            !link.url && 'opacity-50 cursor-not-allowed'
                                        ]"
                                        v-html="link.label"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
