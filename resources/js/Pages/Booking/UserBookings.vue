<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarDays,
    Clock3,
    LoaderCircle,
    MapPin,
    Plus,
    RotateCcw,
    Star,
    TriangleAlert,
} from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    bookings: { type: Object, required: true },
});

const page = usePage();
const isEnglish = computed(() => page.props.locale === 'en');
const locale = computed(() => isEnglish.value ? 'en-US' : 'cs-CZ');
const tr = (czech, english) => isEnglish.value ? english : czech;
const cancelTarget = ref(null);
const cancelForm = useForm({});

const statusConfig = {
    pending: { cs: 'Čeká na potvrzení', en: 'Pending', classes: 'bg-amber-50 text-amber-800 ring-amber-600/20' },
    confirmed: { cs: 'Potvrzeno', en: 'Confirmed', classes: 'bg-green-50 text-green-800 ring-green-600/20' },
    completed: { cs: 'Dokončeno', en: 'Completed', classes: 'bg-gray-100 text-gray-700 ring-gray-500/20' },
    cancelled: { cs: 'Zrušeno', en: 'Cancelled', classes: 'bg-red-50 text-red-800 ring-red-600/20' },
};

const allBookings = computed(() => Array.isArray(props.bookings.data) ? props.bookings.data : []);
const upcomingBookings = computed(() => allBookings.value.filter(isUpcoming));
const historyBookings = computed(() => allBookings.value.filter((booking) => !isUpcoming(booking)));

function statusFor(booking) {
    return statusConfig[booking?.status] ?? statusConfig.pending;
}

function statusLabel(booking) {
    const config = statusFor(booking);
    return isEnglish.value ? config.en : config.cs;
}

function isUpcoming(booking) {
    if (typeof booking?.is_upcoming === 'boolean') return booking.is_upcoming;
    if (!['pending', 'confirmed'].includes(booking?.status)) return false;

    const rawDate = booking?.booking_date ? String(booking.booking_date).slice(0, 10) : '';
    const rawTime = booking?.start_time ? String(booking.start_time).slice(0, 5) : '23:59';
    const startsAt = new Date(`${rawDate}T${rawTime}:00`);
    return !Number.isNaN(startsAt.getTime()) && startsAt.getTime() >= Date.now();
}

function canCancel(booking) {
    return booking?.can_cancel === true;
}

