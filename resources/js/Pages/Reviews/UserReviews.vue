<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import StarRating from '@/Components/StarRating.vue';

const props = defineProps({
    reviews: Object,
});

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

function formatTime(time) {
    if (!time) return '';
    const s = String(time).trim();
    if (!s) return '';
    if (/[ap]m\b/.test(s) && !s.includes('T')) return s;

    const asDate = new Date(s);
    if (!Number.isNaN(asDate.getTime())) {
        return asDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    const m = s.match(/^(\d{1,2}):(\d{2})(?::(\d{2})(?:\.\d+)?)?$/);
    if (m) {
        const h = Number(m[1]);
        const min = Number(m[2]);
        const d = new Date(1970, 0, 1, h, min, 0);
        return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    return s;
}
</script>

<template>
    <AppLayout>
        <Head title="My Reviews" />

        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">My Reviews</h1>
                    <p class="text-gray-600 mt-2">Reviews you've written for services you've used</p>
                </div>

                <!-- Reviews List -->
                <div v-if="reviews.data.length > 0" class="space-y-4">
                    <div
                        v-for="review in reviews.data"
                        :key="review.id"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
                    >
                        <div class="flex items-start gap-4">
                            <!-- Service Image -->
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xl font-bold">{{ getInitials(review.service.name) }}</span>
                            </div>

                            <div class="flex-1">
                                <!-- Header -->
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <Link
                                            :href="route('services.show', review.service.slug)"
                                            class="font-bold text-gray-900 hover:text-blue-600 text-lg"
                                        >
                                            {{ review.service.name }}
                                        </Link>
                                        <p class="text-sm text-gray-500">
                                            Reviewed on {{ formatDate(review.reviewed_at) }}
                                        </p>
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
                                <p class="text-gray-700 leading-relaxed mb-4">{{ review.comment }}</p>

                                <!-- Booking Info -->
                                <div class="flex items-center gap-4 text-sm text-gray-500 pt-4 border-t border-gray-100">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ formatDate(review.booking.booking_date) }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ formatTime(review.booking.start_time) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No reviews yet</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        You haven't written any reviews yet. After completing a service booking, you can share your experience to help others.
                    </p>
                    <Link
                        :href="route('bookings.index')"
                        class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors"
                    >
                        View My Bookings
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="reviews.links && reviews.links.length > 3" class="mt-8 flex justify-center">
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
    </AppLayout>
</template>
