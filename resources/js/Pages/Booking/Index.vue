<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    Check,
    CheckCircle2,
    ChevronRight,
    Clock3,
    Info,
    LoaderCircle,
    Mail,
    MapPin,
    ShieldCheck,
    UserRound,
} from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    shop: { type: Object, required: true },
    service: { type: Object, default: null },
    date: { type: String, default: null },
    time: { type: String, default: null },
    authUser: { type: Object, default: null },
});

const page = usePage();
const isEnglish = computed(() => page.props.locale === 'en');
const locale = computed(() => isEnglish.value ? 'en-US' : 'cs-CZ');
const tr = (czech, english) => isEnglish.value ? english : czech;
const services = computed(() => (props.shop.services ?? []).filter((item) => item?.is_available !== false));
const selectedService = ref(
    props.service
        ? services.value.find((item) => Number(item.id) === Number(props.service.id)) ?? props.service
        : null,
);
const reviewMode = ref(false);
const localErrors = ref({});
const availability = ref({
    slots: [],
    closed: false,
    reason: null,
    timezone: props.shop.timezone || 'Europe/Prague',
});
const availabilityLoading = ref(false);
const availabilityError = ref('');
let availabilityRequest = null;

function normalizeTime(value) {
    if (!value) return '';
    const raw = String(value).trim();
    const meridiem = raw.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    const twentyFourHour = raw.match(/^(\d{1,2}):(\d{2})/);

    if (meridiem) {
        let hour = Number(meridiem[1]);
        if (meridiem[3].toUpperCase() === 'PM' && hour !== 12) hour += 12;
        if (meridiem[3].toUpperCase() === 'AM' && hour === 12) hour = 0;
        return `${String(hour).padStart(2, '0')}:${meridiem[2]}`;
    }

    if (twentyFourHour) {
        return `${String(Number(twentyFourHour[1])).padStart(2, '0')}:${twentyFourHour[2]}`;
    }

    return '';
}

const form = useForm({
    service_id: selectedService.value?.id ?? null,
    booking_date: props.date?.slice(0, 10) ?? '',
    start_time: normalizeTime(props.time),
    full_name: props.authUser?.name ?? '',
    email: props.authUser?.email ?? '',
    phone: props.authUser?.phone ?? '',
    customer_notes: '',
});

const minimumDate = computed(() => {
    const parts = new Intl.DateTimeFormat('en-CA', {
        timeZone: availability.value.timezone || 'Europe/Prague',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).formatToParts(new Date());
    const values = Object.fromEntries(parts.map((part) => [part.type, part.value]));
    return `${values.year}-${values.month}-${values.day}`;
});

const currentStep = computed(() => {
    if (reviewMode.value) return 5;
    if (!selectedService.value) return 1;
    if (!form.booking_date) return 2;
    if (!form.start_time) return 3;
    return 4;
});

const steps = [
    { number: 1, cs: 'Služba', en: 'Service' },
    { number: 2, cs: 'Datum', en: 'Date' },
    { number: 3, cs: 'Čas', en: 'Time' },
    { number: 4, cs: 'Údaje', en: 'Details' },
    { number: 5, cs: 'Potvrzení', en: 'Confirm' },
];

const availabilityMessage = computed(() => {
    if (availabilityError.value) return availabilityError.value;
    const messages = {
        unavailable: tr('Tuto službu nyní nelze rezervovat.', 'This service is not currently available to book.'),
        past_date: tr('Vyberte dnešní nebo budoucí datum.', 'Choose today or a future date.'),
        closed: tr('V tento den má provozovna zavřeno.', 'The shop is closed on this day.'),
        no_slots: tr('Pro tento den už nejsou volné termíny.', 'There are no available times left on this day.'),
    };
    return messages[availability.value.reason] ?? '';
});

const contactIsValid = computed(() => (
    form.full_name.trim().length > 0
    && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())
    && form.phone.trim().length > 0
));

