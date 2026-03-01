<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterSidebar from '@/Components/FilterSidebar.vue';
import ServiceCard from '@/Components/ServiceCard.vue';
import AppPagination from '@/Components/AppPagination.vue';

const props = defineProps({
    services:   { type: Object, required: true },
    categories: { type: Array,  default: () => [] },
    filters:    { type: Object, default: () => ({}) },
});

const currentSort = ref(props.filters.sort ?? 'recommended');

const sortOptions = [
    { value: 'recommended', label: 'Recommended' },
    { value: 'nearest',     label: 'Nearest' },
    { value: 'cheapest',    label: 'Cheapest' },
];

function setSort(value) {
    currentSort.value = value;
    router.get(route('services.index'), { ...props.filters, sort: value }, { preserveScroll: true });
}

const searchTitle = computed(() => {
    if (props.filters.q) return `"${props.filters.q}"`;
    if (props.filters.categories) {
        const cats = Array.isArray(props.filters.categories) ? props.filters.categories : [props.filters.categories];
        return cats.map(s => s.charAt(0).toUpperCase() + s.slice(1)).join(', ');
    }
    return 'All Services';
});
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">

            <!-- Page Header -->
            <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-5">
                    <h1 class="text-2xl font-bold text-white mb-1">{{ searchTitle }}</h1>
                    <p class="text-sm text-blue-200 mb-5">{{ services.total }} {{ services.total === 1 ? 'service' : 'services' }} available</p>

                    <!-- Filter + Sort row -->
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <!-- Filters -->
                        <FilterSidebar :categories="categories" :filters="filters" />

                        <!-- Sort -->
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs font-semibold text-blue-300 mr-0.5 hidden sm:block">{{ $t('Sort:') }}</span>
                            <button
                                v-for="opt in sortOptions"
                                :key="opt.value"
                                @click="setSort(opt.value)"
                                class="px-4 py-2 text-sm font-semibold rounded-full border-2 transition-all"
                                :class="currentSort === opt.value
                                    ? 'bg-white border-white text-gray-900'
                                    : 'bg-transparent border-white/30 text-blue-100 hover:border-white/50 hover:text-white'"
                            >
                                {{ opt.label }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

                <!-- Service Cards — 2-column grid -->
                <div v-if="services.data.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <ServiceCard
                        v-for="service in services.data"
                        :key="service.id"
                        :service="service"
                    />
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl border border-gray-100 p-16 text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-5">
                        <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $t('No services found') }}</h3>
                    <p class="text-gray-500 text-sm max-w-sm mx-auto">{{ $t('Try adjusting your filters or search terms.') }}</p>
                </div>

                <!-- Pagination -->
                <div v-if="services.data.length > 0">
                    <div v-if="services.current_page < services.last_page" class="flex justify-center mt-8">
                        <Link
                            :href="services.next_page_url"
                            class="px-8 py-3 bg-white border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm"
                        >{{ $t('Load More') }}</Link>
                    </div>

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
    </AppLayout>
</template>