function formatDate(value) {
    if (!value) return '—';
    const raw = String(value).slice(0, 10);
    const date = new Date(`${raw}T12:00:00`);
    if (Number.isNaN(date.getTime())) return '—';
    return new Intl.DateTimeFormat(locale.value, {
        weekday: 'short',
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

function formatMoney(booking) {
    const value = booking?.price_amount ?? booking?.total_price ?? booking?.service?.price;
    const amount = Number(value);
    if (!Number.isFinite(amount)) return '—';
    const currency = booking?.currency || booking?.shop?.currency || 'CZK';
    try {
        return new Intl.NumberFormat(locale.value, {
            style: 'currency',
            currency,
            maximumFractionDigits: amount % 1 === 0 ? 0 : 2,
        }).format(amount);
    } catch {
        return `${amount.toFixed(2)} ${currency}`;
    }
}

function shopUrl(booking) {
    return booking?.shop?.slug ? route('shops.show', booking.shop.slug) : route('shops.index');
}

function rebookUrl(booking) {
    if (!booking?.shop?.slug) return route('shops.index');
    return route('shops.book', {
        slug: booking.shop.slug,
        ...(booking?.service?.id ? { service_id: booking.service.id } : {}),
    });
}

function cancelBooking(booking) {
    if (!canCancel(booking)) return;
    cancelForm.post(route('bookings.cancel', booking.id), {
        preserveScroll: true,
        onSuccess: () => {
            cancelTarget.value = null;
        },
    });
}
</script>

<template>
    <Head :title="tr('Moje rezervace', 'My bookings')" />

    <AppLayout>
        <header class="border-b border-line bg-white">
            <div class="ui-container flex flex-col gap-5 py-8 sm:flex-row sm:items-end sm:justify-between sm:py-10">
                <div>
                    <p class="text-sm font-bold text-brand-700">Domluveno</p>
                    <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-ink">{{ tr('Moje rezervace', 'My bookings') }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-muted">
                        {{ tr('Na jednom místě najdete nadcházející termíny i historii návštěv.', 'Find upcoming appointments and visit history in one place.') }}
                    </p>
                </div>
                <Link :href="route('shops.index')" class="ui-button ui-button-primary w-full sm:w-auto">
                    <Plus :size="18" aria-hidden="true" />
                    {{ tr('Nová rezervace', 'New booking') }}
                </Link>
            </div>
        </header>

        <div class="ui-container py-8 sm:py-10">
            <div v-if="allBookings.length" class="space-y-10">
                <section aria-labelledby="upcoming-heading">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <h2 id="upcoming-heading" class="text-xl font-bold text-ink">{{ tr('Nadcházející', 'Upcoming') }}</h2>
                            <p class="mt-1 text-sm text-muted">{{ tr('Termíny, které vás teprve čekají.', 'Appointments that are still ahead.') }}</p>
                        </div>
                        <span class="rounded-full bg-brand-100 px-2.5 py-1 text-xs font-bold text-brand-800">{{ upcomingBookings.length }}</span>
                    </div>

                    <div v-if="upcomingBookings.length" class="mt-5 grid gap-4 lg:grid-cols-2">
                        <article v-for="booking in upcomingBookings" :key="booking.id" class="ui-card overflow-hidden">
                            <div class="flex flex-col gap-4 p-5 sm:p-6">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <span :class="[statusFor(booking).classes, 'inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset']">
                                            {{ statusLabel(booking) }}
                                        </span>
                                        <h3 class="mt-3 truncate text-lg font-bold text-ink">{{ booking.service?.name || tr('Rezervovaná služba', 'Booked service') }}</h3>
                                        <p class="mt-1 truncate text-sm text-muted">{{ booking.shop?.name || tr('Provozovna', 'Shop') }}</p>
                                    </div>
                                    <strong class="text-lg text-ink">{{ formatMoney(booking) }}</strong>
                                </div>

                                <dl class="grid gap-3 rounded-xl bg-gray-50 p-4 text-sm sm:grid-cols-2">
                                    <div class="grid grid-cols-[auto_1fr] gap-x-2">
                                        <dt>
                                            <CalendarDays :size="18" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                                            <span class="sr-only">{{ tr('Datum', 'Date') }}</span>
                                        </dt>
                                        <dd class="font-semibold text-ink">{{ formatDate(booking.booking_date) }}</dd>
                                    </div>
                                    <div class="grid grid-cols-[auto_1fr] gap-x-2">
                                        <dt>
                                            <Clock3 :size="18" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                                            <span class="sr-only">{{ tr('Čas', 'Time') }}</span>
                                        </dt>
                                        <dd class="font-semibold text-ink">{{ formatTime(booking.start_time) }}–{{ formatTime(booking.end_time) }}</dd>
                                    </div>
                                    <div v-if="booking.shop?.address" class="grid grid-cols-[auto_1fr] gap-x-2 sm:col-span-2">
                                        <dt>
                                            <MapPin :size="18" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                                            <span class="sr-only">{{ tr('Místo', 'Location') }}</span>
                                        </dt>
                                        <dd class="text-muted">{{ booking.shop.address }}<span v-if="booking.shop.city">, {{ booking.shop.city }}</span></dd>
                                    </div>
                                </dl>

                                <p v-if="booking.customer_notes" class="line-clamp-2 text-sm leading-6 text-muted">{{ booking.customer_notes }}</p>

                                <div v-if="cancelTarget === booking.id" class="rounded-xl border border-red-200 bg-red-50 p-4" role="alertdialog" :aria-labelledby="`cancel-title-${booking.id}`">
                                    <div class="flex items-start gap-3">
                                        <TriangleAlert :size="20" class="mt-0.5 flex-none text-danger" aria-hidden="true" />
                                        <div>
                                            <h4 :id="`cancel-title-${booking.id}`" class="font-bold text-ink">{{ tr('Zrušit tento termín?', 'Cancel this appointment?') }}</h4>
                                            <p class="mt-1 text-sm text-muted">{{ tr('Tento krok nelze vrátit zpět.', 'This action cannot be undone.') }}</p>
                                        </div>
                                    </div>
                                    <p v-if="Object.keys(cancelForm.errors).length" class="mt-3 text-sm font-medium text-danger" role="alert">
                                        {{ tr('Rezervaci se nepodařilo zrušit. Zkuste to prosím znovu.', 'The booking could not be cancelled. Please try again.') }}
                                    </p>
                                    <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row">
                                        <button type="button" class="ui-button ui-button-secondary" :disabled="cancelForm.processing" @click="cancelTarget = null">
                                            {{ tr('Ponechat rezervaci', 'Keep booking') }}
                                        </button>
                                        <button type="button" class="ui-button ui-button-danger" :disabled="cancelForm.processing" @click="cancelBooking(booking)">
                                            <LoaderCircle v-if="cancelForm.processing" :size="18" class="animate-spin" aria-hidden="true" />
                                            {{ cancelForm.processing ? tr('Rušíme…', 'Cancelling…') : tr('Ano, zrušit', 'Yes, cancel') }}
                                        </button>
                                    </div>
                                </div>

                                <div v-else class="flex flex-col gap-2 border-t border-line pt-4 sm:flex-row sm:items-center">
                                    <Link :href="shopUrl(booking)" class="ui-button ui-button-secondary flex-1 sm:flex-none">
                                        {{ tr('Detail provozovny', 'Shop details') }}
                                        <ArrowRight :size="16" aria-hidden="true" />
                                    </Link>
                                    <button v-if="canCancel(booking)" type="button" class="ui-button flex-1 border border-red-200 bg-white text-danger hover:bg-red-50 sm:flex-none" @click="cancelTarget = booking.id">
                                        {{ tr('Zrušit rezervaci', 'Cancel booking') }}
                                    </button>
                                    <p v-else class="text-xs leading-5 text-muted sm:ml-auto sm:max-w-52 sm:text-right">
                                        {{ tr('Online zrušení už není dostupné.', 'Online cancellation is no longer available.') }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div v-else class="ui-card mt-5 p-7 text-center sm:p-10">
                        <CalendarDays :size="30" class="mx-auto text-brand-700" aria-hidden="true" />
                        <h3 class="mt-3 font-bold text-ink">{{ tr('Žádný nadcházející termín', 'No upcoming appointments') }}</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted">
                            {{ tr('Až si něco rezervujete, termín se objeví tady.', 'Your next appointment will appear here after you make a booking.') }}
                        </p>
                        <Link :href="route('shops.index')" class="ui-button ui-button-primary mt-5">
                            {{ tr('Najít službu', 'Find a service') }}
                        </Link>
                    </div>
                </section>

                <section aria-labelledby="history-heading">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <h2 id="history-heading" class="text-xl font-bold text-ink">{{ tr('Historie', 'History') }}</h2>
                            <p class="mt-1 text-sm text-muted">{{ tr('Dokončené, zrušené a uplynulé rezervace.', 'Completed, cancelled and past bookings.') }}</p>
                        </div>
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-muted">{{ historyBookings.length }}</span>
                    </div>

                    <div v-if="historyBookings.length" class="mt-5 overflow-hidden rounded-2xl border border-line bg-white">
                        <article v-for="(booking, index) in historyBookings" :key="booking.id" class="p-5 sm:p-6" :class="index ? 'border-t border-line' : ''">
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span :class="[statusFor(booking).classes, 'inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset']">
                                            {{ statusLabel(booking) }}
                                        </span>
                                        <span class="text-sm text-muted">{{ formatDate(booking.booking_date) }} · {{ formatTime(booking.start_time) }}</span>
                                    </div>
                                    <h3 class="mt-2 font-bold text-ink">{{ booking.service?.name || tr('Rezervovaná služba', 'Booked service') }}</h3>
                                    <p class="mt-1 text-sm text-muted">{{ booking.shop?.name || tr('Provozovna', 'Shop') }} · {{ formatMoney(booking) }}</p>
                                </div>

                                <div class="flex flex-col gap-2 sm:flex-row">
                                    <Link
                                        v-if="booking.status === 'completed' && !booking.has_review"
                                        :href="route('reviews.create', booking.id)"
                                        class="ui-button border border-amber-200 bg-white text-amber-800 hover:bg-amber-50"
                                    >
                                        <Star :size="17" aria-hidden="true" />
                                        {{ tr('Napsat recenzi', 'Write a review') }}
                                    </Link>
                                    <Link :href="rebookUrl(booking)" class="ui-button ui-button-secondary">
                                        <RotateCcw :size="17" aria-hidden="true" />
                                        {{ tr('Rezervovat znovu', 'Book again') }}
                                    </Link>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div v-else class="ui-card mt-5 p-7 text-center">
                        <p class="text-sm text-muted">{{ tr('Historie je zatím prázdná.', 'Your booking history is empty.') }}</p>
                    </div>
                </section>
            </div>

            <div v-else class="ui-card mx-auto max-w-2xl p-8 text-center sm:p-12">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-100 text-brand-700">
                    <CalendarDays :size="28" aria-hidden="true" />
                </span>
                <h2 class="mt-5 text-xl font-bold text-ink">{{ tr('Zatím nemáte žádnou rezervaci', 'You do not have any bookings yet') }}</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted">
                    {{ tr('Najděte službu, zvolte volný čas a potvrďte rezervaci během pár minut.', 'Find a service, choose an available time and confirm in a few minutes.') }}
                </p>
                <Link :href="route('shops.index')" class="ui-button ui-button-primary mt-6">
                    {{ tr('Procházet služby', 'Browse services') }}
                    <ArrowRight :size="17" aria-hidden="true" />
                </Link>
            </div>

            <nav v-if="bookings.links?.length > 3" class="mt-8 flex flex-wrap justify-center gap-2" :aria-label="tr('Stránkování rezervací', 'Booking pagination')">
                <Link
                    v-for="(link, index) in bookings.links"
                    :key="`${index}-${link.label}`"
                    :href="link.url || '#'"
                    :aria-current="link.active ? 'page' : undefined"
                    :aria-disabled="!link.url"
                    :class="[
                        'inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl border px-3 text-sm font-semibold transition',
                        link.active
                            ? 'border-brand-600 bg-brand-600 text-white'
                            : 'border-line bg-white text-muted hover:border-brand-300 hover:text-brand-700',
                        !link.url ? 'pointer-events-none opacity-40' : '',
                    ]"
                    v-html="link.label"
                ></Link>
            </nav>
        </div>
    </AppLayout>
</template>