function selectService(service) {
    selectedService.value = service;
    form.service_id = service.id;
    form.booking_date = '';
    form.start_time = '';
    form.clearErrors();
    localErrors.value = {};
    availability.value = {
        slots: [],
        closed: false,
        reason: null,
        timezone: props.shop.timezone || 'Europe/Prague',
    };
    reviewMode.value = false;
}

function handleDateChange() {
    form.start_time = '';
    form.clearErrors('booking_date', 'start_time');
    localErrors.value = {};
    reviewMode.value = false;
}

function selectTime(slot) {
    form.start_time = slot;
    form.clearErrors('start_time');
    reviewMode.value = false;
}

async function loadAvailability() {
    if (!selectedService.value || !form.booking_date) return;

    availabilityRequest?.abort();
    availabilityRequest = new AbortController();
    availabilityLoading.value = true;
    availabilityError.value = '';

    try {
        const endpoint = route('shops.availability', {
            shop: props.shop.slug,
            service_id: selectedService.value.id,
            date: form.booking_date,
        });
        const response = await fetch(endpoint, {
            headers: { Accept: 'application/json' },
            signal: availabilityRequest.signal,
        });

        if (!response.ok) throw new Error('availability_failed');

        const payload = await response.json();
        availability.value = {
            slots: Array.isArray(payload.slots) ? payload.slots : [],
            closed: Boolean(payload.closed),
            reason: payload.reason ?? null,
            timezone: payload.timezone || props.shop.timezone || 'Europe/Prague',
        };

        if (form.start_time && !availability.value.slots.includes(form.start_time)) {
            form.start_time = '';
        }
    } catch (error) {
        if (error?.name !== 'AbortError') {
            availabilityError.value = tr('Volné termíny se nepodařilo načíst. Zkuste to prosím znovu.', 'Available times could not be loaded. Please try again.');
            availability.value.slots = [];
        }
    } finally {
        availabilityLoading.value = false;
    }
}

watch(
    () => [form.service_id, form.booking_date],
    ([serviceId, date]) => {
        if (serviceId && date) loadAvailability();
    },
    { immediate: true },
);

onBeforeUnmount(() => availabilityRequest?.abort());

function validateContact() {
    const errors = {};
    if (!form.full_name.trim()) errors.full_name = tr('Vyplňte své jméno.', 'Enter your full name.');
    if (!form.email.trim()) errors.email = tr('Vyplňte e-mailovou adresu.', 'Enter your email address.');
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) errors.email = tr('Zadejte platnou e-mailovou adresu.', 'Enter a valid email address.');
    if (!form.phone.trim()) errors.phone = tr('Vyplňte telefonní číslo.', 'Enter your phone number.');
    localErrors.value = errors;
    return Object.keys(errors).length === 0;
}

