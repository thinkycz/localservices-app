<script setup>
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    customers: { type: Array, required: true },
    meta: { type: Object, required: true },
    filters: { type: Object, required: true },
    stats: { type: Object, required: true },
});

const searchQuery = ref(props.filters.search || '');
const activeFilter = ref(props.filters.filter || 'all');

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
</script>

<template>
    <Head title="Customers" />

    <VendorLayout activePage="customers">
        <div class="flex flex-col gap-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-4 gap-4">
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
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-center gap-4">
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
                    
                    <div class="flex items-center gap-1">
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
                        <tr class="bg-gray-50/50">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Bookings</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Spent</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Booking</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Services</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="customer in customers"
                            :key="customer.id"
                            class="group hover:bg-blue-50/30 transition-colors cursor-pointer"
                            @click="router.visit(route('vendor.customers.show', customer.id))"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-semibold text-xs flex-shrink-0 shadow-sm">
                                        {{ customer.avatar_initials }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 text-sm truncate">{{ customer.name }}</div>
                                        <div class="text-xs text-gray-400 truncate">Since {{ formatDate(customer.first_booking_date) }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 truncate">{{ customer.email }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ customer.phone || '—' }}</div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-900">{{ customer.total_bookings }}</span>
                                    <span v-if="customer.cancelled_bookings > 0" class="text-xs text-red-500">({{ customer.cancelled_bookings }} cancelled)</span>
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ customer.completed_bookings }} completed
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-gray-900">{{ formatPrice(customer.total_spent) }}</span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">{{ formatDate(customer.last_booking_date) }}</span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="(service, idx) in customer.services_used.slice(0, 2)"
                                        :key="idx"
                                        class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xs font-medium rounded-md ring-1 ring-inset ring-blue-700/10"
                                    >
                                        {{ service }}
                                    </span>
                                    <span
                                        v-if="customer.services_used.length > 2"
                                        class="px-2 py-0.5 bg-gray-50 text-gray-500 text-xs font-medium rounded-md ring-1 ring-inset ring-gray-500/10"
                                    >
                                        +{{ customer.services_used.length - 2 }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <Link
                                    :href="route('vendor.customers.show', customer.id)"
                                    class="inline-flex items-center text-gray-400 group-hover:text-blue-600 transition-colors"
                                    @click.stop
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </Link>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="customers.length === 0">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-base font-semibold text-gray-900 mb-1">No customers found</h3>
                                <p class="text-sm text-gray-500">Customers will appear here once they book your services.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="meta.total > meta.per_page" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/30">
                    <div class="text-sm text-gray-500">
                        Showing <span class="font-medium text-gray-700">{{ meta.from }}</span> to <span class="font-medium text-gray-700">{{ meta.to }}</span> of <span class="font-medium text-gray-700">{{ meta.total }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button
                            v-for="page in Math.ceil(meta.total / meta.per_page)"
                            :key="page"
                            @click="changePage(page)"
                            :class="[
                                'w-9 h-9 rounded-lg text-sm font-medium transition-colors',
                                page === meta.current_page
                                    ? 'bg-blue-600 text-white shadow-sm'
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
