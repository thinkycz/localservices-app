<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import UiButton from '@/Components/UiButton.vue';
import UiCard from '@/Components/UiCard.vue';
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, CircleDollarSign, Search, UserPlus, Users } from '@lucide/vue';
import { computed, ref } from 'vue';

const props = defineProps({
    customers: { type: Array, required: true },
    meta: { type: Object, required: true },
    filters: { type: Object, required: true },
    stats: { type: Object, required: true },
});

const search = ref(props.filters.search || '');
const filter = ref(props.filters.filter || 'all');
const lastPage = computed(() => Math.max(1, Math.ceil(Number(props.meta.total || 0) / Number(props.meta.per_page || 10))));

function visit(page = 1) {
    router.get(route('vendor.customers.index'), { page, search: search.value, filter: filter.value }, { preserveState: true, replace: true });
}

function resetFilters() { search.value = ''; filter.value = 'all'; visit(); }
function formatDate(value) { return value ? new Intl.DateTimeFormat('cs-CZ', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(`${String(value).slice(0, 10)}T12:00:00`)) : '—'; }
</script>

<template>
    <Head title="Zákazníci" />
    <VendorLayout activePage="customers">
        <div class="space-y-6">
            <PageHeader title="Zákazníci" description="Přehled registrovaných zákazníků, kteří si u vás už rezervovali službu. Hosté zůstávají u jednotlivých rezervací." />

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <UiCard v-for="item in [
                    { label: 'Zákazníci ve výběru', value: stats.total_customers ?? 0, icon: Users },
                    { label: 'První rezervace', value: stats.new_customers ?? 0, icon: UserPlus },
                    { label: 'Vracející se', value: stats.returning_customers ?? 0, icon: Users },
                    { label: 'Hodnota nezrušených rezervací', value: stats.total_revenue ?? '0,00 CZK', icon: CircleDollarSign },
                ]" :key="item.label" padding="sm" class="min-w-0"><div class="flex gap-3"><span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-brand-50 text-brand-700"><component :is="item.icon" :size="19" /></span><div class="min-w-0"><p class="text-xs font-bold uppercase tracking-wide text-muted">{{ item.label }}</p><p class="mt-1 break-words text-xl font-extrabold text-ink">{{ item.value }}</p></div></div></UiCard>
            </div>

            <UiCard padding="sm">
                <form class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto_auto]" @submit.prevent="visit()">
                    <label class="relative"><span class="sr-only">Hledat zákazníka</span><Search class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" :size="18" /><input v-model="search" type="search" class="ui-field pl-11" placeholder="Jméno nebo e-mail…" /></label>
                    <select v-model="filter" class="ui-field sm:w-48" aria-label="Typ zákazníka" @change="visit()"><option value="all">Všichni</option><option value="new">První rezervace</option><option value="returning">Vracející se</option></select>
                    <UiButton type="submit" variant="secondary">Hledat</UiButton>
                </form>
            </UiCard>

            <EmptyState v-if="!customers.length" title="Žádní odpovídající zákazníci" description="Změňte hledání, nebo vyčkejte, až první registrovaný zákazník vytvoří rezervaci.">
                <template #icon><Users :size="23" /></template><template #actions><UiButton variant="secondary" @click="resetFilters">Vymazat filtry</UiButton></template>
            </EmptyState>

            <template v-else>
                <div class="grid gap-3 md:hidden">
                    <Link v-for="customer in customers" :key="customer.id" :href="route('vendor.customers.show', customer.id)" class="rounded-2xl border border-line bg-white p-4 transition hover:border-brand-300 hover:shadow-soft">
                        <div class="flex gap-3"><span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-brand-50 text-sm font-extrabold text-brand-800">{{ customer.avatar_initials }}</span><div class="min-w-0"><p class="break-words font-extrabold text-ink">{{ customer.name }}</p><p class="mt-1 break-all text-sm text-muted">{{ customer.email }}</p></div></div>
                        <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-line pt-4 text-sm"><div><dt class="text-xs font-bold uppercase text-muted">Rezervace</dt><dd class="mt-1 font-bold text-ink">{{ customer.total_bookings }}</dd></div><div><dt class="text-xs font-bold uppercase text-muted">Nezrušené</dt><dd class="mt-1 break-words font-bold text-ink">{{ customer.total_spent }}</dd></div><div class="col-span-2"><dt class="text-xs font-bold uppercase text-muted">Naposledy</dt><dd class="mt-1 text-ink">{{ formatDate(customer.last_booking_date) }}</dd></div></dl>
                    </Link>
                </div>
                <div class="hidden overflow-hidden rounded-2xl border border-line bg-white md:block"><div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left"><thead class="border-b border-line bg-gray-50 text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-5 py-3">Zákazník</th><th class="px-5 py-3">Kontakt</th><th class="px-5 py-3">Rezervace</th><th class="px-5 py-3">Nezrušené</th><th class="px-5 py-3">Naposledy</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y divide-line"><tr v-for="customer in customers" :key="customer.id" class="hover:bg-brand-50/30"><td class="px-5 py-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-brand-50 text-xs font-extrabold text-brand-800">{{ customer.avatar_initials }}</span><p class="font-extrabold text-ink">{{ customer.name }}</p></div></td><td class="px-5 py-4"><p class="text-sm text-ink">{{ customer.email }}</p><p class="mt-1 text-xs text-muted">{{ customer.phone === 'N/A' ? 'Telefon neuveden' : customer.phone }}</p></td><td class="px-5 py-4 text-sm font-bold text-ink">{{ customer.total_bookings }}</td><td class="px-5 py-4 text-sm font-bold text-ink">{{ customer.total_spent }}</td><td class="px-5 py-4 text-sm text-muted">{{ formatDate(customer.last_booking_date) }}</td><td class="px-5 py-4 text-right"><UiButton :href="route('vendor.customers.show', customer.id)" variant="secondary" size="sm">Detail</UiButton></td></tr></tbody></table></div></div>

                <nav v-if="lastPage > 1" class="flex flex-col items-center justify-between gap-3 sm:flex-row" aria-label="Stránkování zákazníků"><p class="text-sm text-muted">{{ meta.from }}–{{ meta.to }} z {{ meta.total }}</p><div class="flex gap-2"><UiButton variant="secondary" size="sm" :disabled="meta.current_page <= 1" @click="visit(meta.current_page - 1)"><ChevronLeft :size="17" /> Předchozí</UiButton><span class="flex min-h-9 items-center px-3 text-sm font-bold text-ink">{{ meta.current_page }} / {{ lastPage }}</span><UiButton variant="secondary" size="sm" :disabled="meta.current_page >= lastPage" @click="visit(meta.current_page + 1)">Další <ChevronRight :size="17" /></UiButton></div></nav>
            </template>
        </div>
    </VendorLayout>
</template>
