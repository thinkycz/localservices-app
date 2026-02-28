<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import StarRating from '@/Components/StarRating.vue';

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
</script>

<template>
    <AppLayout>
        <Head title="Write a Review" />

        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <Link :href="route('bookings.index')" class="text-blue-600 hover:text-blue-800 text-sm mb-4 inline-block">
                        ← Back to My Bookings
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-900">Write a Review</h1>
                    <p class="text-gray-600 mt-2">Share your experience with {{ booking.service.name }}</p>
                </div>

                <!-- Service Summary -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ booking.service.name }}</h3>
                            <p class="text-sm text-gray-600">{{ booking.offering.name }}</p>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ new Date(booking.booking_date).toLocaleDateString() }} • 
                                {{ formatTime(booking.start_time) }} - {{ formatTime(booking.end_time) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Review Form -->
                <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm p-6">
                    <!-- Rating -->
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            How would you rate your experience? <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                @click="setRating(star)"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                                class="w-12 h-12 transition-transform hover:scale-110 focus:outline-none"
                            >
                                <svg
                                    class="w-full h-full"
                                    :class="(hoverRating ? star <= hoverRating : star <= form.rating) ? 'text-yellow-400 fill-current' : 'text-gray-300'"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.rating" class="text-red-500 text-sm mt-1">{{ form.errors.rating }}</p>
                        <p class="text-sm text-gray-500 mt-2">
                            {{ form.rating === 1 ? 'Poor' : 
                               form.rating === 2 ? 'Fair' : 
                               form.rating === 3 ? 'Good' : 
                               form.rating === 4 ? 'Very Good' : 
                               form.rating === 5 ? 'Excellent' : 'Select a rating' }}
                        </p>
                    </div>

                    <!-- Review Text -->
                    <div class="mb-6">
                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                            Tell us about your experience <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="comment"
                            v-model="form.comment"
                            rows="5"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            placeholder="What did you like? How was the service quality? Would you recommend it?"
                            required
                            minlength="10"
                        ></textarea>
                        <p v-if="form.errors.comment" class="text-red-500 text-sm mt-1">{{ form.errors.comment }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ form.comment.length }}/1000 characters (minimum 10)</p>
                    </div>

                    <!-- Tags -->
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Select tags that describe your experience (optional)
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="tag in availableTags"
                                :key="tag"
                                type="button"
                                @click="toggleTag(tag)"
                                :class="[
                                    'px-4 py-2 rounded-full text-sm font-medium transition-colors',
                                    selectedTags.includes(tag)
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                ]"
                            >
                                {{ tag }}
                            </button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-between pt-6 border-t">
                        <p class="text-sm text-gray-500">
                            Your review will be public and help others make informed decisions.
                        </p>
                        <div class="flex gap-3">
                            <Link
                                :href="route('bookings.index')"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="isSubmitting || form.rating === 0 || form.comment.length < 10"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
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
