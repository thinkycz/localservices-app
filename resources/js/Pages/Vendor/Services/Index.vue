<script setup>
import { ref } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import VendorLayout from '@/Layouts/VendorLayout.vue';

const props = defineProps({
    services: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, required: true },
});

const searchQuery = ref(props.filters.q || '');
const activeStatus = ref(props.filters.status || 'all');

function handleSearch() {
    router.get(route('vendor.services.index'), {
        q: searchQuery.value,
        status: activeStatus.value,
        sort: props.filters.sort,
    }, { preserveState: true, replace: true });
}

function setStatus(status) {
    activeStatus.value = status;
    router.get(route('vendor.services.index'), {
        q: searchQuery.value,
        status: status,
        sort: props.filters.sort,
    }, { preserveState: true, replace: true });
}

function setSort(sort) {
    router.get(route('vendor.services.index'), {
        q: searchQuery.value,
        status: activeStatus.value,
        sort: sort,
    }, { preserveState: true, replace: true });
}

function toggleAvailability(serviceId) {
    router.post(route('vendor.services.toggle', serviceId));
}

function deleteService(serviceId) {
    if (!confirm('Are you sure you want to delete this service? This will also delete all associated offerings.')) return;
    router.delete(route('vendor.services.destroy', serviceId));
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price || 0);
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function getBadgeClasses(color) {
    const colors = {
        blue: 'bg-blue-50 text-blue-700 ring-blue-700/10',
        gray: 'bg-gray-50 text-gray-700 ring-gray-700/10',
        green: 'bg-green-50 text-green-700 ring-green-700/10',
    };
    return colors[color] || 'bg-gray-50 text-gray-700 ring-gray-700/10';
}
</script>

<template>
    <Head title="My Services" />

    <VendorLayout activePage="services">
        <div class="flex flex-col gap-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">Total Services</div>
                    <div class="text-2xl font-bold text-gray-900">{{ stats.total_services }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">Total Offerings</div>
                    <div class="text-2xl font-bold text-gray-900">{{ stats.total_offerings }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">Active Services</div>
                    <div class="text-2xl font-bold text-gray-900">{{ stats.available_services }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">Potential Revenue</div>
                    <div class="text-2xl font-bold text-gray-900">{{ formatPrice(stats.potential_revenue) }}</div>
                </div>
            </div>

            <!-- Search, Filter, and Add -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-center gap-4">
                    <div class="flex-1 relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search services by name..."
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @keyup.enter="handleSearch"
                        />
                    </div>

                    <div class="flex items-center gap-1">
                        <button
                            @click="setStatus('all')"
                            :class="['px-4 py-2 text-sm font-medium rounded-xl transition-colors', activeStatus === 'all' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700']"
                        >All</button>
                        <button
                            @click="setStatus('available')"
                            :class="['px-4 py-2 text-sm font-medium rounded-xl transition-colors', activeStatus === 'available' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700']"
                        >Active</button>
                        <button
                            @click="setStatus('unavailable')"
                            :class="['px-4 py-2 text-sm font-medium rounded-xl transition-colors', activeStatus === 'unavailable' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700']"
                        >Inactive</button>
                    </div>

                    <Link
                        :href="route('vendor.services.create')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-medium text-sm flex items-center gap-2 transition-colors shadow-sm flex-shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Service
                    </Link>
                </div>
            </div>

            <!-- Services Table -->
            <div v-if="services.data.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Service</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Offerings</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Starting Price</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="service in services.data"
                            :key="service.id"
                            class="group hover:bg-blue-50/30 transition-colors cursor-pointer"
                            @click="router.visit(route('vendor.services.show', service.id))"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-sm font-bold shadow-sm flex-shrink-0">
                                        {{ service.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 text-sm truncate max-w-[200px]">{{ service.name }}</div>
                                        <div v-if="service.badge" class="mt-0.5">
                                            <span :class="[getBadgeClasses(service.badge_color), 'text-[10px] font-bold px-1.5 py-0.5 rounded ring-1 ring-inset uppercase tracking-wider']">{{ service.badge }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">{{ service.category?.name || 'Uncategorized' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-900">{{ service.offerings?.length || 0 }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-green-600">
                                    {{ service.offerings?.length ? formatPrice(Math.min(...service.offerings.map(o => o.price))) : '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button
                                    @click.stop="toggleAvailability(service.id)"
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 ring-inset transition-colors',
                                        service.is_available
                                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 hover:bg-emerald-100'
                                            : 'bg-gray-50 text-gray-500 ring-gray-500/10 hover:bg-gray-100'
                                    ]"
                                >
                                    <span :class="['w-1.5 h-1.5 rounded-full', service.is_available ? 'bg-emerald-500' : 'bg-gray-400']"></span>
                                    {{ service.is_available ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-500">{{ formatDate(service.created_at) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1">
                                    <Link
                                        :href="route('vendor.services.show', service.id)"
                                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                        @click.stop
                                        title="Manage"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </Link>
                                    <button
                                        @click.stop="deleteService(service.id)"
                                        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Delete"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="py-16 text-center bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900 mb-1">No services yet</h3>
                <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">Start by adding your first service to attract customers.</p>
                <Link
                    :href="route('vendor.services.create')"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-medium text-sm rounded-xl hover:bg-blue-700 transition-colors shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Your First Service
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="services.last_page > 1" class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-4 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Showing <span class="font-medium text-gray-700">{{ services.from }}</span> to <span class="font-medium text-gray-700">{{ services.to }}</span> of <span class="font-medium text-gray-700">{{ services.total }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <Link
                        v-if="services.prev_page_url"
                        :href="services.prev_page_url"
                        class="px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >Previous</Link>
                    
                    <template v-for="page in services.last_page" :key="page">
                        <Link
                            v-if="page >= services.current_page - 2 && page <= services.current_page + 2"
                            :href="`?page=${page}`"
                            :class="[
                                'w-9 h-9 rounded-lg text-sm font-medium transition-colors flex items-center justify-center',
                                page === services.current_page ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'
                            ]"
                        >{{ page }}</Link>
                    </template>

                    <Link
                        v-if="services.next_page_url"
                        :href="services.next_page_url"
                        class="px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >Next</Link>
                </div>
            </div>
        </div>
    </VendorLayout>
</template>
