<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSidebar from '@/Components/FilterSidebar.vue';
import ServiceCard from '@/Components/ServiceCard.vue';
import AppPagination from '@/Components/AppPagination.vue';

const props = defineProps({
    services: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const currentSort = ref(props.filters.sort ?? 'recommended');

const sortOptions = [
    { value: 'recommended', label: 'Recommended' },
    { value: 'nearest',     label: 'Nearest' },
    { value: 'cheapest',    label: 'Cheapest' },
];

function setSort(value) {
    currentSort.value = value;
    router.get(route('services.index'), {
        ...props.filters,
        sort: value,
    }, { preserveScroll: true });
}

const searchTitle = computed(() => {
    if (props.filters.q) return `'${props.filters.q}'`;
    if (props.filters.categories) {
        const cats = Array.isArray(props.filters.categories)
            ? props.filters.categories
            : [props.filters.categories];
        return cats.map(s => s.charAt(0).toUpperCase() + s.slice(1)).join(', ');
    }
    return 'All Services';
});

const locationLabel = computed(() => {
    return props.filters.city ? `${props.filters.city}, NY` : 'New York, NY';
});
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex gap-8">

                <!-- Sidebar -->
                <FilterSidebar
                    :categories="categories"
                    :filters="filters"
                />

                <!-- Main Content -->
                <div class="flex-1 min-w-0">

                    <!-- Results Header -->
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                {{ services.total }} results for {{ searchTitle }}
                            </h1>
                            <p class="text-sm text-gray-500 mt-0.5">
                                Showing top rated services in {{ locationLabel }}
                            </p>
                        </div>

                        <!-- Sort Buttons -->
                        <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-xl p-1 shrink-0">
                            <button
                                v-for="opt in sortOptions"
                                :key="opt.value"
                                @click="setSort(opt.value)"
                                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                                :class="currentSort === opt.value
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'text-gray-600 hover:bg-gray-100'"
                            >
                                {{ opt.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Service Cards -->
                    <div v-if="services.data.length > 0" class="space-y-4">
                        <ServiceCard
                            v-for="service in services.data"
                            :key="service.id"
                            :service="service"
                        />
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-20">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">No services found</h3>
                        <p class="text-gray-500 text-sm">Try adjusting your filters or search terms.</p>
                    </div>

                    <!-- Load More + Pagination -->
                    <div v-if="services.data.length > 0">
                        <!-- Load More Button -->
                        <div v-if="services.current_page < services.last_page" class="flex justify-center mt-8">
                            <Link
                                :href="services.next_page_url"
                                class="px-8 py-3 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                            >
                                Load More Results
                            </Link>
                        </div>

                        <!-- Pagination -->
                        <AppPagination
                            :meta="{
                                current_page: services.current_page,
                                last_page: services.last_page,
                                from: services.from,
                                to: services.to,
                                total: services.total,
                            }"
                            :links="services.links"
                        />
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
