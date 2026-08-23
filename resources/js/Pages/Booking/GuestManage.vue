<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    CheckCircle2,
    Clock3,
    LoaderCircle,
    Mail,
    MapPin,
    ShieldCheck,
    TriangleAlert,
    X,
} from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    booking: { type: Object, required: true },
    token: { type: String, required: true },
    canCancel: { type: Boolean, default: false },
});

const page = usePage();
const isEnglish = computed(() => page.props.locale === 'en');
const locale = computed(() => isEnglish.value ? 'en-US' : 'cs-CZ');
const tr = (czech, english) => isEnglish.value ? english : czech;
const showCancelConfirmation = ref(false);
const cancelForm = useForm({});

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

function formatDate(value) {
    if (!value) return '—';
    const raw = String(value).slice(0, 10);
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

function cancelBooking() {
    cancelForm.post(route('guest.bookings.cancel', {
        booking: props.booking.id,
        token: props.token,
    }), {
        preserveScroll: true,
        onSuccess: () => {
            showCancelConfirmation.value = false;
        },
    });
}
</script>

<template>
    <Head :title="tr('Správa rezervace', 'Manage booking')" />

    <AppLayout>
        <div class="ui-container py-8 sm:py-12">
            <div class="mx-auto max-w-3xl">
                <header>
                    <p class="text-sm font-bold text-brand-700">Domluveno</p>
                    <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">
                                {{ tr('Vaše rezervace', 'Your booking') }}
                            </h1>
                            <p class="mt-2 text-sm text-muted">{{ tr('Číslo rezervace', 'Booking number') }} #{{ booking.id }}</p>
                        </div>
                        <span :class="[status.classes, 'inline-flex w-fit items-center rounded-full px-3 py-1.5 text-xs font-bold ring-1 ring-inset']">
                            {{ statusLabel }}
                        </span>
                    </div>
                </header>

                <div class="mt-6 flex items-start gap-3 rounded-2xl border border-brand-200 bg-brand-50 p-4 sm:p-5">
                    <Mail :size="22" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                    <div>
                        <h2 class="font-bold text-brand-950">{{ tr('Odkaz ke správě rezervace máte v e-mailu', 'Your management link is in your email') }}</h2>
                        <p class="mt-1 text-sm leading-6 text-brand-900">
                            {{ tr(
                                'Tato stránka je dostupná přes zabezpečený odkaz. Odkaz nikomu nepřeposílejte.',
                                'This page is available through a secure link. Do not forward the link to anyone.',
                            ) }}
                            <span v-if="booking.customer_email" class="mt-1 block break-all font-semibold">{{ booking.customer_email }}</span>
                        </p>
                    </div>
                </div>

                <article class="ui-card mt-5 overflow-hidden" aria-labelledby="guest-booking-heading">
                    <div class="border-b border-line px-5 py-5 sm:px-6">
                        <p class="text-sm font-semibold text-brand-700">{{ booking.shop?.name || tr('Provozovna', 'Shop') }}</p>
                        <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <h2 id="guest-booking-heading" class="text-xl font-bold text-ink">
                                {{ booking.service?.name || tr('Rezervovaná služba', 'Booked service') }}
                            </h2>
                            <strong class="text-xl text-ink">{{ formatMoney(price) }}</strong>
                        </div>
                        <p v-if="booking.service?.duration_minutes" class="mt-1 text-sm text-muted">{{ booking.service.duration_minutes }} min</p>
                    </div>

                    <dl class="grid gap-px bg-line sm:grid-cols-2">
                        <div class="bg-white p-5">
                            <dt class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-muted">
                                <CalendarDays :size="17" class="text-brand-700" aria-hidden="true" />
                                {{ tr('Datum', 'Date') }}
                            </dt>
                            <dd class="mt-2 font-semibold text-ink">{{ formatDate(booking.booking_date) }}</dd>
                        </div>
                        <div class="bg-white p-5">
                            <dt class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-muted">
                                <Clock3 :size="17" class="text-brand-700" aria-hidden="true" />
                                {{ tr('Čas', 'Time') }}
                            </dt>
                            <dd class="mt-2 font-semibold text-ink">{{ formatTime(booking.start_time) }}–{{ formatTime(booking.end_time) }}</dd>
                            <dd class="mt-1 text-xs text-muted">{{ timezone }}</dd>
                        </div>
                        <div v-if="booking.shop?.address" class="bg-white p-5 sm:col-span-2">
                            <dt class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-muted">
                                <MapPin :size="17" class="text-brand-700" aria-hidden="true" />
                                {{ tr('Místo', 'Location') }}
                            </dt>
                            <dd class="mt-2 font-semibold text-ink">
                                {{ booking.shop.address }}<span v-if="booking.shop.city">, {{ booking.shop.city }}</span>
                            </dd>
                        </div>
                    </dl>

                    <div v-if="booking.customer_notes" class="border-t border-line px-5 py-5 sm:px-6">
                        <h3 class="text-sm font-bold text-ink">{{ tr('Poznámka pro poskytovatele', 'Note for the provider') }}</h3>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-muted">{{ booking.customer_notes }}</p>
                    </div>
                </article>

                <section class="mt-5 rounded-2xl border border-line bg-white p-5" aria-labelledby="guest-actions-heading">
                    <div class="flex items-start gap-3">
                        <ShieldCheck :size="21" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                        <div>
                            <h2 id="guest-actions-heading" class="font-bold text-ink">{{ tr('Správa termínu', 'Manage appointment') }}</h2>
                            <p v-if="canCancel" class="mt-1 text-sm leading-6 text-muted">
                                {{ tr('Rezervaci můžete zrušit nejpozději 24 hodin před začátkem.', 'You can cancel this booking up to 24 hours before it starts.') }}
                            </p>
                            <p v-else-if="booking.status === 'cancelled'" class="mt-1 flex items-center gap-2 text-sm text-muted">
                                <X :size="17" class="text-danger" aria-hidden="true" />
                                {{ tr('Tato rezervace už byla zrušena.', 'This booking has already been cancelled.') }}
                            </p>
                            <p v-else class="mt-1 text-sm leading-6 text-muted">
                                {{ tr('Rezervaci už nelze zrušit online. Pokud potřebujete pomoc, kontaktujte provozovnu.', 'This booking can no longer be cancelled online. Contact the shop if you need help.') }}
                            </p>
                        </div>
                    </div>

                    <button v-if="canCancel && !showCancelConfirmation" type="button" class="ui-button mt-4 border border-red-200 bg-white text-danger hover:bg-red-50" @click="showCancelConfirmation = true">
                        {{ tr('Zrušit rezervaci', 'Cancel booking') }}
                    </button>

                    <div v-if="canCancel && showCancelConfirmation" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4" role="alertdialog" aria-labelledby="cancel-title">
                        <div class="flex items-start gap-3">
                            <TriangleAlert :size="20" class="mt-0.5 flex-none text-danger" aria-hidden="true" />
                            <div>
                                <h3 id="cancel-title" class="font-bold text-ink">{{ tr('Opravdu chcete rezervaci zrušit?', 'Are you sure you want to cancel?') }}</h3>
                                <p class="mt-1 text-sm leading-6 text-muted">{{ tr('Tento krok nelze vrátit zpět.', 'This action cannot be undone.') }}</p>
                            </div>
                        </div>
                        <p v-if="Object.keys(cancelForm.errors).length" class="mt-3 text-sm font-medium text-danger" role="alert">
                            {{ tr('Rezervaci se nepodařilo zrušit. Obnovte stránku a zkuste to znovu.', 'The booking could not be cancelled. Refresh the page and try again.') }}
                        </p>
                        <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row">
                            <button type="button" class="ui-button ui-button-secondary" :disabled="cancelForm.processing" @click="showCancelConfirmation = false">
                                {{ tr('Ponechat rezervaci', 'Keep booking') }}
                            </button>
                            <button type="button" class="ui-button ui-button-danger" :disabled="cancelForm.processing" @click="cancelBooking">
                                <LoaderCircle v-if="cancelForm.processing" :size="18" class="animate-spin" aria-hidden="true" />
                                {{ cancelForm.processing ? tr('Rušíme…', 'Cancelling…') : tr('Ano, zrušit', 'Yes, cancel') }}
                            </button>
                        </div>
                    </div>
                </section>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <Link v-if="booking.shop?.slug" :href="route('shops.show', booking.shop.slug)" class="ui-button ui-button-primary">
                        <CheckCircle2 :size="18" aria-hidden="true" />
                        {{ tr('Zpět na profil provozovny', 'Back to shop') }}
                    </Link>
                    <Link :href="route('shops.index')" class="ui-button ui-button-secondary">
                        {{ tr('Najít další službu', 'Find another service') }}
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
