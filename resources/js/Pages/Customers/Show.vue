<script setup>
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
});

function formatPrice(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount || 0);
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function formatTime(timeString) {
    if (!timeString) return '';
    // Handle time object from database
    if (typeof timeString === 'object' && timeString !== null) {
        return timeString;
    }
    // Handle string time format
    const [hours, minutes] = timeString.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return `${h12}:${minutes} ${ampm}`;
}

function getStatusBadge(status) {
    const badges = {
        pending: 'bg-amber-50 text-amber-600 border-amber-200',
        confirmed: 'bg-blue-50 text-blue-600 border-blue-200',
        completed: 'bg-green-50 text-green-600 border-green-200',
        cancelled: 'bg-red-50 text-red-600 border-red-200',
    };
    return badges[status] || 'bg-gray-50 text-gray-600 border-gray-200';
}

function getStatusLabel(status) {
    const labels = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        completed: 'Completed',
        cancelled: 'Cancelled',
    };
    return labels[status] || status;
}
</script>

<template>
    <Head :title="`${customer.name} - Customers`" />

    <VendorLayout activePage="customers">
        <!-- Back Button -->
        <div class="mb-6">
            <Link
                :href="route('customers.index')"
                class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Customers
            </Link>
        </div>

        <!-- Customer Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-5">
                    <!-- Avatar -->
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-2xl flex-shrink-0">
                        {{ customer.avatar_initials }}
                    </div>
                    <!-- Info -->
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ customer.name }}</h1>
                        <p class="text-sm text-gray-400 mt-1">Customer since {{ formatDate(customer.first_booking_date) }}</p>
                        <div class="flex items-center gap-4 mt-3">
                            <div class="flex items-center gap-1.5 text-sm text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ customer.email }}
                            </div>
                            <div class="flex items-center gap-1.5 text-sm text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ customer.phone }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Total Bookings</div>
                <div class="text-2xl font-bold text-gray-900">{{ customer.total_bookings }}</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Completed</div>
                <div class="text-2xl font-bold text-gray-900">{{ customer.completed_bookings }}</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Cancelled</div>
                <div class="text-2xl font-bold text-gray-900">{{ customer.cancelled_bookings }}</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Total Spent</div>
                <div class="text-2xl font-bold text-gray-900">{{ formatPrice(customer.total_spent) }}</div>
            </div>
        </div>

        <!-- Services Used -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Services Used</h2>
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="(service, idx) in customer.services_used"
                    :key="idx"
                    class="px-3 py-1.5 bg-blue-50 text-blue-700 text-sm font-medium rounded-lg"
                >
                    {{ service }}
                </span>
            </div>
        </div>

        <!-- Booking History -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Booking History</h2>
            </div>
            
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Service</th>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Time</th>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="booking in customer.bookings"
                        :key="booking.id"
                        class="border-b border-gray-50 hover:bg-gray-50 transition-colors"
                    >
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-900">{{ formatDate(booking.date) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ booking.service_name }}</div>
                            <div class="text-xs text-gray-400">{{ booking.offering_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ formatTime(booking.time) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                :class="[
                                    'px-2.5 py-1 text-xs font-bold rounded-full border',
                                    getStatusBadge(booking.status)
                                ]"
                            >
                                {{ getStatusLabel(booking.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-gray-900">{{ formatPrice(booking.price) }}</span>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="customer.bookings.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">No bookings yet</h3>
                            <p class="text-sm text-gray-500">This customer hasn't booked any services.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </VendorLayout>
</template>

