<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    bookings: { type: Object, required: true },
});

function formatPrice(amount) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
}

function formatDate(dateStr) {
    if (!dateStr) return 'Invalid Date';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) {
        const isoDate = new Date(dateStr + 'T00:00:00');
        if (!isNaN(isoDate.getTime())) return isoDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        return 'Invalid Date';
    }
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
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
    completed: { label: 'Completed', bg: 'bg-gray-50',   text: 'text-gray-600',   ring: 'ring-gray-500/10',   dot: 'bg-gray-400' },
    cancelled: { label: 'Cancelled', bg: 'bg-red-50',    text: 'text-red-700',    ring: 'ring-red-600/20',    dot: 'bg-red-500' },
};

function getStatus(s) {
    return statusConfig[s] || statusConfig.pending;
}

function isPast(dateStr) {
    if (!dateStr) return false;
    let d = new Date(dateStr);
    if (isNaN(d.getTime())) d = new Date(dateStr + 'T00:00:00');
    if (isNaN(d.getTime())) return false;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return d < today;
}
</script>

<template>
    <AppLayout>
        <!-- Gradient Header -->
        <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $t('My Bookings') }}</h1>
                        <p class="text-sm text-blue-200 mt-1">{{ $t('View and manage your appointments') }}</p>
                    </div>
                    <Link
                        href="/services"
                        class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 backdrop-blur border border-white/20 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>{{ $t('Book a Service') }}</Link>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Bookings List -->
            <div v-if="bookings.data.length > 0" class="space-y-4">
                <div
                    v-for="booking in bookings.data"
                    :key="booking.id"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all hover:shadow-md group"
                    :class="{ 'opacity-50': booking.status === 'cancelled' }"
                >
                    <div class="flex flex-col sm:flex-row">
                        <!-- Left accent bar -->
                        <div
                            class="hidden sm:block w-1.5 shrink-0"
                            :class="{
                                'bg-amber-400': booking.status === 'pending',
                                'bg-green-500': booking.status === 'confirmed',
                                'bg-gray-300': booking.status === 'completed',
                                'bg-red-400': booking.status === 'cancelled',
                            }"
                        ></div>

                        <div class="flex-1 p-5">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                <!-- Left: Service Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2.5 mb-2">
                                        <span
                                            :class="[getStatus(booking.status).bg, getStatus(booking.status).text, getStatus(booking.status).ring, 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold ring-1 ring-inset']"
                                        >
                                            <span :class="[getStatus(booking.status).dot, 'w-1.5 h-1.5 rounded-full']"></span>
                                            {{ getStatus(booking.status).label }}
                                        </span>
                                        <span v-if="isPast(booking.booking_date) && booking.status !== 'cancelled' && booking.status !== 'completed'" class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider bg-gray-100 px-2 py-0.5 rounded">{{ $t('Past') }}</span>
                                    </div>

                                    <h3 class="font-bold text-gray-900 text-base mb-1">
                                        {{ booking.service?.name }}
                                    </h3>

                                    <p class="text-sm text-gray-500 mb-3">
                                        {{ booking.offering?.name }} · {{ booking.provider?.name }}
                                    </p>

                                    <div class="flex flex-wrap items-center gap-2 text-sm">
                                        <span class="flex items-center gap-1.5 text-gray-500 bg-gray-50 px-2.5 py-1 rounded-lg">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ formatDate(booking.booking_date) }}
                                        </span>
                                        <span class="flex items-center gap-1.5 text-gray-500 bg-gray-50 px-2.5 py-1 rounded-lg">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ formatTime(booking.start_time) }} – {{ formatTime(booking.end_time) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Right: Price & Actions -->
                                <div class="flex sm:flex-col items-center sm:items-end gap-3 shrink-0">
                                    <div class="text-xl font-bold text-gray-900">{{ formatPrice(booking.total_price) }}</div>

                                    <div class="flex gap-2">
                                        <Link
                                            v-if="booking.status === 'pending' || booking.status === 'confirmed'"
                                            :href="route('services.show', booking.service?.slug)"
                                            class="text-xs font-semibold text-gray-600 hover:text-gray-900 px-3 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all"
                                        >{{ $t('Details') }}</Link>
                                        <button
                                            v-if="booking.status === 'pending' || booking.status === 'confirmed'"
                                            @click="$inertia.post(route('bookings.cancel', booking.id))"
                                            class="text-xs font-semibold text-red-600 hover:text-red-700 px-3 py-2 rounded-xl border border-red-200 hover:bg-red-50 hover:border-red-300 transition-all"
                                        >{{ $t('Cancel') }}</button>
                                        <Link
                                            v-if="booking.status === 'completed' && !booking.has_review"
                                            :href="route('reviews.create', booking.id)"
                                            class="text-xs font-semibold text-amber-600 hover:text-amber-700 px-3 py-2 rounded-xl border border-amber-200 hover:bg-amber-50 hover:border-amber-300 transition-all flex items-center gap-1"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>{{ $t('Review') }}</Link>
                                        <Link
                                            v-if="booking.status === 'completed'"
                                            :href="route('services.show', booking.service?.slug)"
                                            class="text-xs font-semibold text-blue-600 hover:text-blue-700 px-3 py-2 rounded-xl border border-blue-200 hover:bg-blue-50 hover:border-blue-300 transition-all flex items-center gap-1"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>{{ $t('Rebook') }}</Link>

                                    </div>

                                </div>
                            </div>

                            <!-- Customer Notes -->
                            <div v-if="booking.customer_notes" class="mt-4 pt-3 border-t border-gray-100">
                                <div class="flex items-start gap-2">
                                    <svg class="w-3.5 h-3.5 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                    <p class="text-xs text-gray-500 leading-relaxed">{{ booking.customer_notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $t('No bookings yet') }}</h3>
                <p class="text-gray-500 mb-6 max-w-sm mx-auto text-sm">{{ $t('Browse our services and book your first appointment today.') }}</p>
                <Link
                    href="/services"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-semibold transition-all shadow-sm text-sm"
                >{{ $t('Browse Services') }}</Link>
            </div>

            <!-- Pagination -->
            <div v-if="bookings.links && bookings.links.length > 3" class="mt-8 flex justify-center">
                <div class="flex gap-1.5">
                    <Link
                        v-for="(link, index) in bookings.links"
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
