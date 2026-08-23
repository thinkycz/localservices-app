<script setup>
import AppPagination from '@/Components/AppPagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import UiButton from '@/Components/UiButton.vue';
import UiCard from '@/Components/UiCard.vue';
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CircleDollarSign, MapPin, Plus, Search, Store, Wrench } from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps({
    shops: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, required: true },
});

const search = ref(props.filters.q || '');
const status = ref(props.filters.status || '');
const sort = ref(props.filters.sort || 'newest');

function applyFilters() {
    router.get(route('vendor.shops.index'), { q: search.value, status: status.value, sort: sort.value }, { preserveState: true, replace: true });
}

function toggleAvailability(shop) {
    router.post(route('vendor.shops.toggle', shop.id), {}, { preserveScroll: true });
}

function removeShop(shop) {
    if (window.confirm(`Opravdu chcete smazat provozovnu „${shop.name}“ včetně jejích služeb? Tuto akci nelze vrátit.`)) {
        router.delete(route('vendor.shops.destroy', shop.id));
    }
}

function location(shop) {
    if (shop.is_online_only) return 'Pouze online';
    return [shop.address, shop.city].filter(Boolean).join(', ') || 'Adresa není doplněna';
}
</script>

<template>
    <Head title="Provozovny" />
    <VendorLayout activePage="shops">
        <div class="space-y-6">
            <PageHeader title="Provozovny a služby" description="Spravujte místa, jejich dostupnost a nabídku služeb.">
                <template #actions><UiButton :href="route('vendor.shops.create')"><Plus :size="18" /> Přidat provozovnu</UiButton></template>
            </PageHeader>

            <div class="grid min-w-0 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <UiCard v-for="item in [
                    { label: 'Provozovny', value: stats.total_shops ?? 0, icon: Store },
                    { label: 'Aktivní', value: stats.available_shops ?? 0, icon: MapPin },
                    { label: 'Služby', value: stats.total_services ?? 0, icon: Wrench },
                    { label: 'Součet ceníkových cen', value: stats.potential_revenue ?? '0,00 CZK', icon: CircleDollarSign },
                ]" :key="item.label" padding="sm" class="min-w-0">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-brand-50 text-brand-700"><component :is="item.icon" :size="19" /></span>
                        <div class="min-w-0"><p class="text-xs font-bold uppercase tracking-wide text-muted">{{ item.label }}</p><p class="mt-1 break-words text-xl font-extrabold text-ink">{{ item.value }}</p></div>
                    </div>
                </UiCard>
            </div>

            <UiCard padding="sm">
                <form class="grid gap-3 md:grid-cols-[minmax(0,1fr)_auto_auto_auto]" @submit.prevent="applyFilters">
                    <label class="relative min-w-0">
                        <span class="sr-only">Hledat provozovnu</span>
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" :size="18" />
                        <input v-model="search" class="ui-field pl-11" type="search" placeholder="Název provozovny…" />
                    </label>
                    <select v-model="status" class="ui-field md:w-40" aria-label="Filtrovat podle stavu" @change="applyFilters">
                        <option value="">Všechny stavy</option><option value="available">Aktivní</option><option value="unavailable">Neaktivní</option>
                    </select>
                    <select v-model="sort" class="ui-field md:w-44" aria-label="Řazení" @change="applyFilters">
                        <option value="newest">Nejnovější</option><option value="oldest">Nejstarší</option><option value="name_asc">Název A–Z</option><option value="name_desc">Název Z–A</option>
                    </select>
                    <UiButton type="submit" variant="secondary">Použít</UiButton>
                </form>
            </UiCard>

            <EmptyState v-if="!shops.data?.length" title="Zatím tu není žádná provozovna" description="Přidejte první provozovnu, otevírací dobu a služby. Zákazníci ji pak najdou ve vyhledávání.">
                <template #icon><Store :size="23" /></template>
                <template #actions><UiButton :href="route('vendor.shops.create')"><Plus :size="18" /> Přidat provozovnu</UiButton></template>
            </EmptyState>

            <template v-else>
                <div class="grid gap-4 md:hidden">
                    <UiCard v-for="shop in shops.data" :key="shop.id" padding="sm">
                        <div class="flex items-start gap-3">
                            <img v-if="shop.cover_image_url" :src="shop.cover_image_url" alt="" class="h-16 w-20 flex-none rounded-xl object-cover" />
                            <span v-else class="flex h-16 w-20 flex-none items-center justify-center rounded-xl bg-brand-50 text-brand-700"><Store :size="24" /></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2"><Link :href="route('vendor.shops.show', shop.id)" class="break-words font-extrabold text-ink hover:text-brand-700">{{ shop.name }}</Link><StatusBadge :tone="shop.is_available ? 'success' : 'neutral'">{{ shop.is_available ? 'Aktivní' : 'Neaktivní' }}</StatusBadge></div>
                                <p class="mt-1 text-sm text-muted">{{ shop.category?.name || 'Bez kategorie' }}</p>
                                <p class="mt-1 break-words text-xs text-muted">{{ location(shop) }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2 border-t border-line pt-4">
                            <UiButton :href="route('vendor.shops.show', shop.id)" variant="secondary" size="sm">Spravovat</UiButton>
                            <UiButton variant="ghost" size="sm" @click="toggleAvailability(shop)">{{ shop.is_available ? 'Skrýt' : 'Aktivovat' }}</UiButton>
                            <UiButton variant="ghost" size="sm" class="text-danger" @click="removeShop(shop)">Smazat</UiButton>
                        </div>
                    </UiCard>
                </div>

                <div class="hidden overflow-hidden rounded-2xl border border-line bg-white md:block">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left">
                            <thead class="border-b border-line bg-gray-50 text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-5 py-3">Provozovna</th><th class="px-5 py-3">Místo</th><th class="px-5 py-3">Služby</th><th class="px-5 py-3">Stav</th><th class="px-5 py-3 text-right">Akce</th></tr></thead>
                            <tbody class="divide-y divide-line">
                                <tr v-for="shop in shops.data" :key="shop.id" class="hover:bg-brand-50/30">
                                    <td class="px-5 py-4"><Link :href="route('vendor.shops.show', shop.id)" class="font-extrabold text-ink hover:text-brand-700">{{ shop.name }}</Link><p class="mt-1 text-xs text-muted">{{ shop.category?.name || 'Bez kategorie' }} · {{ shop.currency }}</p></td>
                                    <td class="max-w-xs px-5 py-4 text-sm text-muted">{{ location(shop) }}</td>
                                    <td class="px-5 py-4 text-sm font-bold text-ink">{{ shop.services?.length || 0 }}</td>
                                    <td class="px-5 py-4"><StatusBadge :tone="shop.is_available ? 'success' : 'neutral'">{{ shop.is_available ? 'Aktivní' : 'Neaktivní' }}</StatusBadge></td>
                                    <td class="px-5 py-4"><div class="flex justify-end gap-2"><UiButton :href="route('vendor.shops.show', shop.id)" variant="secondary" size="sm">Spravovat</UiButton><button class="ui-icon-button text-muted" :aria-label="shop.is_available ? 'Skrýt provozovnu' : 'Aktivovat provozovnu'" @click="toggleAvailability(shop)"><MapPin :size="17" /></button></div></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <AppPagination :meta="shops" :links="shops.links || []" />
            </template>
        </div>
    </VendorLayout>
</template>
