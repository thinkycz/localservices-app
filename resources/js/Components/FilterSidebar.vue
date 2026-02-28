<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:filters']);

// Local filter state
const selectedCategories = ref(
    props.filters.categories
        ? (Array.isArray(props.filters.categories) ? props.filters.categories : props.filters.categories.split(','))
        : []
);

const selectedPriceRanges = ref(
    props.filters.price_range
        ? (Array.isArray(props.filters.price_range) ? props.filters.price_range.map(Number) : props.filters.price_range.split(',').map(Number))
        : []
);

const minRating = ref(props.filters.min_rating ? Number(props.filters.min_rating) : null);

function toggleCategory(slug) {
    const idx = selectedCategories.value.indexOf(slug);
    if (idx === -1) {
        selectedCategories.value.push(slug);
    } else {
        selectedCategories.value.splice(idx, 1);
    }
    applyFilters();
}

function togglePriceRange(val) {
    const idx = selectedPriceRanges.value.indexOf(val);
    if (idx === -1) {
        selectedPriceRanges.value.push(val);
    } else {
        selectedPriceRanges.value.splice(idx, 1);
    }
    applyFilters();
}

function setMinRating(val) {
    minRating.value = minRating.value === val ? null : val;
    applyFilters();
}

function applyFilters() {
    const params = {};
    if (props.filters.q) params.q = props.filters.q;
    if (selectedCategories.value.length) params.categories = selectedCategories.value;
    if (selectedPriceRanges.value.length) params.price_range = selectedPriceRanges.value;
    if (minRating.value) params.min_rating = minRating.value;
    if (props.filters.sort) params.sort = props.filters.sort;

    router.get(route('services.index'), params, { preserveScroll: true });
}

function clearAll() {
    selectedCategories.value = [];
    selectedPriceRanges.value = [];
    minRating.value = null;
    router.get(route('services.index'), props.filters.q ? { q: props.filters.q } : {});
}

const priceLabels = ['$', '$$', '$$$', '$$$$'];
</script>

<template>
    <aside class="w-64 shrink-0">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2 font-semibold text-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                </svg>
                Filters
            </div>
            <button
                @click="clearAll"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium"
            >
                Clear all
            </button>
        </div>

        <!-- Category -->
        <div class="mb-6">
            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Category</h4>
            <div class="space-y-2">
                <label
                    v-for="cat in categories"
                    :key="cat.id"
                    class="flex items-center gap-2.5 cursor-pointer group"
                >
                    <div
                        class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors"
                        :class="selectedCategories.includes(cat.slug)
                            ? 'bg-blue-600 border-blue-600'
                            : 'border-gray-300 group-hover:border-blue-400'"
                        @click="toggleCategory(cat.slug)"
                    >
                        <svg v-if="selectedCategories.includes(cat.slug)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span
                        class="text-sm text-gray-700 group-hover:text-gray-900"
                        @click="toggleCategory(cat.slug)"
                    >
                        {{ cat.name }}
                    </span>
                </label>
            </div>
        </div>

        <!-- Price Range -->
        <div class="mb-6">
            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Price Range</h4>
            <div class="flex gap-2">
                <button
                    v-for="(label, idx) in priceLabels"
                    :key="idx"
                    @click="togglePriceRange(idx + 1)"
                    class="flex-1 py-1.5 text-sm font-medium rounded-lg border transition-colors"
                    :class="selectedPriceRanges.includes(idx + 1)
                        ? 'bg-blue-600 border-blue-600 text-white'
                        : 'border-gray-300 text-gray-600 hover:border-blue-400 hover:text-blue-600'"
                >
                    {{ label }}
                </button>
            </div>
        </div>

        <!-- Rating -->
        <div class="mb-6">
            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Rating</h4>
            <div class="space-y-2">
                <button
                    v-for="rating in [4, 3]"
                    :key="rating"
                    @click="setMinRating(rating)"
                    class="flex items-center gap-2 w-full text-left group"
                >
                    <div class="flex items-center gap-0.5">
                        <svg v-for="i in 5" :key="i" class="w-4 h-4" viewBox="0 0 20 20"
                            :fill="i <= rating ? '#F59E0B' : '#D1D5DB'">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <span
                        class="text-sm transition-colors"
                        :class="minRating === rating ? 'text-blue-600 font-semibold' : 'text-gray-600 group-hover:text-gray-900'"
                    >
                        {{ rating }}.0+ Stars
                    </span>
                </button>
            </div>
        </div>


    </aside>
</template>
