<script setup>
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    bookings: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const sort = ref(props.filters.sort || 'newest');

function applyFilters() {
    router.get(route('vendor.bookings.index'), {
        search: search.value,
        status: statusFilter.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        sort: sort.value,
    }, { preserveState: true });
}

function getStatusClasses(status) {
    const classes = {
        pending: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
        confirmed: 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/20',
        completed: 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20',
        cancelled: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20',
    };
    return classes[status] || 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-600/20';
}

function getStatusDot(status) {
    const dots = {
        pending: 'bg-amber-500',
        confirmed: 'bg-blue-500',
        completed: 'bg-emerald-500',
        cancelled: 'bg-red-500',
    };
    return dots[status] || 'bg-gray-500';
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
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
    <Head title="Bookings" />

    <VendorLayout activePage="bookings">
        <div class="flex flex-col gap-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-5 gap-4">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-gray-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-1">Total</div>
                    <div class="text-2xl font-bold text-gray-900">{{ stats.total }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-yellow-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-1">Pending</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-1">Confirmed</div>
                    <div class="text-2xl font-bold text-blue-600">{{ stats.confirmed }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-1">Completed</div>
                    <div class="text-2xl font-bold text-green-600">{{ stats.completed }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 mb-1">Revenue</div>
                    <div class="text-2xl font-bold text-gray-900">${{ Number(stats.total_revenue).toFixed(2) }}</div>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-center gap-4">
                    <!-- Search -->
                    <div class="flex-1 relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by customer name..."
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    
                    <!-- Filter Buttons -->
                    <div class="flex items-center gap-1">
                        <button
                            @click="statusFilter = ''; applyFilters()"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                                statusFilter === ''
                                    ? 'bg-gray-900 text-white'
                                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                            ]"
                        >
                            All
                        </button>
                        <button
                            @click="statusFilter = 'pending'; applyFilters()"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                                statusFilter === 'pending'
                                    ? 'bg-gray-900 text-white'
                                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                            ]"
                        >
                            Pending
                        </button>
                        <button
                            @click="statusFilter = 'confirmed'; applyFilters()"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                                statusFilter === 'confirmed'
                                    ? 'bg-gray-900 text-white'
                                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                            ]"
                        >
                            Confirmed
                        </button>
                        <button
                            @click="statusFilter = 'completed'; applyFilters()"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                                statusFilter === 'completed'
                                    ? 'bg-gray-900 text-white'
                                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                            ]"
                        >
                            Completed
                        </button>
                        <button
                            @click="statusFilter = 'cancelled'; applyFilters()"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                                statusFilter === 'cancelled'
                                    ? 'bg-gray-900 text-white'
                                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                            ]"
                        >
                            Cancelled
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Service</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="booking in bookings.data"
                            :key="booking.id"
                            class="group hover:bg-blue-50/30 transition-colors cursor-pointer"
                            @click="router.visit(route('vendor.bookings.show', booking.id))"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-semibold text-xs flex-shrink-0 shadow-sm">
                                        {{ booking.customer.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 text-sm truncate">{{ booking.customer.name }}</div>
                                        <div class="text-xs text-gray-400 truncate">{{ booking.customer.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 text-sm">{{ booking.service.name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ booking.offering.name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-800">{{ formatDate(booking.booking_date) }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ formatTime(booking.start_time) }} – {{ formatTime(booking.end_time) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-gray-900">${{ Number(booking.total_price).toFixed(2) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium', getStatusClasses(booking.status)]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', getStatusDot(booking.status)]"></span>
                                    {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <Link
                                    :href="route('vendor.bookings.show', booking.id)"
                                    class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-400 group-hover:text-blue-600 transition-colors"
                                    @click.stop
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </Link>
                            </td>
                        </tr>
                        <!-- Empty State -->
                        <tr v-if="bookings.data.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </div>
                                <h3 class="text-base font-semibold text-gray-900 mb-1">No bookings found</h3>
                                <p class="text-sm text-gray-500">Bookings will appear here once customers make reservations.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="bookings.total > bookings.per_page" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/30">
                    <div class="text-sm text-gray-500">
                        Showing <span class="font-medium text-gray-700">{{ bookings.from }}</span> to <span class="font-medium text-gray-700">{{ bookings.to }}</span> of <span class="font-medium text-gray-700">{{ bookings.total }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button
                            v-for="page in Math.ceil(bookings.total / bookings.per_page)"
                            :key="page"
                            @click="router.get(route('vendor.bookings.index'), { page: page, search: search, status: statusFilter }, { preserveState: true })"
                            :class="[
                                'w-9 h-9 rounded-lg text-sm font-medium transition-colors',
                                page === bookings.current_page
                                    ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md'
                                    : 'text-gray-600 hover:bg-white hover:shadow-sm'
                            ]"
                        >
                            {{ page }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </VendorLayout>
</template>
