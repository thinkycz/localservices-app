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
        <Head :title="$t('My Reviews')" />

        <!-- Gradient Header -->
        <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $t('My Reviews') }}</h1>
                        <p class="text-sm text-blue-200 mt-1">{{ $t('Reviews you\'ve written for services') }}</p>
                    </div>
                    <Link
                        :href="route('bookings.index')"
                        class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 backdrop-blur border border-white/20 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>{{ $t('My Bookings') }}</Link>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Reviews List -->
            <div v-if="reviews.data.length > 0" class="space-y-4">
                <div
                    v-for="review in reviews.data"
                    :key="review.id"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all"
                >
                    <div class="p-5">
                        <div class="flex items-start gap-4">
                            <!-- Service Icon -->
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-sm font-bold">{{ getInitials(review.service.name) }}</span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <!-- Header -->
                                <div class="flex items-start justify-between gap-3 mb-1.5">
                                    <div>
                                        <Link
                                            :href="route('services.show', review.service.slug)"
                                            class="font-bold text-gray-900 hover:text-blue-600 transition-colors text-sm"
                                        >
                                            {{ review.service.name }}
                                        </Link>
                                        <p class="text-[11px] text-gray-400 mt-0.5">
                                            Reviewed on {{ formatDate(review.reviewed_at) }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <StarRating :rating="review.rating" size="sm" />
                                    </div>
                                </div>

                                <!-- Tags -->
                                <div v-if="review.tags && review.tags.length > 0" class="flex flex-wrap gap-1.5 mb-2.5">
                                    <span
                                        v-for="tag in review.tags"
                                        :key="tag"
                                        class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-semibold rounded-md"
                                    >
                                        {{ tag }}
                                    </span>
                                </div>

                                <!-- Comment -->
                                <p class="text-sm text-gray-600 leading-relaxed">{{ review.comment }}</p>

                                <!-- Booking Info -->
                                <div class="flex items-center gap-3 text-xs text-gray-400 mt-3 pt-3 border-t border-gray-50">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ formatDate(review.booking.booking_date) }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ formatTime(review.booking.start_time) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-amber-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $t('No reviews yet') }}</h3>
                <p class="text-gray-500 mb-6 max-w-sm mx-auto text-sm">{{ $t('After completing a service, you can share your experience to help others.') }}</p>
                <Link
                    :href="route('bookings.index')"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-semibold transition-all shadow-sm text-sm"
                >{{ $t('View My Bookings') }}</Link>
            </div>

            <!-- Pagination -->
            <div v-if="reviews.links && reviews.links.length > 3" class="mt-8 flex justify-center">
                <div class="flex gap-1.5">
                    <Link
                        v-for="(link, index) in reviews.links"
                        :key="index"
                        :href="link.url"
                        :class="[
                            'px-3.5 py-2 rounded-xl text-sm font-semibold transition-all',
                            link.active
                                ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-sm'
                                : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300',
                            !link.url && 'opacity-40 cursor-not-allowed pointer-events-none'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
