<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    booking: Object,
});

const form = useForm({
    booking_id: props.booking.id,
    service_id: props.booking.service_id,
    rating: 0,
    comment: '',
    tags: [],
});

const selectedTags = ref([]);
const availableTags = [
    'Professional',
    'On-time',
    'Quality Service',
    'Friendly',
    'Clean',
    'Good Value',
    'Expert',
    'Recommended',
];

const hoverRating = ref(0);

const isSubmitting = computed(() => form.processing);

function setRating(rating) {
    form.rating = rating;
}

function toggleTag(tag) {
    const index = selectedTags.value.indexOf(tag);
    if (index > -1) {
        selectedTags.value.splice(index, 1);
    } else {
        selectedTags.value.push(tag);
    }
    form.tags = selectedTags.value;
}

function submit() {
    if (form.rating === 0) {
        alert('Please select a rating');
        return;
    }
    form.post(route('reviews.store'), {
        onSuccess: () => {
            // Redirect handled by controller
        },
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

function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
}

const ratingLabels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
</script>

<template>
    <AppLayout>
        <Head :title="$t('Write a Review')" />

        <!-- Gradient Header -->
        <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <Link :href="route('bookings.index')" class="inline-flex items-center gap-1.5 text-blue-200 hover:text-white text-sm font-medium mb-4 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>{{ $t('Back to My Bookings') }}</Link>
                <h1 class="text-2xl font-bold text-white">{{ $t('Write a Review') }}</h1>
                <p class="text-sm text-blue-200 mt-1">Share your experience with {{ booking.service.name }}</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-2xl">
                <!-- Service Summary -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-5">
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="text-white text-sm font-bold">{{ getInitials(booking.service.name) }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">{{ booking.service.name }}</h3>
                            <p class="text-[11px] text-gray-400">{{ booking.offering.name }}</p>
                        </div>
                        <div class="ml-auto text-right">
                            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ new Date(booking.booking_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}
                                · {{ formatTime(booking.start_time) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Review Form -->
                <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 space-y-6">
                        <!-- Rating -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-3">{{ $t('How would you rate your experience?') }}<span class="text-red-400">*</span>
                            </label>
                            <div class="flex items-center gap-1.5">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    @click="setRating(star)"
                                    @mouseenter="hoverRating = star"
                                    @mouseleave="hoverRating = 0"
                                    class="w-10 h-10 transition-transform hover:scale-110 focus:outline-none"
                                >
                                    <svg
                                        class="w-full h-full transition-colors"
                                        :class="(hoverRating ? star <= hoverRating : star <= form.rating) ? 'text-amber-400 fill-current' : 'text-gray-200'"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                </button>
                                <span v-if="form.rating > 0" class="ml-2 text-sm font-semibold text-gray-700">{{ ratingLabels[form.rating] }}</span>
                                <span v-else class="ml-2 text-xs text-gray-400">{{ $t('Select a rating') }}</span>
                            </div>
                            <p v-if="form.errors.rating" class="text-red-500 text-xs mt-1.5">{{ form.errors.rating }}</p>
                        </div>

                        <!-- Review Text -->
                        <div>
                            <label for="comment" class="block text-xs font-semibold text-gray-500 mb-1.5">{{ $t('Tell us about your experience') }}<span class="text-red-400">*</span>
                            </label>
                            <textarea
                                id="comment"
                                v-model="form.comment"
                                rows="4"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition resize-none"
                                :placeholder="$t('What did you like? How was the service quality? Would you recommend it?')"
                                required
                                minlength="10"
                            ></textarea>
                            <div class="flex items-center justify-between mt-1">
                                <p v-if="form.errors.comment" class="text-red-500 text-xs">{{ form.errors.comment }}</p>
                                <p class="text-[10px] text-gray-400 ml-auto">{{ form.comment.length }}/1000</p>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-2">{{ $t('Tags') }}<span class="text-gray-300 font-normal">{{ $t('(optional)') }}</span>
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="tag in availableTags"
                                    :key="tag"
                                    type="button"
                                    @click="toggleTag(tag)"
                                    :class="[
                                        'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all',
                                        selectedTags.includes(tag)
                                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-sm'
                                            : 'bg-gray-50 text-gray-600 border border-gray-200 hover:border-blue-300 hover:text-blue-600'
                                    ]"
                                >
                                    {{ tag }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Footer -->
                    <div class="px-6 py-4 border-t border-gray-50 flex items-center justify-between bg-gray-50/50">
                        <p class="text-[11px] text-gray-400 hidden sm:block">{{ $t('Your review will be public.') }}</p>
                        <div class="flex gap-2.5 ml-auto">
                            <Link
                                :href="route('bookings.index')"
                                class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                            >{{ $t('Cancel') }}</Link>
                            <button
                                type="submit"
                                :disabled="isSubmitting || form.rating === 0 || form.comment.length < 10"
                                class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm"
                            >
                                {{ isSubmitting ? 'Submitting...' : 'Submit Review' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
