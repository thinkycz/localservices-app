<script setup>
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    customers: {
        type: Array,
        required: true,
    },
    meta: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
});

const searchQuery = ref(props.filters.search || '');
const activeFilter = ref(props.filters.filter || 'all');

// Search and filter handlers
function handleSearch() {
    router.get(route('vendor.customers.index'), {
        search: searchQuery.value,
        filter: activeFilter.value,
    }, { preserveState: true, replace: true });
}

function setFilter(filter) {
    activeFilter.value = filter;
    router.get(route('vendor.customers.index'), {
        search: searchQuery.value,
        filter: filter,
    }, { preserveState: true, replace: true });
}

function changePage(page) {
    router.get(route('vendor.customers.index'), {
        page: page,
        search: searchQuery.value,
        filter: activeFilter.value,
    }, { preserveState: true });
}

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

function getStatusBadge(status) {
    const badges = {
        pending: 'bg-amber-50 text-amber-600',
        confirmed: 'bg-blue-50 text-blue-600',
        completed: 'bg-green-50 text-green-600',
        cancelled: 'bg-red-50 text-red-600',
    };
    return badges[status] || 'bg-gray-50 text-gray-600';
}
</script>

<template>
    <Head title="Customers" />

    <VendorLayout activePage="customers">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Customers</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your customer base and view booking history</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Total Customers</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.total_customers }}</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">New Customers</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.new_customers }}</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Returning</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.returning_customers }}</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Total Revenue</div>
                <div class="text-2xl font-bold text-gray-900">{{ formatPrice(stats.total_revenue) }}</div>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
            <div class="flex items-center gap-4">
                <!-- Search -->
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search customers by name or email..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @keyup.enter="handleSearch"
                    />
                </div>
                
                <!-- Filter Buttons -->
                <div class="flex items-center gap-2">
                    <button
                        @click="setFilter('all')"
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                            activeFilter === 'all'
                                ? 'bg-gray-900 text-white'
                                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                        ]"
                    >
                        All
                    </button>
                    <button
                        @click="setFilter('new')"
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                            activeFilter === 'new'
                                ? 'bg-gray-900 text-white'
                                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                        ]"
                    >
                        New
                    </button>
                    <button
                        @click="setFilter('returning')"
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                            activeFilter === 'returning'
                                ? 'bg-gray-900 text-white'
                                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                        ]"
                    >
                        Returning
                    </button>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Customer</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Contact</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Bookings</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Total Spent</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Last Booking</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Services</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="customer in customers"
                        :key="customer.id"
                        class="border-b border-gray-50 hover:bg-gray-50 transition-colors"
                    >
                        <!-- Customer Info -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                                    {{ customer.avatar_initials }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 text-sm">{{ customer.name }}</div>
                                    <div class="text-xs text-gray-400">Customer since {{ formatDate(customer.first_booking_date) }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Contact -->
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600">{{ customer.email }}</div>
                            <div class="text-xs text-gray-400">{{ customer.phone }}</div>
                        </td>

                        <!-- Bookings -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900">{{ customer.total_bookings }}</span>
                                <span v-if="customer.cancelled_bookings > 0" class="text-xs text-red-500">({{ customer.cancelled_bookings }} cancelled)</span>
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ customer.completed_bookings }} completed
                            </div>
                        </td>

                        <!-- Total Spent -->
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-gray-900">{{ formatPrice(customer.total_spent) }}</span>
                        </td>

                        <!-- Last Booking -->
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ formatDate(customer.last_booking_date) }}</span>
                        </td>

                        <!-- Services -->
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="(service, idx) in customer.services_used.slice(0, 2)"
                                    :key="idx"
                                    class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-medium rounded"
                                >
                                    {{ service }}
                                </span>
                                <span
                                    v-if="customer.services_used.length > 2"
                                    class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-medium rounded"
                                >
                                    +{{ customer.services_used.length - 2 }}
                                </span>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4">
                            <Link
                                :href="route('vendor.customers.show', customer.id)"
                                class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors"
                            >
                                View
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </Link>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="customers.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">No customers yet</h3>
                            <p class="text-sm text-gray-500">Customers will appear here once they book your services.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="meta.total > meta.per_page" class="flex items-center justify-between mt-6">
            <div class="text-sm text-gray-500">
                Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} customers
            </div>
            <div class="flex items-center gap-2">
                <button
                    v-for="page in Math.ceil(meta.total / meta.per_page)"
                    :key="page"
                    @click="changePage(page)"
                    :class="[
                        'w-9 h-9 rounded-lg text-sm font-medium transition-colors',
                        page === meta.current_page
                            ? 'bg-blue-600 text-white'
                            : 'text-gray-600 hover:bg-gray-100'
                    ]"
                >
                    {{ page }}
                </button>
            </div>
        </div>
    </VendorLayout>
</template>

