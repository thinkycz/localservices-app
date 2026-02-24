<script setup>
import { ref, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import VendorLayout from '@/Layouts/VendorLayout.vue';

const props = defineProps({
    services: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        required: true,
    },
});

const searchQuery = ref(props.filters.q || '');
const activeStatus = ref(props.filters.status || 'all');

// Search handler
function handleSearch() {
    router.get(route('vendor.services.index'), {
        q: searchQuery.value,
        status: activeStatus.value,
        sort: props.filters.sort,
    }, { preserveState: true, replace: true });
}

// Status filter
function setStatus(status) {
    activeStatus.value = status;
    router.get(route('vendor.services.index'), {
        q: searchQuery.value,
        status: status,
        sort: props.filters.sort,
    }, { preserveState: true, replace: true });
}

// Sort handler
function setSort(sort) {
    router.get(route('vendor.services.index'), {
        q: searchQuery.value,
        status: activeStatus.value,
        sort: sort,
    }, { preserveState: true, replace: true });
}

// Toggle availability
function toggleAvailability(serviceId) {
    router.post(route('vendor.services.toggle', serviceId), {}, {
        onSuccess: () => {
            // Page will reload with updated data
        },
    });
}

// Delete service
function deleteService(serviceId) {
    if (!confirm('Are you sure you want to delete this service? This will also delete all associated offerings.')) {
        return;
    }
    
    router.delete(route('vendor.services.destroy', serviceId), {
        onSuccess: () => {
            // Page will reload
        },
    });
}

// Format helpers
function formatPrice(price) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price || 0);
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

function getBadgeClasses(color) {
    const colors = {
        blue: 'bg-blue-100 text-blue-700',
        gray: 'bg-gray-100 text-gray-700',
        green: 'bg-green-100 text-green-700',
    };
    return colors[color] || 'bg-gray-100 text-gray-700';
}

function getStatusBadge(isAvailable) {
    return isAvailable 
        ? 'bg-green-50 text-green-600' 
        : 'bg-gray-50 text-gray-500';
}
</script>

<template>
    <Head title="My Services" />

    <VendorLayout activePage="services">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Services</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your services and offerings</p>
            </div>
            <Link
                :href="route('vendor.services.create')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-medium text-sm flex items-center gap-2 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Service
            </Link>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Total Services</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.total_services }}</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Total Offerings</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.total_offerings }}</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Active Services</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.available_services }}</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Potential Revenue</div>
                <div class="text-2xl font-bold text-gray-900">{{ formatPrice(stats.potential_revenue) }}</div>
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
                        placeholder="Search services by name..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @keyup.enter="handleSearch"
                    />
                </div>

                <!-- Status Filter -->
                <div class="flex items-center gap-2">
                    <button
                        @click="setStatus('all')"
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                            activeStatus === 'all'
                                ? 'bg-gray-900 text-white'
                                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                        ]"
                    >
                        All
                    </button>
                    <button
                        @click="setStatus('available')"
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                            activeStatus === 'available'
                                ? 'bg-gray-900 text-white'
                                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                        ]"
                    >
                        Active
                    </button>
                    <button
                        @click="setStatus('unavailable')"
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                            activeStatus === 'unavailable'
                                ? 'bg-gray-900 text-white'
                                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                        ]"
                    >
                        Inactive
                    </button>
                </div>

                <!-- Sort -->
                <select
                    :value="filters.sort || 'newest'"
                    @change="setSort($event.target.value)"
                    class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
                </select>
            </div>
        </div>

        <!-- Services Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Service</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Category</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Offerings</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Badge</th>
                        <th class="text-left px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="service in services.data"
                        :key="service.id"
                        class="border-b border-gray-50 hover:bg-gray-50 transition-colors"
                    >
                        <!-- Service Info -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                                    <img
                                        v-if="service.image"
                                        :src="service.image"
                                        :alt="service.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <svg v-else class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 text-sm">{{ service.name }}</div>
                                    <div class="text-xs text-gray-400 truncate max-w-[200px]">{{ service.description }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Category -->
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ service.category?.name || 'Uncategorized' }}</span>
                        </td>

                        <!-- Offerings Count -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900">{{ service.offerings?.length || 0 }}</span>
                                <span class="text-xs text-gray-400">services</span>
                            </div>
                            <div v-if="service.offerings?.length" class="text-xs text-gray-400 mt-0.5">
                                From {{ formatPrice(Math.min(...service.offerings.map(o => o.price))) }}
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4">
                            <button
                                @click="toggleAvailability(service.id)"
                                :class="[
                                    'px-2.5 py-1 text-xs font-medium rounded-full transition-colors',
                                    service.is_available
                                        ? 'bg-green-50 text-green-600 hover:bg-green-100'
                                        : 'bg-gray-50 text-gray-500 hover:bg-gray-100'
                                ]"
                            >
                                {{ service.is_available ? 'Active' : 'Inactive' }}
                            </button>
                        </td>

                        <!-- Badge -->
                        <td class="px-6 py-4">
                            <span
                                v-if="service.badge"
                                :class="[getBadgeClasses(service.badge_color), 'text-xs font-medium px-2.5 py-1 rounded-full']"
                            >
                                {{ service.badge }}
                            </span>
                            <span v-else class="text-xs text-gray-400">—</span>
                        </td>

                        <!-- Created -->
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-500">{{ formatDate(service.created_at) }}</span>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('vendor.services.edit', service.id)"
                                    class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </Link>
                                <button
                                    @click="deleteService(service.id)"
                                    class="inline-flex items-center gap-1 text-sm font-medium text-red-600 hover:text-red-700 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="services.data.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">No services yet</h3>
                            <p class="text-sm text-gray-500 mb-4">Start by adding your first service to attract customers.</p>
                            <Link
                                :href="route('vendor.services.create')"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Your First Service
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="services.last_page > 1" class="flex items-center justify-between mt-6">
            <div class="text-sm text-gray-500">
                Showing {{ services.from }} to {{ services.to }} of {{ services.total }} services
            </div>
            <div class="flex items-center gap-2">
                <Link
                    v-if="services.prev_page_url"
                    :href="services.prev_page_url"
                    class="px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    Previous
                </Link>
                
                <template v-for="page in services.last_page" :key="page">
                    <Link
                        v-if="page >= services.current_page - 2 && page <= services.current_page + 2"
                        :href="`?page=${page}`"
                        :class="[
                            'w-9 h-9 rounded-lg text-sm font-medium transition-colors flex items-center justify-center',
                            page === services.current_page
                                ? 'bg-blue-600 text-white'
                                : 'text-gray-600 hover:bg-gray-100'
                        ]"
                    >
                        {{ page }}
                    </Link>
                </template>

                <Link
                    v-if="services.next_page_url"
                    :href="services.next_page_url"
                    class="px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    Next
                </Link>
            </div>
        </div>
    </VendorLayout>
</template>

