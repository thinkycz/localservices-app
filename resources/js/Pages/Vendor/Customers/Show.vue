<script setup>
import StatusBadge from '@/Components/StatusBadge.vue';
import UiButton from '@/Components/UiButton.vue';
import UiCard from '@/Components/UiCard.vue';
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CalendarCheck, CircleDollarSign, Mail, Phone, Repeat2, UserRound } from '@lucide/vue';

defineProps({ customer: { type: Object, required: true } });

const statusMap = {
    pending: { label: 'Čeká', tone: 'warning' },
    confirmed: { label: 'Potvrzená', tone: 'brand' },
    completed: { label: 'Dokončená', tone: 'success' },
    cancelled: { label: 'Zrušená', tone: 'danger' },
};

function formatDate(value) { return value ? new Intl.DateTimeFormat('cs-CZ', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(`${String(value).slice(0, 10)}T12:00:00`)) : '—'; }
function formatTime(value) { return String(value || '').slice(0, 5) || '—'; }
</script>

<template>
    <Head :title="customer.name" />
    <VendorLayout activePage="customers">
        <div class="mx-auto max-w-6xl space-y-6">
            <UiButton :href="route('vendor.customers.index')" variant="ghost" size="sm"><ArrowLeft :size="17" /> Zpět na zákazníky</UiButton>

            <UiCard>
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                    <span class="flex h-16 w-16 flex-none items-center justify-center rounded-2xl bg-brand-50 text-lg font-extrabold text-brand-800">{{ customer.avatar_initials }}</span>
                    <div class="min-w-0 flex-1"><h1 class="break-words text-2xl font-extrabold tracking-tight text-ink">{{ customer.name }}</h1><div class="mt-3 flex flex-col gap-2 text-sm text-muted sm:flex-row sm:flex-wrap sm:gap-x-5"><a :href="`mailto:${customer.email}`" class="flex min-h-11 items-center gap-2 break-all hover:text-brand-700"><Mail :size="17" /> {{ customer.email }}</a><a v-if="customer.phone && customer.phone !== 'N/A'" :href="`tel:${customer.phone}`" class="flex min-h-11 items-center gap-2 hover:text-brand-700"><Phone :size="17" /> {{ customer.phone }}</a><span v-else class="flex min-h-11 items-center gap-2"><Phone :size="17" /> Telefon neuveden</span></div></div>
                </div>
            </UiCard>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <UiCard v-for="item in [
                    { label: 'Rezervace', value: customer.total_bookings ?? 0, icon: CalendarCheck },
                    { label: 'Dokončené', value: customer.completed_bookings ?? 0, icon: UserRound },
                    { label: 'Hodnota nezrušených rezervací', value: customer.total_spent, icon: CircleDollarSign },
                    { label: 'Zákazníkem od', value: formatDate(customer.first_booking_date), icon: Repeat2 },
                ]" :key="item.label" padding="sm" class="min-w-0"><div class="flex gap-3"><span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-brand-50 text-brand-700"><component :is="item.icon" :size="19" /></span><div class="min-w-0"><p class="text-xs font-bold uppercase tracking-wide text-muted">{{ item.label }}</p><p class="mt-1 break-words text-xl font-extrabold text-ink">{{ item.value }}</p></div></div></UiCard>
            </div>

            <UiCard v-if="customer.services_used?.length" padding="sm"><p class="text-xs font-bold uppercase tracking-wide text-muted">Využité služby</p><div class="mt-3 flex flex-wrap gap-2"><span v-for="service in customer.services_used" :key="service" class="rounded-full bg-brand-50 px-3 py-1.5 text-sm font-bold text-brand-800">{{ service }}</span></div></UiCard>

            <section class="space-y-4"><div><h2 class="text-xl font-extrabold text-ink">Historie rezervací</h2><p class="mt-1 text-sm text-muted">Všechny termíny tohoto zákazníka u vašich provozoven.</p></div>
                <div class="grid gap-3 md:hidden"><Link v-for="booking in customer.bookings" :key="booking.id" :href="route('vendor.bookings.show', booking.id)" class="rounded-2xl border border-line bg-white p-4"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="font-extrabold text-ink">{{ booking.service_name }}</p><p class="mt-1 text-sm text-muted">{{ booking.shop_name }}</p></div><StatusBadge :tone="statusMap[booking.status]?.tone || 'neutral'">{{ statusMap[booking.status]?.label || booking.status }}</StatusBadge></div><dl class="mt-4 grid grid-cols-2 gap-3 border-t border-line pt-4"><div><dt class="text-xs font-bold uppercase text-muted">Termín</dt><dd class="mt-1 text-sm font-bold text-ink">{{ formatDate(booking.date) }} · {{ formatTime(booking.time) }}</dd></div><div><dt class="text-xs font-bold uppercase text-muted">Cena</dt><dd class="mt-1 text-sm font-bold text-ink">{{ booking.formatted_price }}</dd></div></dl></Link></div>
                <div class="hidden overflow-hidden rounded-2xl border border-line bg-white md:block"><div class="overflow-x-auto"><table class="w-full min-w-[720px] text-left"><thead class="border-b border-line bg-gray-50 text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-5 py-3">Služba</th><th class="px-5 py-3">Provozovna</th><th class="px-5 py-3">Termín</th><th class="px-5 py-3">Cena</th><th class="px-5 py-3">Stav</th></tr></thead><tbody class="divide-y divide-line"><tr v-for="booking in customer.bookings" :key="booking.id" class="hover:bg-brand-50/30"><td class="px-5 py-4"><Link :href="route('vendor.bookings.show', booking.id)" class="font-extrabold text-ink hover:text-brand-700">{{ booking.service_name }}</Link></td><td class="px-5 py-4 text-sm text-muted">{{ booking.shop_name }}</td><td class="px-5 py-4 text-sm text-muted">{{ formatDate(booking.date) }} · <strong class="text-ink">{{ formatTime(booking.time) }}</strong></td><td class="px-5 py-4 text-sm font-bold text-ink">{{ booking.formatted_price }}</td><td class="px-5 py-4"><StatusBadge :tone="statusMap[booking.status]?.tone || 'neutral'">{{ statusMap[booking.status]?.label || booking.status }}</StatusBadge></td></tr></tbody></table></div></div>
            </section>
        </div>
    </VendorLayout>
</template>
