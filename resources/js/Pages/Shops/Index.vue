<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Search, SlidersHorizontal } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppPagination from '@/Components/AppPagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import FilterSidebar from '@/Components/FilterSidebar.vue';
import ShopCard from '@/Components/ShopCard.vue';

const props = defineProps({
    shops: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const isEnglish = computed(() => page.props.locale === 'en');
const tr = (czech, english) => isEnglish.value ? english : czech;
const currentSort = ref(props.filters.sort ?? 'recommended');
const query = ref(props.filters.q ?? '');
const title = computed(() => props.filters.q
    ? tr(`Výsledky pro „${props.filters.q}“`, `Results for “${props.filters.q}”`)
    : tr('Místní služby', 'Local services'));

function search() {
    const q = query.value.trim();
    router.get(route('shops.index'), {
        ...props.filters,
        q: q || undefined,
    }, { preserveState: true });
}

function setSort(event) {
    currentSort.value = event.target.value;
    router.get(route('shops.index'), {
        ...props.filters,
        sort: currentSort.value,
    }, { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <AppLayout>
        <div class="border-b border-line bg-canvas">
            <div class="mx-auto max-w-7xl px-4 py-9 sm:px-6 lg:px-8">
                <Link :href="route('home')" class="mb-4 inline-flex min-h-11 items-center gap-2 rounded-xl text-sm font-bold text-brand-700 hover:text-brand-800">
                    <ArrowLeft :size="17" aria-hidden="true" /> {{ tr('Zpět na úvod', 'Back to home') }}
                </Link>
                <div class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight text-ink">{{ title }}</h1>
                        <p class="mt-2 text-sm text-muted">{{ tr('Nalezeno', 'Found') }} {{ shops.total }} {{ shops.total === 1 ? tr('podnik', 'provider') : tr('podniků', 'providers') }}.</p>
                    </div>

                    <form class="flex w-full max-w-xl gap-2" role="search" @submit.prevent="search">
                        <label for="results-search" class="sr-only">{{ tr('Služba nebo místo', 'Service or place') }}</label>
                        <div class="relative min-w-0 flex-1">
                            <Search class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" :size="19" aria-hidden="true" />
                            <input id="results-search" v-model="query" type="search" :placeholder="tr('Služba nebo místo', 'Service or place')" class="ui-field rounded-xl pl-11">
                        </div>
                        <button type="submit" class="ui-button ui-button-primary flex-none">{{ tr('Hledat', 'Search') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col justify-between gap-4 rounded-2xl border border-line bg-white p-4 shadow-sm sm:flex-row sm:items-center">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <SlidersHorizontal :size="19" aria-hidden="true" />
                    </span>
                    <FilterSidebar :categories="categories" :filters="filters" />
                </div>

                <div class="flex items-center gap-3 sm:flex-none">
                    <label for="shop-sort" class="text-sm font-bold text-muted">{{ tr('Řazení', 'Sort') }}</label>
                    <select id="shop-sort" :value="currentSort" class="ui-field min-w-40 rounded-xl" @change="setSort">
                        <option value="recommended">{{ tr('Doporučené', 'Recommended') }}</option>
                        <option value="cheapest">{{ tr('Nejnižší cenová úroveň', 'Lowest price level') }}</option>
                    </select>
                </div>
            </div>

            <div v-if="shops.data.length" class="grid gap-5 lg:grid-cols-2">
                <ShopCard v-for="shop in shops.data" :key="shop.id" :shop="shop" />
            </div>

            <EmptyState v-else :title="tr('Nic jsme nenašli', 'No results found')" :description="tr('Zkuste upravit hledaný výraz nebo odebrat některý filtr.', 'Try changing the search or removing a filter.')">
                <template #action>
                    <Link :href="route('shops.index')" class="ui-button ui-button-secondary">{{ tr('Vymazat hledání a filtry', 'Clear search and filters') }}</Link>
                </template>
            </EmptyState>

            <AppPagination
                v-if="shops.data.length && shops.last_page > 1"
                :meta="{
                    current_page: shops.current_page,
                    last_page: shops.last_page,
                    from: shops.from,
                    to: shops.to,
                    total: shops.total,
                }"
                :links="shops.links"
            />
        </main>
    </AppLayout>
</template>
