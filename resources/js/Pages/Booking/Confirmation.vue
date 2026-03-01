<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    booking: { type: Object, required: true },
});

function formatPrice(amount) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
}

function formatDate(dateStr) {
    if (!dateStr) return 'Invalid Date';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) {
        const isoDate = new Date(dateStr + 'T00:00:00');
        if (!isNaN(isoDate.getTime())) {
            return isoDate.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        }
        return 'Invalid Date';
    }
    return d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
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

const statusConfig = {
    pending:   { label: 'Pending',   bg: 'bg-amber-50',  text: 'text-amber-700',  ring: 'ring-amber-600/20',  dot: 'bg-amber-500' },
    confirmed: { label: 'Confirmed', bg: 'bg-green-50',  text: 'text-green-700',  ring: 'ring-green-600/20',  dot: 'bg-green-500' },
    completed: { label: 'Completed', bg: 'bg-gray-50',   text: 'text-gray-700',   ring: 'ring-gray-600/10',   dot: 'bg-gray-500' },
    cancelled: { label: 'Cancelled', bg: 'bg-red-50',    text: 'text-red-700',    ring: 'ring-red-600/20',    dot: 'bg-red-500' },
};

const status = statusConfig[props.booking.status] || statusConfig.pending;
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-b from-green-50/50 via-white to-gray-50 py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Success Header -->
                <div class="text-center mb-10">
                    <div class="relative inline-block mb-5">
                        <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center shadow-xl shadow-green-200/50">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <!-- Pulse ring -->
                        <div class="absolute inset-0 w-20 h-20 bg-green-400 rounded-full animate-ping opacity-20"></div>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $t('Booking Confirmed!') }}</h1>
                    <p class="text-gray-500 text-base">{{ $t('Your appointment has been successfully scheduled') }}</p>
                </div>

                <!-- Booking Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-6">

                    <!-- Gradient Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-white font-bold text-lg">{{ $t('Booking Details') }}</h2>
                                <p class="text-blue-100 text-xs">Confirmation #{{ booking.id }}</p>
                            </div>
                        </div>
                        <span :class="[status.bg, status.text, status.ring, 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold ring-1 ring-inset uppercase tracking-wide']">
                            <span :class="[status.dot, 'w-1.5 h-1.5 rounded-full']"></span>
                            {{ status.label }}
                        </span>
                    </div>

                    <div class="p-6 space-y-5">

                        <!-- Service & Offering -->
                        <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 text-base">{{ booking.service?.name }}</h3>
                                <p class="text-sm text-gray-500 mt-0.5">{{ booking.offering?.name }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-xl font-bold text-gray-900">{{ formatPrice(booking.total_price) }}</div>
                            </div>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $t('Date') }}</span>
                                </div>
                                <p class="font-semibold text-gray-900 text-sm">{{ formatDate(booking.booking_date) }}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $t('Time') }}</span>
                                </div>
                                <p class="font-semibold text-gray-900 text-sm">{{ formatTime(booking.start_time) }} – {{ formatTime(booking.end_time) }}</p>
                            </div>
                        </div>

                        <!-- Provider -->
                        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $t('Provider') }}</div>
                                <div class="font-semibold text-gray-900 text-sm">{{ booking.provider?.name }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Card -->
                <div v-if="booking.customer_notes" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <h3 class="font-semibold text-gray-900 text-sm">{{ $t('Your Notes') }}</h3>
                    </div>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ booking.customer_notes }}</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <Link
                        :href="route('bookings.index')"
                        class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3.5 px-6 rounded-xl text-center transition-all shadow-lg shadow-blue-200/50 hover:shadow-xl flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>{{ $t('View My Bookings') }}</Link>
                    <Link
                        href="/"
                        class="flex-1 bg-white hover:bg-gray-50 text-gray-700 font-bold py-3.5 px-6 rounded-xl text-center transition-all border border-gray-200 hover:border-gray-300 flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>{{ $t('Back to Home') }}</Link>
                </div>

                <!-- Help Text -->
                <p class="text-center text-xs text-gray-400 mt-6 leading-relaxed">{{ $t('A confirmation email has been sent to your email address.') }}<br />{{ $t('The service provider will contact you if they have any questions.') }}</p>
            </div>
        </div>
    </AppLayout>
</template>
