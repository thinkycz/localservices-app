<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Check, ChevronDown, Star, X } from '@lucide/vue';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const selectedCategories = ref(props.filters.categories
    ? (Array.isArray(props.filters.categories) ? [...props.filters.categories] : String(props.filters.categories).split(','))
    : []);
const minRating = ref(props.filters.min_rating ? Number(props.filters.min_rating) : null);
const openDropdown = ref(null);
const filterBarRef = ref(null);

const activeFilterCount = computed(() => selectedCategories.value.length + (minRating.value ? 1 : 0));
const categoryLabel = computed(() => {
    if (selectedCategories.value.length !== 1) return null;
    const category = props.categories.find((item) => item.slug === selectedCategories.value[0]);
    return category?.name ?? selectedCategories.value[0];
});

function toggleDropdown(name) {
    openDropdown.value = openDropdown.value === name ? null : name;
}

function applyFilters() {
    const params = {};
    if (props.filters.q) params.q = props.filters.q;
    if (selectedCategories.value.length) params.categories = selectedCategories.value;
    if (minRating.value) params.min_rating = minRating.value;
    if (props.filters.sort) params.sort = props.filters.sort;
    router.get(route('shops.index'), params, { preserveScroll: true, preserveState: true });
}

function toggleCategory(slug) {
    const index = selectedCategories.value.indexOf(slug);
    if (index === -1) selectedCategories.value.push(slug);
    else selectedCategories.value.splice(index, 1);
    applyFilters();
}

function setMinRating(rating) {
    minRating.value = minRating.value === rating ? null : rating;
    applyFilters();
}

function clearAll() {
    selectedCategories.value = [];
    minRating.value = null;
    openDropdown.value = null;
    router.get(route('shops.index'), props.filters.q ? { q: props.filters.q } : {}, { preserveScroll: true });
}

function handleClickOutside(event) {
    if (filterBarRef.value && !filterBarRef.value.contains(event.target)) openDropdown.value = null;
}

function closeOnEscape(event) {
    if (event.key === 'Escape') openDropdown.value = null;
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', closeOnEscape);
});
</script>

<template>
    <div ref="filterBarRef" class="flex flex-wrap items-center gap-2">
        <div class="relative">
            <button
                type="button"
                class="inline-flex min-h-11 items-center gap-2 rounded-xl border px-4 text-sm font-bold shadow-sm transition"
                :class="selectedCategories.length ? 'border-brand-200 bg-brand-50 text-brand-800' : 'border-line bg-white text-ink hover:border-brand-300'"
                :aria-expanded="openDropdown === 'category'"
                @click.stop="toggleDropdown('category')"
            >
                <span v-if="categoryLabel">{{ categoryLabel }}</span>
                <span v-else-if="selectedCategories.length">{{ selectedCategories.length }} {{ $t('categories') }}</span>
                <span v-else>{{ $t('Category') }}</span>
                <ChevronDown :size="16" class="transition" :class="openDropdown === 'category' ? 'rotate-180' : ''" aria-hidden="true" />
            </button>

            <Transition enter-active-class="transition duration-150" enter-from-class="translate-y-1 opacity-0" leave-active-class="transition duration-100" leave-to-class="translate-y-1 opacity-0">
                <div v-if="openDropdown === 'category'" class="absolute left-0 top-full z-30 mt-2 w-[min(20rem,calc(100vw-2rem))] rounded-2xl border border-line bg-white p-2 shadow-lift">
                    <p v-if="categories.length === 0" class="px-3 py-4 text-sm text-muted">{{ $t('No categories available') }}</p>
                    <button
                        v-for="category in categories"
                        v-else
                        :key="category.id"
                        type="button"
                        role="checkbox"
                        :aria-checked="selectedCategories.includes(category.slug)"
                        class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold transition hover:bg-gray-50"
                        :class="selectedCategories.includes(category.slug) ? 'bg-brand-50 text-brand-800' : 'text-ink'"
                        @click="toggleCategory(category.slug)"
                    >
                        <span class="flex h-5 w-5 flex-none items-center justify-center rounded-md border" :class="selectedCategories.includes(category.slug) ? 'border-brand-600 bg-brand-600 text-white' : 'border-line bg-white'">
                            <Check v-if="selectedCategories.includes(category.slug)" :size="14" :stroke-width="3" aria-hidden="true" />
                        </span>
                        <span class="truncate">{{ category.name }}</span>
                    </button>
                </div>
            </Transition>
        </div>

        <div class="relative">
            <button
                type="button"
                class="inline-flex min-h-11 items-center gap-2 rounded-xl border px-4 text-sm font-bold shadow-sm transition"
                :class="minRating ? 'border-amber-200 bg-amber-50 text-amber-900' : 'border-line bg-white text-ink hover:border-brand-300'"
                :aria-expanded="openDropdown === 'rating'"
                @click.stop="toggleDropdown('rating')"
            >
                <Star v-if="minRating" :size="16" class="fill-accent text-accent" aria-hidden="true" />
                <span>{{ minRating ? `${minRating}+` : $t('Rating') }}</span>
                <ChevronDown :size="16" class="transition" :class="openDropdown === 'rating' ? 'rotate-180' : ''" aria-hidden="true" />
            </button>

            <Transition enter-active-class="transition duration-150" enter-from-class="translate-y-1 opacity-0" leave-active-class="transition duration-100" leave-to-class="translate-y-1 opacity-0">
                <div v-if="openDropdown === 'rating'" class="absolute left-0 top-full z-30 mt-2 w-48 rounded-2xl border border-line bg-white p-2 shadow-lift">
                    <button
                        v-for="rating in [4, 3, 2]"
                        :key="rating"
                        type="button"
                        role="checkbox"
                        :aria-checked="minRating === rating"
                        class="flex min-h-11 w-full items-center gap-2 rounded-xl px-3 text-left text-sm font-bold transition hover:bg-gray-50"
                        :class="minRating === rating ? 'bg-amber-50 text-amber-900' : 'text-ink'"
                        @click="setMinRating(rating)"
                    >
                        <Star :size="16" class="fill-accent text-accent" aria-hidden="true" />
                        {{ rating }}+ {{ $t('Stars') }}
                    </button>
                </div>
            </Transition>
        </div>

        <button v-if="activeFilterCount" type="button" class="inline-flex min-h-11 items-center gap-1.5 rounded-xl px-3 text-xs font-bold text-danger transition hover:bg-red-50" @click="clearAll">
            <X :size="16" aria-hidden="true" />{{ $t('Clear all') }}
        </button>
    </div>
</template>
