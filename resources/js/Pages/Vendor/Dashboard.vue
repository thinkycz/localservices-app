<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import UiButton from '@/Components/UiButton.vue';
import UiCard from '@/Components/UiCard.vue';
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarCheck, CalendarClock, CalendarX, CircleDollarSign, Clock3, Store, UserPlus, Users, Wrench } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps({
    stats: { type: Array, default: () => [] },
    todayBookings: { type: Array, default: () => [] },
    weekStats: { type: Object, default: () => ({}) },
    servicePopularity: { type: Array, default: () => [] },
    monthlyRevenue: { type: Array, default: () => [] },
    recentBookings: { type: Array, default: () => [] },
    overview: { type: Object, default: () => ({}) },
});

const iconMap = { 'calendar-check': CalendarCheck, 'calendar-x': CalendarX, 'user-plus': UserPlus, cash: CircleDollarSign };
const labelMap = { 'Total Bookings': 'Všechny rezervace', Cancellations: 'Zrušené rezervace', 'New Customers': 'Noví zákazníci tento měsíc', Revenue: 'Hodnota nezrušených rezervací' };
const todayLabel = new Intl.DateTimeFormat('cs-CZ', { weekday: 'long', day: 'numeric', month: 'long' }).format(new Date());

const isNewAccount = computed(() => !props.stats.some((stat) => Number(stat.value) > 0) && props.recentBookings.length === 0);

const statusMap = {
    pending: { label: 'Čeká', tone: 'warning' }, PENDING: { label: 'Čeká', tone: 'warning' },
    confirmed: { label: 'Potvrzená', tone: 'brand' }, CONFIRMED: { label: 'Potvrzená', tone: 'brand' },
    completed: { label: 'Dokončená', tone: 'success' }, COMPLETED: { label: 'Dokončená', tone: 'success' },
    cancelled: { label: 'Zrušená', tone: 'danger' }, CANCELLED: { label: 'Zrušená', tone: 'danger' },
};

function formatDate(value) { return value ? new Intl.DateTimeFormat('cs-CZ', { day: 'numeric', month: 'short' }).format(new Date(`${value}T12:00:00`)) : '—'; }
function monthLabel(value) { return ({ Jan: 'Led', Feb: 'Úno', Mar: 'Bře', Apr: 'Dub', May: 'Kvě', Jun: 'Čvn', Jul: 'Čvc', Aug: 'Srp', Sep: 'Zář', Oct: 'Říj', Nov: 'Lis', Dec: 'Pro' })[value] || value; }

function europeanTime(value) {
    const string = String(value || '').trim();
    const match = string.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    if (!match) return string.slice(0, 5);
    let hour = Number(match[1]);
    if (match[3].toUpperCase() === 'PM' && hour !== 12) hour += 12;
    if (match[3].toUpperCase() === 'AM' && hour === 12) hour = 0;
    return `${String(hour).padStart(2, '0')}:${match[2]}`;
}
</script>

