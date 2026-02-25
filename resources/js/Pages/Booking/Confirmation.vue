<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    booking: { type: Object, required: true },
});

function formatPrice(amount) {
    return `$${Number(amount).toFixed(2)}`;
}

function formatDate(dateStr) {
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50 py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Success Header -->
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Booking Confirmed!</h1>
                    <p class="text-gray-600">Your appointment has been successfully booked.</p>
                </div>

                <!-- Booking Details Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-blue-600 px-6 py-4">
                        <h2 class="text-white font-bold text-lg">Booking Details</h2>
                        <p class="text-blue-100 text-sm">Confirmation #{{ booking.id }}</p>
                    </div>

                    <div class="p-6 space-y-4">
                        <!-- Service Info -->
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Service</p>
                                <p class="font-semibold text-gray-900">{{ booking.service?.name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Offering</p>
                                <p class="font-semibold text-gray-900">{{ booking.offering?.name }}</p>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <!-- Date & Time -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-gray-500">Date</p>
                                    <p class="font-semibold text-gray-900">{{ formatDate(booking.booking_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Time</p>
                                    <p class="font-semibold text-gray-900">{{ booking.start_time }} - {{ booking.end_time }}</p>
                                </div>
                            </div>

                            <!-- Provider -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-500">Service Provider</p>
                                <p class="font-semibold text-gray-900">{{ booking.provider?.name }}</p>
                            </div>

                            <!-- Status -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-500">Status</p>
                                <span 
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': booking.status === 'pending',
                                        'bg-green-100 text-green-800': booking.status === 'confirmed',
                                        'bg-gray-100 text-gray-800': booking.status === 'completed',
                                        'bg-red-100 text-red-800': booking.status === 'cancelled',
                                    }"
                                >
                                    {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-gray-900">Total Paid</span>
                                <span class="text-2xl font-bold text-blue-600">{{ formatPrice(booking.total_price) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Card -->
                <div v-if="booking.customer_notes" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Your Notes</h3>
                    <p class="text-gray-600">{{ booking.customer_notes }}</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <Link 
                        :href="route('bookings.index')"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl text-center transition"
                    >
                        View My Bookings
                    </Link>
                    <Link 
                        href="/"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-xl text-center transition"
                    >
                        Back to Home
                    </Link>
                </div>

                <!-- Help Text -->
                <p class="text-center text-sm text-gray-500 mt-6">
                    A confirmation email has been sent to your email address. 
                    The service provider will contact you if they have any questions.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

