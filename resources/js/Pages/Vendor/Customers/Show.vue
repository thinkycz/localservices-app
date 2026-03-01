<script setup>
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    customer: { type: Object, required: true },
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
    if (typeof timeString === 'object' && timeString !== null) return timeString;
    const [hours, minutes] = timeString.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return `${h12}:${minutes} ${ampm}`;
}

function getStatusConfig(status) {
    const config = {
        pending: { label: 'Pending', bg: 'bg-amber-50', text: 'text-amber-700', ring: 'ring-amber-600/20', dot: 'bg-amber-500' },
        confirmed: { label: 'Confirmed', bg: 'bg-blue-50', text: 'text-blue-700', ring: 'ring-blue-700/20', dot: 'bg-blue-500' },
        completed: { label: 'Completed', bg: 'bg-emerald-50', text: 'text-emerald-700', ring: 'ring-emerald-600/20', dot: 'bg-emerald-500' },
        cancelled: { label: 'Cancelled', bg: 'bg-red-50', text: 'text-red-700', ring: 'ring-red-600/20', dot: 'bg-red-500' },
    };
    return config[status] || config.pending;
}
</script>

<template>
    <Head ::title="$t('`${customer.name} - Customers`')" />

    <VendorLayout activePage="customers">
        <div class="flex flex-col gap-6">

            <!-- Back link -->
            <div>
                <Link
                    :href="route('vendor.customers.index')"
                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>{{ $t('Back to Customers') }}</Link>
            </div>

            <!-- Customer Profile Card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-2xl font-bold shadow-md flex-shrink-0">
                        {{ customer.avatar_initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl font-bold text-gray-900">{{ customer.name }}</h1>
                        <p class="text-sm text-gray-400 mt-0.5">Customer since {{ formatDate(customer.first_booking_date) }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a
                            v-if="customer.email"
                            :href="'mailto:' + customer.email"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-200 rounded-xl text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ customer.email }}
                        </a>
                        <a
                            v-if="customer.phone"
                            :href="'tel:' + customer.phone"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-200 rounded-xl text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ customer.phone }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats + Services row -->
            <div class="grid grid-cols-5 gap-4">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">{{ $t('Total Bookings') }}</div>
                    <div class="text-2xl font-bold text-gray-900">{{ customer.total_bookings }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">{{ $t('Completed') }}</div>
                    <div class="text-2xl font-bold text-emerald-600">{{ customer.completed_bookings }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">{{ $t('Cancelled') }}</div>
                    <div class="text-2xl font-bold text-red-600">{{ customer.cancelled_bookings }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">{{ $t('Total Spent') }}</div>
                    <div class="text-2xl font-bold text-gray-900">{{ formatPrice(customer.total_spent) }}</div>
                </div>

                <!-- Services Used -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-2">{{ $t('Services') }}</div>
                    <div class="flex flex-wrap gap-1">
                        <span
                            v-for="(service, idx) in customer.services_used"
                            :key="idx"
                            class="px-2 py-0.5 bg-purple-50 text-purple-700 text-xs font-medium rounded-md ring-1 ring-inset ring-purple-700/10"
                        >
                            {{ service }}
                        </span>
                        <span v-if="customer.services_used.length === 0" class="text-xs text-gray-400">{{ $t('None') }}</span>
                    </div>
                </div>
            </div>

            <!-- Booking History -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">{{ $t('Booking History') }}</h2>
                </div>
                
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/30">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $t('Date') }}</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $t('Service') }}</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $t('Time') }}</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $t('Status') }}</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $t('Price') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="booking in customer.bookings"
                            :key="booking.id"
                            class="group hover:bg-blue-50/30 transition-colors cursor-pointer"
                            @click="router.visit(route('vendor.bookings.show', booking.id))"
                        >
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-900">{{ formatDate(booking.date) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ booking.service_name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ booking.offering_name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">{{ formatTime(booking.time) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="[
                                    'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 ring-inset',
                                    getStatusConfig(booking.status).bg,
                                    getStatusConfig(booking.status).text,
                                    getStatusConfig(booking.status).ring
                                ]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', getStatusConfig(booking.status).dot]"></span>
                                    {{ getStatusConfig(booking.status).label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-gray-900">{{ formatPrice(booking.price) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <Link
                                    :href="route('vendor.bookings.show', booking.id)"
                                    class="text-gray-400 group-hover:text-blue-600 transition-colors"
                                    @click.stop
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </Link>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="customer.bookings.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-base font-semibold text-gray-900 mb-1">{{ $t('No bookings yet') }}</h3>
                                <p class="text-sm text-gray-500">{{ $t('This customer hasn\'t booked any services.') }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </VendorLayout>
</template>
