<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    bookings: { type: Object, required: true },
});

function formatPrice(amount) {
    return `$${Number(amount).toFixed(2)}`;
}

function formatDate(dateStr) {
    if (!dateStr) return 'Invalid Date';
    // Handle various date formats
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) {
        // Try parsing as ISO date string
        const isoDate = new Date(dateStr + 'T00:00:00');
        if (!isNaN(isoDate.getTime())) {
            return isoDate.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            });
        }
        return 'Invalid Date';
    }
    return d.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function getStatusClasses(status) {
    return {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-green-100 text-green-800',
        'completed': 'bg-gray-100 text-gray-800',
        'cancelled': 'bg-red-100 text-red-800',
    }[status] || 'bg-gray-100 text-gray-800';
}

function isPast(dateStr) {
    if (!dateStr) return false;
    const bookingDate = new Date(dateStr);
    if (isNaN(bookingDate.getTime())) {
        const isoDate = new Date(dateStr + 'T00:00:00');
        if (!isNaN(isoDate.getTime())) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return isoDate < today;
        }
        return false;
    }
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return bookingDate < today;
}
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">My Bookings</h1>
                    <p class="text-gray-600 mt-2">View and manage your appointments</p>
                </div>

                <!-- Bookings List -->
                <div v-if="bookings.data.length > 0" class="space-y-4">
                    <div
                        v-for="booking in bookings.data"
                        :key="booking.id"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
                        :class="{ 'opacity-60': booking.status === 'cancelled' }"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <!-- Left: Service Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2">
                                    <span 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="getStatusClasses(booking.status)"
                                    >
                                        {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
                                    </span>
                                    <span v-if="isPast(booking.booking_date) && booking.status !== 'cancelled'" class="text-xs text-gray-500">
                                        Past
                                    </span>
                                </div>
                                
                                <h3 class="font-bold text-gray-900 text-lg mb-1">
                                    {{ booking.service?.name }}
                                </h3>
                                
                                <p class="text-sm text-gray-500 mb-3">
                                    {{ booking.offering?.name }} with {{ booking.provider?.name }}
                                </p>

                                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ formatDate(booking.booking_date) }}
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ booking.start_time }} - {{ booking.end_time }}
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Price & Actions -->
                            <div class="flex sm:flex-col items-center sm:items-end gap-3">
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-gray-900">{{ formatPrice(booking.total_price) }}</p>
                                </div>
                                
                                <div class="flex gap-2">
                                    <Link
                                        v-if="booking.status === 'pending' || booking.status === 'confirmed'"
                                        :href="route('services.show', booking.service?.slug)"
                                        class="text-sm text-gray-600 hover:text-gray-900 px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                                    >
                                        View Details
                                    </Link>
                                    <button
                                        v-if="booking.status === 'pending' || booking.status === 'confirmed'"
                                        @click="$inertia.post(route('bookings.cancel', booking.id))"
                                        class="text-sm text-red-600 hover:text-red-700 px-3 py-2 rounded-lg border border-red-200 hover:bg-red-50 transition"
                                    >
                                        Cancel
                                    </button>
                                    <Link
                                        v-if="booking.status === 'completed' && !booking.has_review"
                                        :href="route('reviews.create', booking.id)"
                                        class="text-sm text-green-600 hover:text-green-700 px-3 py-2 rounded-lg border border-green-200 hover:bg-green-50 transition"
                                    >
                                        Write a Review
                                    </Link>
                                    <Link
                                        v-if="booking.status === 'completed'"
                                        :href="route('services.show', booking.service?.slug)"
                                        class="text-sm text-blue-600 hover:text-blue-700 px-3 py-2 rounded-lg border border-blue-200 hover:bg-blue-50 transition"
                                    >
                                        Book Again
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Notes -->
                        <div v-if="booking.customer_notes" class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">Your notes:</p>
                            <p class="text-sm text-gray-700">{{ booking.customer_notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No bookings yet</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">You haven't made any service bookings yet.</p>
                    <Link 
                        href="/services"
                        class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors"
                    >
                        Browse Services
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="bookings.links && bookings.links.length > 3" class="mt-8 flex justify-center">
                    <div class="flex gap-2">
                        <Link
                            v-for="(link, index) in bookings.links"
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
