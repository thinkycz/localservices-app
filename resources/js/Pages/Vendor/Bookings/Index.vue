<script setup>
import AppPagination from '@/Components/AppPagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import UiButton from '@/Components/UiButton.vue';
import UiCard from '@/Components/UiCard.vue';
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CalendarCheck, CheckCircle2, CircleDollarSign, Clock3, Search } from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps({
    bookings: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const sort = ref(props.filters.sort || 'newest');

const statusMap = {
    pending: { label: 'Čeká na potvrzení', tone: 'warning' },
    confirmed: { label: 'Potvrzená', tone: 'brand' },
    completed: { label: 'Dokončená', tone: 'success' },
    cancelled: { label: 'Zrušená', tone: 'danger' },
};

function applyFilters() {
    router.get(route('vendor.bookings.index'), { search: search.value, status: status.value, date_from: dateFrom.value, date_to: dateTo.value, sort: sort.value }, { preserveState: true, replace: true });
}

function resetFilters() {
    search.value = ''; status.value = ''; dateFrom.value = ''; dateTo.value = ''; sort.value = 'newest'; applyFilters();
}

function formatDate(value) {
    if (!value) return '—';
    return new Intl.DateTimeFormat('cs-CZ', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(`${String(value).slice(0, 10)}T12:00:00`));
}

function formatTime(value) {
    return String(value || '').slice(0, 5) || '—';
}

function customerName(booking) {
    return booking.customer_display_name || booking.customer_name || booking.customer?.name || 'Zákazník bez jména';
}

function money(booking) {
    return new Intl.NumberFormat('cs-CZ', { style: 'currency', currency: booking.currency || booking.shop?.currency || 'CZK' }).format(Number(booking.price_amount ?? booking.total_price ?? booking.service?.price ?? 0));
}
</script>

<template>
    <Head title="Rezervace" />
    <VendorLayout activePage="bookings">
        <div class="space-y-6">
            <PageHeader title="Rezervace" :description="`${stats.total ?? 0} rezervací celkem. Potvrďte nové požadavky a dokončete pouze proběhlé termíny.`" />

            <div class="grid min-w-0 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <UiCard v-for="item in [
                    { label: 'Čeká na potvrzení', value: stats.pending ?? 0, icon: Clock3 },
                    { label: 'Potvrzené', value: stats.confirmed ?? 0, icon: CalendarCheck },
                    { label: 'Dokončené', value: stats.completed ?? 0, icon: CheckCircle2 },
                    { label: 'Hodnota nezrušených podle provozoven', value: stats.total_revenue ?? '0,00 CZK', icon: CircleDollarSign },
                ]" :key="item.label" padding="sm" class="min-w-0"><div class="flex gap-3"><span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-brand-50 text-brand-700"><component :is="item.icon" :size="19" /></span><div class="min-w-0"><p class="text-xs font-bold uppercase tracking-wide text-muted">{{ item.label }}</p><p class="mt-1 break-words text-xl font-extrabold text-ink">{{ item.value }}</p></div></div></UiCard>
            </div>

            <UiCard padding="sm">
                <form class="grid gap-3 lg:grid-cols-[minmax(12rem,1fr)_auto_auto_auto_auto]" @submit.prevent="applyFilters">
                    <label class="relative min-w-0"><span class="sr-only">Hledat zákazníka</span><Search class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" :size="18" /><input v-model="search" type="search" class="ui-field pl-11" placeholder="Jméno nebo e-mail…" /></label>
                    <select v-model="status" class="ui-field lg:w-48" aria-label="Stav rezervace" @change="applyFilters"><option value="">Všechny stavy</option><option value="pending">Čekající</option><option value="confirmed">Potvrzené</option><option value="completed">Dokončené</option><option value="cancelled">Zrušené</option></select>
                    <label><span class="sr-only">Od data</span><input v-model="dateFrom" type="date" class="ui-field" @change="applyFilters" /></label>
                    <label><span class="sr-only">Do data</span><input v-model="dateTo" type="date" class="ui-field" @change="applyFilters" /></label>
                    <select v-model="sort" class="ui-field lg:w-40" aria-label="Řazení" @change="applyFilters"><option value="newest">Nejnovější</option><option value="oldest">Nejstarší</option><option value="date_asc">Termín vzestupně</option><option value="date_desc">Termín sestupně</option></select>
                    <div class="flex gap-2 lg:col-span-5 lg:justify-end"><UiButton type="button" variant="ghost" @click="resetFilters">Vymazat filtry</UiButton><UiButton type="submit" variant="secondary">Použít</UiButton></div>
                </form>
            </UiCard>

            <EmptyState v-if="!bookings.data?.length" title="Žádné odpovídající rezervace" description="Změňte filtry, nebo vyčkejte na první rezervaci od zákazníka.">
                <template #icon><CalendarCheck :size="23" /></template><template #actions><UiButton variant="secondary" @click="resetFilters">Vymazat filtry</UiButton></template>
            </EmptyState>

            <template v-else>
                <div class="grid gap-3 md:hidden">
                    <Link v-for="booking in bookings.data" :key="booking.id" :href="route('vendor.bookings.show', booking.id)" class="rounded-2xl border border-line bg-white p-4 transition hover:border-brand-300 hover:shadow-soft">
                        <div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="break-words font-extrabold text-ink">{{ customerName(booking) }}</p><p class="mt-1 text-sm text-muted">{{ booking.service?.name || 'Služba' }}</p></div><StatusBadge :tone="statusMap[booking.status]?.tone || 'neutral'">{{ statusMap[booking.status]?.label || booking.status }}</StatusBadge></div>
                        <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-line pt-4 text-sm"><div><dt class="text-xs font-bold uppercase text-muted">Termín</dt><dd class="mt-1 font-semibold text-ink">{{ formatDate(booking.booking_date) }} · {{ formatTime(booking.start_time) }}</dd></div><div><dt class="text-xs font-bold uppercase text-muted">Cena</dt><dd class="mt-1 font-semibold text-ink">{{ money(booking) }}</dd></div><div class="col-span-2"><dt class="text-xs font-bold uppercase text-muted">Provozovna</dt><dd class="mt-1 text-muted">{{ booking.shop?.name || '—' }}</dd></div></dl>
                    </Link>
                </div>

                <div class="hidden overflow-hidden rounded-2xl border border-line bg-white md:block"><div class="overflow-x-auto"><table class="w-full min-w-[860px] text-left"><thead class="border-b border-line bg-gray-50 text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-5 py-3">Zákazník</th><th class="px-5 py-3">Služba</th><th class="px-5 py-3">Termín</th><th class="px-5 py-3">Cena</th><th class="px-5 py-3">Stav</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y divide-line"><tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-brand-50/30"><td class="px-5 py-4"><p class="font-extrabold text-ink">{{ customerName(booking) }}</p><p class="mt-1 text-xs text-muted">{{ booking.customer_contact_email || booking.customer_email || booking.customer?.email }}</p></td><td class="px-5 py-4"><p class="text-sm font-semibold text-ink">{{ booking.service?.name || 'Služba' }}</p><p class="mt-1 text-xs text-muted">{{ booking.shop?.name }}</p></td><td class="px-5 py-4 text-sm text-muted">{{ formatDate(booking.booking_date) }}<br><span class="font-bold text-ink">{{ formatTime(booking.start_time) }}</span></td><td class="px-5 py-4 text-sm font-bold text-ink">{{ money(booking) }}</td><td class="px-5 py-4"><StatusBadge :tone="statusMap[booking.status]?.tone || 'neutral'">{{ statusMap[booking.status]?.label || booking.status }}</StatusBadge></td><td class="px-5 py-4 text-right"><UiButton :href="route('vendor.bookings.show', booking.id)" variant="secondary" size="sm">Detail</UiButton></td></tr></tbody></table></div></div>
                <AppPagination :meta="bookings" :links="bookings.links || []" />
            </template>
        </div>
    </VendorLayout>
</template>
