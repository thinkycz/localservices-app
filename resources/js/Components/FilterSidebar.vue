<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    filters:    { type: Object, default: () => ({}) },
});

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

// Dropdown state
const openDropdown = ref(null);

function toggleDropdown(name) {
    openDropdown.value = openDropdown.value === name ? null : name;
}

// Close on outside click
const filterBarRef = ref(null);
function handleClickOutside(e) {
    if (filterBarRef.value && !filterBarRef.value.contains(e.target)) {
        openDropdown.value = null;
    }
}
onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

const activeFilterCount = computed(() => {
    let c = 0;
    if (selectedCategories.value.length) c += selectedCategories.value.length;
    if (selectedPriceRanges.value.length) c += selectedPriceRanges.value.length;
    if (minRating.value) c += 1;
    return c;
});

function toggleCategory(slug) {
    const idx = selectedCategories.value.indexOf(slug);
    if (idx === -1) selectedCategories.value.push(slug);
    else selectedCategories.value.splice(idx, 1);
    applyFilters();
}

function togglePriceRange(val) {
    const idx = selectedPriceRanges.value.indexOf(val);
    if (idx === -1) selectedPriceRanges.value.push(val);
    else selectedPriceRanges.value.splice(idx, 1);
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
    openDropdown.value = null;
    router.get(route('services.index'), props.filters.q ? { q: props.filters.q } : {});
}

const priceLabels = ['$', '$$', '$$$', '$$$$'];

// Labels for the trigger buttons
const categoryLabel = computed(() => {
    if (!selectedCategories.value.length) return 'Category';
    if (selectedCategories.value.length === 1) {
        const cat = props.categories.find(c => c.slug === selectedCategories.value[0]);
        return cat?.name || selectedCategories.value[0];
    }
    return `${selectedCategories.value.length} categories`;
});

const priceLabel = computed(() => {
    if (!selectedPriceRanges.value.length) return 'Price';
    return selectedPriceRanges.value.map(v => '$'.repeat(v)).join(', ');
});

const ratingLabel = computed(() => {
    if (!minRating.value) return 'Rating';
    return `${minRating.value}+ Stars`;
});
</script>

<template>
    <div ref="filterBarRef" class="flex items-center gap-2 flex-wrap">

        <!-- Category Dropdown -->
        <div class="relative">
            <button
                @click.stop="toggleDropdown('category')"
                class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-full border-2 transition-all"
                :class="selectedCategories.length
                    ? 'bg-white border-white text-gray-900'
                    : openDropdown === 'category'
                        ? 'bg-white/10 border-white/50 text-white'
                        : 'bg-transparent border-white/30 text-blue-100 hover:border-white/50 hover:text-white'"
            >
                {{ categoryLabel }}
                <svg class="w-3.5 h-3.5 transition-transform" :class="openDropdown === 'category' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <transition
                enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0 translate-y-1"
            >
                <div v-if="openDropdown === 'category'" class="absolute top-full left-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-100 p-3 z-30 min-w-[200px]">
                    <div class="space-y-0.5">
                        <label
                            v-for="cat in categories"
                            :key="cat.id"
                            class="flex items-center gap-2.5 cursor-pointer px-2.5 py-2 rounded-lg transition-colors"
                            :class="selectedCategories.includes(cat.slug) ? 'bg-blue-50' : 'hover:bg-gray-50'"
                            @click="toggleCategory(cat.slug)"
                        >
                            <div
                                class="w-[18px] h-[18px] rounded flex items-center justify-center transition-all shrink-0"
                                :class="selectedCategories.includes(cat.slug)
                                    ? 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-sm border-transparent'
                                    : 'border-2 border-gray-300'"
                            >
                                <svg v-if="selectedCategories.includes(cat.slug)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-sm" :class="selectedCategories.includes(cat.slug) ? 'text-blue-700 font-semibold' : 'text-gray-700'">
                                {{ cat.name }}
                            </span>
                        </label>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Price Dropdown -->
        <div class="relative">
            <button
                @click.stop="toggleDropdown('price')"
                class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-full border-2 transition-all"
                :class="selectedPriceRanges.length
                    ? 'bg-white border-white text-gray-900'
                    : openDropdown === 'price'
                        ? 'bg-white/10 border-white/50 text-white'
                        : 'bg-transparent border-white/30 text-blue-100 hover:border-white/50 hover:text-white'"
            >
                {{ priceLabel }}
                <svg class="w-3.5 h-3.5 transition-transform" :class="openDropdown === 'price' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <transition
                enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0 translate-y-1"
            >
                <div v-if="openDropdown === 'price'" class="absolute top-full left-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-100 p-3 z-30 min-w-[200px]">
                    <div class="flex gap-2">
                        <button
                            v-for="(label, idx) in priceLabels"
                            :key="idx"
                            @click="togglePriceRange(idx + 1)"
                            class="flex-1 py-2.5 text-sm font-bold rounded-lg border-2 transition-all"
                            :class="selectedPriceRanges.includes(idx + 1)
                                ? 'bg-gradient-to-r from-blue-600 to-indigo-600 border-transparent text-white shadow-md'
                                : 'border-gray-200 text-gray-500 hover:border-blue-300 hover:text-blue-600'"
                        >
                            {{ label }}
                        </button>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Rating Dropdown -->
        <div class="relative">
            <button
                @click.stop="toggleDropdown('rating')"
                class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-full border-2 transition-all"
                :class="minRating
                    ? 'bg-white border-white text-gray-900'
                    : openDropdown === 'rating'
                        ? 'bg-white/10 border-white/50 text-white'
                        : 'bg-transparent border-white/30 text-blue-100 hover:border-white/50 hover:text-white'"
            >
                <svg v-if="minRating" class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                {{ ratingLabel }}
                <svg class="w-3.5 h-3.5 transition-transform" :class="openDropdown === 'rating' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <transition
                enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0 translate-y-1"
            >
                <div v-if="openDropdown === 'rating'" class="absolute top-full left-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-100 p-3 z-30 min-w-[180px]">
                    <div class="space-y-0.5">
                        <button
                            v-for="rating in [4, 3, 2]"
                            :key="rating"
                            @click="setMinRating(rating)"
                            class="flex items-center gap-2.5 w-full text-left px-2.5 py-2 rounded-lg transition-all"
                            :class="minRating === rating ? 'bg-amber-50' : 'hover:bg-gray-50'"
                        >
                            <div class="flex items-center gap-0.5">
                                <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5" viewBox="0 0 20 20"
                                    :fill="i <= rating ? '#F59E0B' : '#E5E7EB'">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold" :class="minRating === rating ? 'text-amber-700' : 'text-gray-500'">
                                {{ rating }}+ Stars
                            </span>
                        </button>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Clear All -->
        <button
            v-if="activeFilterCount > 0"
            @click="clearAll"
            class="flex items-center gap-1 px-3 py-2 text-xs font-semibold text-red-300 hover:text-red-200 hover:bg-white/10 rounded-full transition-colors"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>{{ $t('Clear all') }}</button>
    </div>
</template>
