<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    Check,
    Clock3,
    Mail,
    MapPin,
    NotebookPen,
    ShieldCheck,
} from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    booking: { type: Object, required: true },
});

const page = usePage();
const isEnglish = computed(() => page.props.locale === 'en');
const locale = computed(() => isEnglish.value ? 'en-US' : 'cs-CZ');
const tr = (czech, english) => isEnglish.value ? english : czech;

const statusConfig = {
    pending: { cs: 'Čeká na potvrzení', en: 'Pending', classes: 'bg-amber-50 text-amber-800 ring-amber-600/20' },
    confirmed: { cs: 'Potvrzeno', en: 'Confirmed', classes: 'bg-green-50 text-green-800 ring-green-600/20' },
    completed: { cs: 'Dokončeno', en: 'Completed', classes: 'bg-gray-100 text-gray-700 ring-gray-500/20' },
    cancelled: { cs: 'Zrušeno', en: 'Cancelled', classes: 'bg-red-50 text-red-800 ring-red-600/20' },
};

const status = computed(() => statusConfig[props.booking.status] ?? statusConfig.pending);
const statusLabel = computed(() => isEnglish.value ? status.value.en : status.value.cs);
const price = computed(() => props.booking.price_amount ?? props.booking.total_price ?? props.booking.service?.price ?? null);
const currency = computed(() => props.booking.currency || props.booking.shop?.currency || 'CZK');
const timezone = computed(() => props.booking.timezone || props.booking.shop?.timezone || 'Europe/Prague');

function dateOnly(value) {
    if (!value) return '';
    return String(value).slice(0, 10);
}

function formatDate(value) {
    const raw = dateOnly(value);
    if (!raw) return '—';
    const date = new Date(`${raw}T12:00:00`);
    if (Number.isNaN(date.getTime())) return '—';
    return new Intl.DateTimeFormat(locale.value, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
}

function formatTime(value) {
    if (!value) return '—';
    const match = String(value).match(/^(\d{1,2}):(\d{2})/);
    return match ? `${String(Number(match[1])).padStart(2, '0')}:${match[2]}` : '—';
}

function formatMoney(value) {
    const amount = Number(value);
    if (!Number.isFinite(amount)) return '—';
    try {
        return new Intl.NumberFormat(locale.value, {
            style: 'currency',
            currency: currency.value,
            maximumFractionDigits: amount % 1 === 0 ? 0 : 2,
        }).format(amount);
    } catch {
        return `${amount.toFixed(2)} ${currency.value}`;
    }
}
</script>

<template>
    <Head :title="tr('Rezervace vytvořena', 'Booking created')" />

    <AppLayout>
        <div class="ui-container py-10 sm:py-14">
            <div class="mx-auto max-w-3xl">
                <header class="text-center">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-soft">
                        <Check :size="34" stroke-width="2.5" aria-hidden="true" />
                    </span>
                    <p class="mt-5 text-sm font-bold text-brand-700">{{ tr('Hotovo', 'All set') }}</p>
                    <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-ink">
                        {{ tr('Rezervaci jsme přijali', 'We received your booking') }}
                    </h1>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-muted sm:text-base">
                        {{ tr(
                            'Potvrzení jsme poslali e-mailem. Aktuální stav rezervace najdete kdykoli ve svém účtu.',
                            'We sent a confirmation by email. You can find the current booking status in your account at any time.',
                        ) }}
                    </p>
                </header>

                <article class="ui-card mt-8 overflow-hidden" aria-labelledby="booking-details-heading">
                    <div class="flex flex-col gap-3 border-b border-line bg-brand-50 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div>
                            <h2 id="booking-details-heading" class="font-bold text-ink">
                                {{ tr('Podrobnosti rezervace', 'Booking details') }}
                            </h2>
                            <p class="mt-1 text-xs text-muted">{{ tr('Číslo rezervace', 'Booking number') }} #{{ booking.id }}</p>
                        </div>
                        <span :class="[status.classes, 'inline-flex w-fit items-center rounded-full px-3 py-1.5 text-xs font-bold ring-1 ring-inset']">
                            {{ statusLabel }}
                        </span>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col gap-4 border-b border-line pb-5 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-brand-700">{{ booking.shop?.name || tr('Provozovna', 'Shop') }}</p>
                                <h3 class="mt-1 text-xl font-bold text-ink">{{ booking.service?.name || tr('Rezervovaná služba', 'Booked service') }}</h3>
                                <p v-if="booking.service?.duration_minutes" class="mt-1 text-sm text-muted">{{ booking.service.duration_minutes }} min</p>
                            </div>
                            <strong class="text-xl text-ink">{{ formatMoney(price) }}</strong>
                        </div>

                        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl bg-gray-50 p-4">
                                <dt class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-muted">
                                    <CalendarDays :size="17" class="text-brand-700" aria-hidden="true" />
                                    {{ tr('Datum', 'Date') }}
                                </dt>
                                <dd class="mt-2 font-semibold text-ink">{{ formatDate(booking.booking_date) }}</dd>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-4">
                                <dt class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-muted">
                                    <Clock3 :size="17" class="text-brand-700" aria-hidden="true" />
                                    {{ tr('Čas', 'Time') }}
                                </dt>
                                <dd class="mt-2 font-semibold text-ink">{{ formatTime(booking.start_time) }}–{{ formatTime(booking.end_time) }}</dd>
                                <dd class="mt-1 text-xs text-muted">{{ timezone }}</dd>
                            </div>
                            <div v-if="booking.shop?.address" class="rounded-xl bg-gray-50 p-4 sm:col-span-2">
                                <dt class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-muted">
                                    <MapPin :size="17" class="text-brand-700" aria-hidden="true" />
                                    {{ tr('Místo', 'Location') }}
                                </dt>
                                <dd class="mt-2 font-semibold text-ink">
                                    {{ booking.shop.address }}<span v-if="booking.shop.city">, {{ booking.shop.city }}</span>
                                </dd>
                            </div>
                        </dl>

                        <div v-if="booking.customer_notes" class="mt-5 rounded-xl border border-line p-4">
                            <h3 class="flex items-center gap-2 text-sm font-bold text-ink">
                                <NotebookPen :size="18" class="text-brand-700" aria-hidden="true" />
                                {{ tr('Vaše poznámka', 'Your note') }}
                            </h3>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-muted">{{ booking.customer_notes }}</p>
                        </div>
                    </div>
                </article>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="flex items-start gap-3 rounded-2xl border border-line bg-white p-4">
                        <Mail :size="20" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                        <p class="text-sm leading-6 text-muted">
                            {{ tr('Potvrzení a případné změny posíláme na e-mail uvedený u rezervace.', 'Confirmation and any updates are sent to the email attached to this booking.') }}
                        </p>
                    </div>
                    <div class="flex items-start gap-3 rounded-2xl border border-line bg-white p-4">
                        <ShieldCheck :size="20" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                        <p class="text-sm leading-6 text-muted">
                            {{ tr('Bezplatné zrušení je možné nejpozději 24 hodin před začátkem.', 'Free cancellation is available up to 24 hours before the appointment.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <Link :href="route('bookings.index')" class="ui-button ui-button-primary">
                        <CalendarDays :size="18" aria-hidden="true" />
                        {{ tr('Moje rezervace', 'My bookings') }}
                    </Link>
                    <Link :href="route('shops.index')" class="ui-button ui-button-secondary">
                        {{ tr('Procházet služby', 'Browse services') }}
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