async function openReview() {
    if (!validateContact()) {
        await nextTick();
        document.querySelector('[aria-invalid="true"]')?.focus();
        return;
    }
    reviewMode.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function submitBooking() {
    if (!reviewMode.value || !contactIsValid.value || !form.service_id || !form.booking_date || !form.start_time) return;

    form.transform((data) => ({
        service_id: data.service_id,
        booking_date: data.booking_date,
        start_time: data.start_time,
        full_name: data.full_name.trim(),
        email: data.email.trim(),
        phone: data.phone.trim(),
        customer_notes: data.customer_notes?.trim() || null,
    })).post(route('bookings.store'), {
        preserveScroll: true,
        onError: (errors) => {
            reviewMode.value = false;
            if (errors.start_time) {
                form.start_time = '';
                loadAvailability();
            }
            nextTick(() => document.querySelector('[aria-invalid="true"]')?.focus());
        },
    });
}

function formatDate(value) {
    if (!value) return '—';
    const date = new Date(`${String(value).slice(0, 10)}T12:00:00`);
    if (Number.isNaN(date.getTime())) return '—';
    return new Intl.DateTimeFormat(locale.value, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
}

function formatTime(value) {
    return normalizeTime(value) || '—';
}

function formatMoney(value, currency) {
    const amount = Number(value);
    if (!Number.isFinite(amount)) return '—';
    try {
        return new Intl.NumberFormat(locale.value, {
            style: 'currency',
            currency: currency || 'CZK',
            maximumFractionDigits: amount % 1 === 0 ? 0 : 2,
        }).format(amount);
    } catch {
        return `${amount.toFixed(2)} ${currency || 'CZK'}`;
    }
}
</script>

<template>
    <Head :title="tr('Rezervace', 'Booking')" />

    <AppLayout>
        <div class="border-b border-line bg-white">
            <div class="ui-container py-5 sm:py-7">
                <Link
                    :href="route('shops.show', shop.slug)"
                    class="inline-flex min-h-11 items-center gap-2 rounded-xl text-sm font-semibold text-muted transition hover:text-brand-700"
                >
                    <ArrowLeft :size="18" aria-hidden="true" />
                    {{ tr('Zpět na profil provozovny', 'Back to shop') }}
                </Link>

                <div class="mt-3 max-w-3xl">
                    <p class="text-sm font-semibold text-brand-700">{{ tr('Rezervace u', 'Booking at') }} {{ shop.name }}</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">
                        {{ tr('Vyberte si termín bez zbytečných kroků', 'Choose a time without unnecessary steps') }}
                    </h1>
                </div>

                <ol class="mt-6 grid grid-cols-5 gap-1" :aria-label="tr('Průběh rezervace', 'Booking progress')">
                    <li v-for="step in steps" :key="step.number" class="min-w-0">
                        <div
                            class="h-1.5 rounded-full"
                            :class="step.number <= currentStep ? 'bg-brand-600' : 'bg-line'"
                        ></div>
                        <span
                            class="mt-2 hidden text-xs font-semibold sm:block"
                            :class="step.number <= currentStep ? 'text-brand-700' : 'text-muted'"
                        >{{ isEnglish ? step.en : step.cs }}</span>
                    </li>
                </ol>
            </div>
        </div>

        <div class="ui-container py-8 sm:py-10">
            <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <main class="min-w-0">
                    <form v-if="!reviewMode" class="space-y-5" @submit.prevent="openReview" novalidate>
                        <section class="ui-card p-5 sm:p-6" aria-labelledby="service-heading">
                            <div class="flex items-start gap-3">
                                <span class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-brand-100 text-sm font-bold text-brand-800">1</span>
                                <div>
                                    <h2 id="service-heading" class="text-lg font-bold text-ink">{{ tr('Vyberte službu', 'Choose a service') }}</h2>
                                    <p class="mt-1 text-sm text-muted">{{ tr('Cena i délka rezervace se odvíjí od vybrané služby.', 'The price and duration depend on the selected service.') }}</p>
                                </div>
                            </div>

                            <div v-if="services.length" class="mt-5 grid gap-3 sm:grid-cols-2">
                                <button
                                    v-for="item in services"
                                    :key="item.id"
                                    type="button"
                                    class="min-h-24 rounded-2xl border p-4 text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                                    :class="selectedService?.id === item.id
                                        ? 'border-brand-600 bg-brand-50 shadow-sm'
                                        : 'border-line bg-white hover:border-brand-300 hover:bg-brand-50/40'"
                                    :aria-pressed="selectedService?.id === item.id"
                                    @click="selectService(item)"
                                >
                                    <span class="flex items-start justify-between gap-3">
                                        <span class="min-w-0">
                                            <span class="block font-bold text-ink">{{ item.name }}</span>
                                            <span class="mt-1 block text-sm text-muted">{{ item.duration_minutes }} min</span>
                                        </span>
                                        <span class="flex items-center gap-2 font-bold text-ink">
                                            {{ formatMoney(item.price, shop.currency) }}
                                            <CheckCircle2 v-if="selectedService?.id === item.id" :size="20" class="text-brand-700" aria-hidden="true" />
                                        </span>
                                    </span>
                                </button>
                            </div>
                            <p v-else class="mt-5 rounded-xl bg-gray-50 p-4 text-sm text-muted">
                                {{ tr('Tato provozovna zatím nemá žádnou službu dostupnou k rezervaci.', 'This shop does not currently have a service available to book.') }}
                            </p>
                            <p v-if="form.errors.service_id" class="mt-3 text-sm font-medium text-danger" role="alert">{{ form.errors.service_id }}</p>
                        </section>

                        <section v-if="selectedService" class="ui-card p-5 sm:p-6" aria-labelledby="date-heading">
                            <div class="flex items-start gap-3">
                                <span class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-brand-100 text-sm font-bold text-brand-800">2</span>
                                <div>
                                    <h2 id="date-heading" class="text-lg font-bold text-ink">{{ tr('Vyberte datum', 'Choose a date') }}</h2>
                                    <p class="mt-1 text-sm text-muted">{{ tr('Zobrazíme jen skutečně dostupné časy.', 'We only show times that are actually available.') }}</p>
                                </div>
                            </div>
                            <div class="mt-5 max-w-sm">
                                <label for="booking-date" class="mb-2 block text-sm font-semibold text-ink">{{ tr('Datum návštěvy', 'Appointment date') }}</label>
                                <input
                                    id="booking-date"
                                    v-model="form.booking_date"
                                    type="date"
                                    :min="minimumDate"
                                    class="ui-field"
                                    :class="form.errors.booking_date ? 'border-danger' : ''"
                                    :aria-invalid="Boolean(form.errors.booking_date)"
                                    :aria-describedby="form.errors.booking_date ? 'booking-date-error' : undefined"
                                    required
                                    @change="handleDateChange"
                                />
                                <p v-if="form.errors.booking_date" id="booking-date-error" class="mt-2 text-sm font-medium text-danger" role="alert">
                                    {{ form.errors.booking_date }}
                                </p>
                            </div>
                        </section>

                        <section v-if="selectedService && form.booking_date" class="ui-card p-5 sm:p-6" aria-labelledby="time-heading">
                            <div class="flex items-start gap-3">
                                <span class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-brand-100 text-sm font-bold text-brand-800">3</span>
                                <div>
                                    <h2 id="time-heading" class="text-lg font-bold text-ink">{{ tr('Vyberte čas', 'Choose a time') }}</h2>
                                    <p class="mt-1 text-sm text-muted">{{ tr('Časy jsou uvedené v pásmu', 'Times are shown in') }} {{ availability.timezone }}.</p>
                                </div>
                            </div>

                            <div v-if="availabilityLoading" class="mt-5 flex min-h-24 items-center justify-center gap-2 rounded-xl bg-gray-50 text-sm text-muted" role="status">
                                <LoaderCircle :size="20" class="animate-spin" aria-hidden="true" />
                                {{ tr('Načítáme volné termíny…', 'Loading available times…') }}
                            </div>
                            <div v-else-if="availability.slots.length" class="mt-5 grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
                                <button
                                    v-for="slot in availability.slots"
                                    :key="slot"
                                    type="button"
                                    class="min-h-11 rounded-xl border px-3 py-2 text-sm font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                                    :class="form.start_time === slot
                                        ? 'border-brand-600 bg-brand-600 text-white'
                                        : 'border-line bg-white text-ink hover:border-brand-300 hover:bg-brand-50'"
                                    :aria-pressed="form.start_time === slot"
                                    @click="selectTime(slot)"
                                >{{ formatTime(slot) }}</button>
                            </div>
                            <div v-else class="mt-5 rounded-xl border border-line bg-gray-50 p-4 text-sm text-muted" role="status">
                                <p>{{ availabilityMessage || tr('Pro tento den nejsou dostupné žádné termíny.', 'There are no available times on this day.') }}</p>
                                <button v-if="availabilityError" type="button" class="mt-3 font-semibold text-brand-700 underline decoration-brand-300 underline-offset-4" @click="loadAvailability">
                                    {{ tr('Načíst znovu', 'Try again') }}
                                </button>
                            </div>
                            <p v-if="form.errors.start_time" class="mt-3 text-sm font-medium text-danger" role="alert">{{ form.errors.start_time }}</p>
                        </section>

                        <section v-if="form.start_time" class="ui-card p-5 sm:p-6" aria-labelledby="contact-heading">
                            <div class="flex items-start gap-3">
                                <span class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-brand-100 text-sm font-bold text-brand-800">4</span>
                                <div>
                                    <h2 id="contact-heading" class="text-lg font-bold text-ink">{{ tr('Kontaktní údaje', 'Contact details') }}</h2>
                                    <p class="mt-1 text-sm text-muted">
                                        {{ authUser
                                            ? tr('Údaje jsme předvyplnili z vašeho účtu.', 'We prefilled these details from your account.')
                                            : tr('Účet nepotřebujete. Odkaz ke správě rezervace pošleme e-mailem.', 'You do not need an account. We will email you a secure management link.') }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label for="full-name" class="mb-2 block text-sm font-semibold text-ink">{{ tr('Jméno a příjmení', 'Full name') }}</label>
                                    <input
                                        id="full-name"
                                        v-model="form.full_name"
                                        type="text"
                                        autocomplete="name"
                                        class="ui-field"
                                        :class="(localErrors.full_name || form.errors.full_name) ? 'border-danger' : ''"
                                        :aria-invalid="Boolean(localErrors.full_name || form.errors.full_name)"
                                        :aria-describedby="(localErrors.full_name || form.errors.full_name) ? 'full-name-error' : undefined"
                                        required
                                    />
                                    <p v-if="localErrors.full_name || form.errors.full_name" id="full-name-error" class="mt-2 text-sm font-medium text-danger" role="alert">
                                        {{ localErrors.full_name || form.errors.full_name }}
                                    </p>
                                </div>
                                <div>
                                    <label for="booking-email" class="mb-2 block text-sm font-semibold text-ink">{{ tr('E-mail', 'Email') }}</label>
                                    <input
                                        id="booking-email"
                                        v-model="form.email"
                                        type="email"
                                        autocomplete="email"
                                        inputmode="email"
                                        class="ui-field"
                                        :class="(localErrors.email || form.errors.email) ? 'border-danger' : ''"
                                        :aria-invalid="Boolean(localErrors.email || form.errors.email)"
                                        :aria-describedby="(localErrors.email || form.errors.email) ? 'booking-email-hint booking-email-error' : 'booking-email-hint'"
                                        required
                                    />
                                    <p id="booking-email-hint" class="mt-2 text-xs text-muted">{{ tr('Na tento e-mail přijde potvrzení a odkaz ke správě rezervace.', 'We will send confirmation and a secure management link to this email.') }}</p>
                                    <p v-if="localErrors.email || form.errors.email" id="booking-email-error" class="mt-2 text-sm font-medium text-danger" role="alert">
                                        {{ localErrors.email || form.errors.email }}
                                    </p>
                                </div>
                                <div>
                                    <label for="booking-phone" class="mb-2 block text-sm font-semibold text-ink">{{ tr('Telefon', 'Phone') }}</label>
                                    <input
                                        id="booking-phone"
                                        v-model="form.phone"
                                        type="tel"
                                        autocomplete="tel"
                                        inputmode="tel"
                                        placeholder="+420 123 456 789"
                                        class="ui-field"
                                        :class="(localErrors.phone || form.errors.phone) ? 'border-danger' : ''"
                                        :aria-invalid="Boolean(localErrors.phone || form.errors.phone)"
                                        :aria-describedby="(localErrors.phone || form.errors.phone) ? 'booking-phone-error' : undefined"
                                        required
                                    />
                                    <p v-if="localErrors.phone || form.errors.phone" id="booking-phone-error" class="mt-2 text-sm font-medium text-danger" role="alert">
                                        {{ localErrors.phone || form.errors.phone }}
                                    </p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="customer-notes" class="mb-2 block text-sm font-semibold text-ink">
                                        {{ tr('Poznámka', 'Note') }} <span class="font-normal text-muted">{{ tr('(nepovinné)', '(optional)') }}</span>
                                    </label>
                                    <textarea
                                        id="customer-notes"
                                        v-model="form.customer_notes"
                                        rows="4"
                                        maxlength="1000"
                                        class="ui-field resize-y"
                                        :class="form.errors.customer_notes ? 'border-danger' : ''"
                                        :aria-invalid="Boolean(form.errors.customer_notes)"
                                        :aria-describedby="form.errors.customer_notes ? 'customer-notes-error' : undefined"
                                        :placeholder="tr('Napište poskytovateli vše, co by měl před návštěvou vědět.', 'Tell the provider anything they should know before your visit.')"
                                    ></textarea>
                                    <p v-if="form.errors.customer_notes" id="customer-notes-error" class="mt-2 text-sm font-medium text-danger" role="alert">{{ form.errors.customer_notes }}</p>
                                </div>
                            </div>

                            <button type="submit" class="ui-button ui-button-primary mt-6 w-full sm:w-auto">
                                {{ tr('Zkontrolovat rezervaci', 'Review booking') }}
                                <ChevronRight :size="18" aria-hidden="true" />
                            </button>
                        </section>
                    </form>

                    <section v-else class="ui-card p-5 sm:p-7" aria-labelledby="review-heading">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-brand-100 text-sm font-bold text-brand-800">5</span>
                            <div>
                                <h2 id="review-heading" class="text-xl font-bold text-ink">{{ tr('Zkontrolujte rezervaci', 'Review your booking') }}</h2>
                                <p class="mt-1 text-sm text-muted">{{ tr('Po potvrzení termín závazně odešleme provozovně.', 'After confirmation, we will send the booking to the shop.') }}</p>
                            </div>
                        </div>

                        <dl class="mt-6 divide-y divide-line rounded-2xl border border-line">
                            <div class="grid gap-1 p-4 sm:grid-cols-[9rem_1fr]">
                                <dt class="text-sm text-muted">{{ tr('Služba', 'Service') }}</dt>
                                <dd class="font-semibold text-ink">{{ selectedService?.name }}</dd>
                            </div>
                            <div class="grid gap-1 p-4 sm:grid-cols-[9rem_1fr]">
                                <dt class="text-sm text-muted">{{ tr('Termín', 'Appointment') }}</dt>
                                <dd class="font-semibold text-ink">{{ formatDate(form.booking_date) }}, {{ formatTime(form.start_time) }}</dd>
                            </div>
                            <div class="grid gap-1 p-4 sm:grid-cols-[9rem_1fr]">
                                <dt class="text-sm text-muted">{{ tr('Délka a cena', 'Duration and price') }}</dt>
                                <dd class="font-semibold text-ink">{{ selectedService?.duration_minutes }} min · {{ formatMoney(selectedService?.price, shop.currency) }}</dd>
                            </div>
                            <div class="grid gap-1 p-4 sm:grid-cols-[9rem_1fr]">
                                <dt class="text-sm text-muted">{{ tr('Kontakt', 'Contact') }}</dt>
                                <dd class="min-w-0 font-semibold text-ink">
                                    <span class="block">{{ form.full_name }}</span>
                                    <span class="block break-all text-sm font-normal text-muted">{{ form.email }} · {{ form.phone }}</span>
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-5 flex items-start gap-3 rounded-xl bg-brand-50 p-4 text-sm text-brand-950">
                            <ShieldCheck :size="20" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                            <p>{{ tr('Rezervaci můžete bezplatně zrušit nejpozději 24 hodin před začátkem. Údaje o platbě po vás nyní nechceme.', 'You can cancel for free up to 24 hours before the appointment. We do not ask for payment details.') }}</p>
                        </div>

                        <div v-if="Object.keys(form.errors).length" class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-danger" role="alert">
                            {{ tr('Rezervaci se nepodařilo odeslat. Vraťte se k údajům a opravte označená pole.', 'The booking could not be submitted. Return to your details and correct the highlighted fields.') }}
                        </div>

                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button type="button" class="ui-button ui-button-secondary" @click="reviewMode = false">
                                {{ tr('Upravit údaje', 'Edit details') }}
                            </button>
                            <button type="button" class="ui-button ui-button-primary" :disabled="form.processing" @click="submitBooking">
                                <LoaderCircle v-if="form.processing" :size="18" class="animate-spin" aria-hidden="true" />
                                <Check v-else :size="18" aria-hidden="true" />
                                {{ form.processing ? tr('Odesíláme…', 'Submitting…') : tr('Potvrdit rezervaci', 'Confirm booking') }}
                            </button>
                        </div>
                    </section>
                </main>

                <aside class="ui-card p-5 lg:sticky lg:top-24" aria-labelledby="summary-heading">
                    <h2 id="summary-heading" class="font-bold text-ink">{{ tr('Shrnutí rezervace', 'Booking summary') }}</h2>
                    <p class="mt-1 text-sm text-muted">{{ shop.name }}</p>

                    <dl class="mt-5 space-y-4 text-sm">
                        <div v-if="shop.address">
                            <dt class="flex items-center gap-3 font-semibold text-ink">
                                <MapPin :size="18" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                                <span>{{ tr('Místo', 'Location') }}</span>
                            </dt>
                            <dd class="ml-[30px] mt-0.5 text-muted">{{ shop.address }}<span v-if="shop.city">, {{ shop.city }}</span></dd>
                        </div>
                        <div>
                            <dt class="flex items-center gap-3 font-semibold text-ink">
                                <CheckCircle2 :size="18" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                                <span>{{ tr('Služba', 'Service') }}</span>
                            </dt>
                            <dd class="ml-[30px] mt-0.5 text-muted">{{ selectedService?.name || tr('Zatím nevybráno', 'Not selected yet') }}</dd>
                        </div>
                        <div>
                            <dt class="flex items-center gap-3 font-semibold text-ink">
                                <CalendarDays :size="18" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                                <span>{{ tr('Datum', 'Date') }}</span>
                            </dt>
                            <dd class="ml-[30px] mt-0.5 text-muted">{{ form.booking_date ? formatDate(form.booking_date) : tr('Zatím nevybráno', 'Not selected yet') }}</dd>
                        </div>
                        <div>
                            <dt class="flex items-center gap-3 font-semibold text-ink">
                                <Clock3 :size="18" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                                <span>{{ tr('Čas a pásmo', 'Time and timezone') }}</span>
                            </dt>
                            <dd class="ml-[30px] mt-0.5 text-muted">{{ form.start_time ? formatTime(form.start_time) : tr('Zatím nevybráno', 'Not selected yet') }}<span class="block text-xs">{{ availability.timezone }}</span></dd>
                        </div>
                    </dl>

                    <div v-if="selectedService" class="mt-5 flex items-end justify-between border-t border-line pt-5">
                        <span class="text-sm text-muted">{{ tr('Celkem', 'Total') }}</span>
                        <div class="text-right">
                            <strong class="text-xl text-ink">{{ formatMoney(selectedService.price, shop.currency) }}</strong>
                            <span class="block text-xs text-muted">{{ selectedService.duration_minutes }} min</span>
                        </div>
                    </div>

                    <div class="mt-5 flex items-start gap-3 rounded-xl bg-gray-50 p-4 text-xs leading-5 text-muted">
                        <Info :size="18" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                        <p>{{ tr('Bezplatné zrušení je možné do 24 hodin před termínem.', 'Free cancellation is available up to 24 hours before the appointment.') }}</p>
                    </div>

                    <div v-if="!authUser" class="mt-4 flex items-start gap-3 text-xs leading-5 text-muted">
                        <Mail :size="18" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                        <p>{{ tr('Jako host obdržíte zabezpečený odkaz pro správu rezervace e-mailem.', 'As a guest, you will receive a secure booking management link by email.') }}</p>
                    </div>
                    <div v-else class="mt-4 flex items-start gap-3 text-xs leading-5 text-muted">
                        <UserRound :size="18" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" />
                        <p>{{ tr('Rezervaci po potvrzení najdete také ve svém účtu.', 'After confirmation, you will also find the booking in your account.') }}</p>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>