<template>
    <Head title="Přehled" />
    <VendorLayout activePage="dashboard">
        <div class="min-w-0 space-y-6">
            <PageHeader title="Přehled" description="Dnešní termíny, nové požadavky a výsledky bez míchání různých měn.">
                <template #actions><UiButton :href="route('vendor.calendar')" variant="secondary"><CalendarClock :size="18" /> Otevřít kalendář</UiButton></template>
            </PageHeader>

            <div v-if="isNewAccount" class="rounded-2xl border border-brand-200 bg-brand-50 p-5 sm:p-6"><div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><h2 class="font-extrabold text-brand-900">Vítejte v Domluveno</h2><p class="mt-1 max-w-2xl text-sm leading-6 text-brand-800">Začněte kontrolou provozovny, otevírací doby a služeb. Jakmile je provozovna aktivní, zákazníci si mohou vybrat volný termín.</p></div><UiButton :href="route('vendor.shops.index')"><Store :size="18" /> Zkontrolovat provozovny</UiButton></div></div>

            <div class="grid min-w-0 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <UiCard v-for="stat in stats" :key="stat.label" padding="sm" class="min-w-0">
                    <div class="flex items-start gap-3"><span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-brand-50 text-brand-700"><component :is="iconMap[stat.icon] || CalendarCheck" :size="19" /></span><div class="min-w-0 flex-1"><p class="text-xs font-bold uppercase tracking-wide text-muted">{{ labelMap[stat.label] || stat.label }}</p><p class="mt-1 break-words text-xl font-extrabold text-ink">{{ stat.value }}</p><p v-if="stat.change" class="mt-1 break-words text-xs text-muted">{{ stat.change.replace('this month', 'tento měsíc').replace('rate', 'podíl') }}</p></div></div>
                </UiCard>
            </div>

            <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1.45fr)_minmax(19rem,0.85fr)]">
                <UiCard padding="none" class="min-w-0 overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4 sm:px-6"><div><h2 class="font-extrabold text-ink">Nedávné rezervace</h2><p class="mt-1 text-xs text-muted">Nejnovější požadavky napříč provozovnami</p></div><Link :href="route('vendor.bookings.index')" class="min-h-11 flex-none content-center text-sm font-bold text-brand-700 hover:text-brand-800">Zobrazit vše</Link></div>
                    <EmptyState v-if="!recentBookings.length" class="m-4 border-0" title="Zatím žádné rezervace" description="Po zveřejnění aktivní provozovny se nové rezervace zobrazí tady."><template #icon><CalendarCheck :size="23" /></template></EmptyState>
                    <div v-else>
                        <div class="grid gap-0 divide-y divide-line md:hidden"><Link v-for="booking in recentBookings" :key="booking.id" :href="route('vendor.bookings.show', booking.id)" class="p-4"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="break-words font-bold text-ink">{{ booking.customer_name }}</p><p class="mt-1 text-sm text-muted">{{ booking.service_name }}</p></div><StatusBadge :tone="statusMap[booking.status]?.tone || 'neutral'">{{ statusMap[booking.status]?.label || booking.status }}</StatusBadge></div><p class="mt-3 text-sm font-semibold text-ink">{{ formatDate(booking.date) }} · {{ europeanTime(booking.time) }} · {{ booking.formatted_price }}</p></Link></div>
                        <div class="hidden overflow-x-auto md:block"><table class="w-full min-w-[620px] text-left"><thead class="border-b border-line bg-gray-50 text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-5 py-3">Zákazník</th><th class="px-5 py-3">Služba</th><th class="px-5 py-3">Termín</th><th class="px-5 py-3">Cena</th><th class="px-5 py-3">Stav</th></tr></thead><tbody class="divide-y divide-line"><tr v-for="booking in recentBookings" :key="booking.id"><td class="px-5 py-4"><Link :href="route('vendor.bookings.show', booking.id)" class="font-bold text-ink hover:text-brand-700">{{ booking.customer_name }}</Link></td><td class="px-5 py-4 text-sm text-muted">{{ booking.service_name }}</td><td class="px-5 py-4 text-sm text-muted">{{ formatDate(booking.date) }} · {{ europeanTime(booking.time) }}</td><td class="px-5 py-4 text-sm font-bold text-ink">{{ booking.formatted_price }}</td><td class="px-5 py-4"><StatusBadge :tone="statusMap[booking.status]?.tone || 'neutral'">{{ statusMap[booking.status]?.label || booking.status }}</StatusBadge></td></tr></tbody></table></div>
                    </div>
                </UiCard>

                <UiCard padding="none" class="min-w-0 overflow-hidden">
                    <div class="border-b border-line px-5 py-4"><h2 class="font-extrabold text-ink">Dnes</h2><p class="mt-1 text-xs capitalize text-muted">{{ todayLabel }}</p></div>
                    <EmptyState v-if="!todayBookings.length" class="m-4 border-0" title="Dnes máte volno" description="Na dnešek není naplánovaný žádný termín."><template #icon><Clock3 :size="23" /></template></EmptyState>
                    <div v-else class="divide-y divide-line"><Link v-for="booking in todayBookings" :key="booking.id" :href="route('vendor.bookings.show', booking.id)" class="flex gap-3 p-4 transition hover:bg-brand-50/30"><div class="w-14 flex-none"><p class="text-sm font-extrabold text-ink">{{ europeanTime(booking.time) }}</p><p class="mt-1 text-xs text-muted">{{ europeanTime(booking.end_time) }}</p></div><div class="min-w-0 flex-1"><p class="break-words text-sm font-bold text-ink">{{ booking.title }}</p><p class="mt-1 break-words text-xs text-muted">{{ booking.customer }} · {{ booking.duration }}</p><StatusBadge class="mt-2" :tone="statusMap[booking.status]?.tone || 'neutral'">{{ statusMap[booking.status]?.label || booking.status }}</StatusBadge></div></Link></div>
                </UiCard>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <UiCard><h2 class="font-extrabold text-ink">Tento týden</h2><dl class="mt-5 grid grid-cols-3 gap-3"><div class="rounded-xl bg-gray-50 p-3"><dt class="text-xs font-bold uppercase text-muted">Rezervace</dt><dd class="mt-2 text-xl font-extrabold text-ink">{{ weekStats.total_bookings ?? 0 }}</dd></div><div class="rounded-xl bg-gray-50 p-3"><dt class="text-xs font-bold uppercase text-muted">Dokončené</dt><dd class="mt-2 text-xl font-extrabold text-ink">{{ weekStats.completed ?? 0 }}</dd></div><div class="min-w-0 rounded-xl bg-gray-50 p-3"><dt class="text-xs font-bold uppercase text-muted">Nezrušené</dt><dd class="mt-2 break-words text-base font-extrabold text-ink">{{ weekStats.revenue || '0,00 CZK' }}</dd></div></dl></UiCard>
                <UiCard><h2 class="font-extrabold text-ink">Nabídka a zákazníci</h2><dl class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3"><div v-for="item in [{ label: 'Aktivní služby', value: overview.available_services ?? 0, icon: Wrench }, { label: 'Čekající', value: overview.pending_bookings ?? 0, icon: CalendarClock }, { label: 'Vracející se', value: overview.returning_customers ?? 0, icon: Users }]" :key="item.label" class="min-w-0 rounded-xl bg-gray-50 p-3"><dt class="text-xs font-bold uppercase text-muted"><component :is="item.icon" :size="18" class="mb-3 text-brand-700" aria-hidden="true" />{{ item.label }}</dt><dd class="mt-1 text-xl font-extrabold text-ink">{{ item.value }}</dd></div></dl></UiCard>
            </div>

            <UiCard v-if="monthlyRevenue.length"><h2 class="font-extrabold text-ink">Posledních šest měsíců</h2><p class="mt-1 text-sm text-muted">Hodnota nezrušených rezervací je vždy oddělená podle měny.</p><div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6"><div v-for="month in monthlyRevenue" :key="month.month" class="min-w-0 rounded-xl border border-line p-3"><p class="text-xs font-bold uppercase text-muted">{{ monthLabel(month.month) }}</p><p class="mt-2 break-words text-sm font-extrabold text-ink">{{ month.formatted_revenue || '0,00 CZK' }}</p><p class="mt-1 text-xs text-muted">{{ month.bookings }} rezervací</p></div></div></UiCard>
        </div>
    </VendorLayout>
</template>
