<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StarRating from '@/Components/StarRating.vue';

const props = defineProps({
    service: { type: Object, required: true },
    related:  { type: Array,  default: () => [] },
    bookings: { type: Array,  default: () => [] },
});

// ── Offerings / filter ────────────────────────────────────────────────────────
const offerings = computed(() => props.service.offerings ?? []);

const categoryTags = computed(() =>
    [...new Set(offerings.value.map(o => o.category_tag).filter(Boolean))]
);

const activeTag = ref(null);

const filteredOfferings = computed(() =>
    activeTag.value
        ? offerings.value.filter(o => o.category_tag === activeTag.value)
        : offerings.value
);

// ── Booking sidebar ───────────────────────────────────────────────────────────
const selectedOffering = ref(null);

function toggleOffering(offering) {
    selectedOffering.value = selectedOffering.value?.id === offering.id ? null : offering;
}

function goToBooking() {
    if (!selectedOffering.value) return;
    const dateStr = selectedDay.value
        ? `${calYear.value}-${String(calMonth.value + 1).padStart(2, '0')}-${String(selectedDay.value).padStart(2, '0')}`
        : null;
    router.visit(route('services.book', props.service.slug), {
        data: {
            offering_id: selectedOffering.value.id,
            date: dateStr,
            time: selectedTime.value,
        },
    });
}

// Calendar
const today = new Date();
const calMonth = ref(today.getMonth());
const calYear  = ref(today.getFullYear());
const selectedDay = ref(today.getDate());
const selectedTime = ref(null);

const MONTH_NAMES = [
    'January','February','March','April','May','June',
    'July','August','September','October','November','December',
];

const calendarDays = computed(() => {
    const firstDay    = new Date(calYear.value, calMonth.value, 1).getDay();
    const daysInMonth = new Date(calYear.value, calMonth.value + 1, 0).getDate();
    const days = Array(firstDay).fill(null);
    for (let d = 1; d <= daysInMonth; d++) days.push(d);
    return days;
});

function prevMonth() {
    if (calMonth.value === 0) { calMonth.value = 11; calYear.value--; }
    else calMonth.value--;
    selectedDay.value = null;
    selectedTime.value = null;
}
function nextMonth() {
    if (calMonth.value === 11) { calMonth.value = 0; calYear.value++; }
    else calMonth.value++;
    selectedDay.value = null;
    selectedTime.value = null;
}

// ── Business Hours Logic ──────────────────────────────────────────────────────
const businessHours = computed(() => props.service.business_hours || []);
const DAY_NAMES_SHORT = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

function isDayAvailable(day) {
    if (!day) return false;
    const checkDate = new Date(calYear.value, calMonth.value, day);
    const todayStr = new Date(today.getFullYear(), today.getMonth(), today.getDate()).getTime();
    if (checkDate.getTime() < todayStr) return false;
    const dayOfWeek = checkDate.getDay();
    return businessHours.value.some(h => h.day_of_week === dayOfWeek);
}

const availableTimeSlots = computed(() => {
    if (!selectedDay.value || !selectedOffering.value || businessHours.value.length === 0) return [];
    const selectedDate = new Date(calYear.value, calMonth.value, selectedDay.value);
    const dayOfWeek = selectedDate.getDay();
    const dayHours = businessHours.value.find(h => h.day_of_week === dayOfWeek);
    if (!dayHours) return [];

    const fromParts = dayHours.time_from.split(':').map(Number);
    const toParts = dayHours.time_to.split(':').map(Number);
    const startMins = fromParts[0] * 60 + fromParts[1];
    const endMins = toParts[0] * 60 + toParts[1];
    const duration = selectedOffering.value.duration_minutes || 60;
    const interval = 30;

    const selectedDateStr = `${calYear.value}-${String(calMonth.value + 1).padStart(2, '0')}-${String(selectedDay.value).padStart(2, '0')}`;
    const dayBookings = props.bookings.filter(b => {
        const bDate = String(b.booking_date).split('T')[0].substring(0, 10);
        return bDate === selectedDateStr;
    }).map(b => {
        const [sh, sm] = String(b.start_time).split(':').map(Number);
        const [eh, em] = String(b.end_time).split(':').map(Number);
        return { start: sh * 60 + sm, end: eh * 60 + em };
    });

    const isToday = today.getFullYear() === calYear.value &&
                    today.getMonth() === calMonth.value &&
                    today.getDate() === selectedDay.value;
    const currentMins = today.getHours() * 60 + today.getMinutes();

    const slots = [];
    let currentSlotMins = startMins;
    while (currentSlotMins + duration <= endMins) {
        const isPast = isToday && (currentSlotMins <= currentMins + 30);
        const slotEnd = currentSlotMins + duration;
        const isOverlapping = dayBookings.some(b => currentSlotMins < b.end && slotEnd > b.start);
        if (!isPast && !isOverlapping) {
            let h = Math.floor(currentSlotMins / 60);
            let m = currentSlotMins % 60;
            const ampm = h >= 12 ? 'PM' : 'AM';
            let displayH = h % 12;
            if (displayH === 0) displayH = 12;
            slots.push(`${displayH}:${m.toString().padStart(2, '0')} ${ampm}`);
        }
        currentSlotMins += interval;
    }
    return slots;
});

// Auto-select first available date
if (!selectedDay.value || !isDayAvailable(selectedDay.value)) {
    const nextAvailable = calendarDays.value.find(d => typeof d === 'number' && isDayAvailable(d));
    selectedDay.value = nextAvailable || null;
}

// ── Review modal ──────────────────────────────────────────────────────────────
const showReviewModal = ref(false);
const reviewForm = ref({ rating: 5, text: '' });

function submitReview() {
    router.post(route('services.reviews.store', props.service.id), reviewForm.value, {
        onSuccess: () => {
            showReviewModal.value = false;
            reviewForm.value = { rating: 5, text: '' };
        },
    });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const formattedReviews = computed(() => {
    const n = props.service.reviews_count;
    if (n >= 1000) return (n / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
    return n.toLocaleString();
});

const badgeClasses = computed(() => ({
    blue:  'bg-blue-100 text-blue-700',
    gray:  'bg-gray-100 text-gray-700',
    green: 'bg-green-100 text-green-700',
}[props.service.badge_color] ?? 'bg-gray-100 text-gray-700'));

function formatPrice(price) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price || 0);
}

function formatTime(t) {
    if (!t) return '';
    const [h, m] = t.split(':').map(Number);
    const ampm = h >= 12 ? 'PM' : 'AM';
    let dh = h % 12;
    if (dh === 0) dh = 12;
    return `${dh}:${m.toString().padStart(2, '0')} ${ampm}`;
}

// ── Mock reviews ──────────────────────────────────────────────────────────────
const mockReviews = [
    {
        id: 1, name: 'Marcus Thompson', initials: 'MT',
        date: '2 days ago', verified: true, rating: 5,
        text: 'Best service in the city. The attention to detail is incredible and the team is highly professional. Worth every penny.',
    },
    {
        id: 2, name: 'Sarah Johnson', initials: 'SJ',
        date: '1 week ago', verified: true, rating: 5,
        text: 'Absolutely fantastic experience from start to finish. Highly recommend to anyone looking for quality service.',
    },
    {
        id: 3, name: 'David Chen', initials: 'DC',
        date: '2 weeks ago', verified: false, rating: 4,
        text: 'Great service overall. Very professional and punctual. Will definitely book again.',
    },
];
</script>

<template>
    <AppLayout>

        <!-- ── Hero Banner ──────────────────────────────────────────────────── -->
        <div class="relative h-80 bg-gray-900 overflow-hidden">
            <img
                v-if="service.image"
                :src="service.image"
                ::alt="$t('service.name')"
                class="w-full h-full object-cover opacity-60"
            />
            <div v-else class="w-full h-full bg-gradient-to-br from-blue-900 via-indigo-900 to-gray-900" />

            <!-- Gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent" />

            <!-- Hero content -->
            <div class="absolute bottom-0 left-0 right-0 max-w-7xl mx-auto w-full px-6 pb-6" style="left:50%;transform:translateX(-50%)">
                <div class="flex items-end justify-between gap-4">
                    <!-- Provider logo + info -->
                    <div class="flex items-end gap-4">
                        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-xl shrink-0 border border-white/20">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                            </svg>
                        </div>
                        <div class="pb-0.5">
                            <div class="flex items-center gap-3 mb-1">
                                <h1 class="text-2xl md:text-3xl font-bold text-white leading-tight">{{ service.name }}</h1>
                                <span v-if="service.badge" :class="badgeClasses" class="text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">{{ service.badge }}</span>
                            </div>
                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="flex items-center gap-1.5">
                                    <StarRating :rating="service.rating" size="sm" />
                                    <span class="text-white font-bold text-sm">{{ service.rating }}</span>
                                    <span class="text-gray-300 text-sm">({{ formattedReviews }} reviews)</span>
                                </div>
                                <span class="text-gray-500">·</span>
                                <span class="text-gray-300 text-sm font-medium">{{ service.category?.name }}</span>
                                <span v-if="service.city" class="text-gray-500">·</span>
                                <span v-if="service.city" class="text-gray-300 text-sm">{{ service.city }}, {{ service.state }}</span>
                            </div>
                        </div>
                    </div>

            </div>
        </div>
    </div>

        <!-- ── Page body ────────────────────────────────────────────────────── -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex gap-8 items-start">

                <!-- ── Left / main column ───────────────────────────────────── -->
                <div class="flex-1 min-w-0 space-y-10">

                    <!-- ════════════════ SERVICES ════════════════ -->
                    <div>
                        <!-- Header + filter tabs -->
                        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">{{ $t('Services & Pricing') }}</h2>
                                <p class="text-sm text-gray-500 mt-0.5">{{ $t('Select a service to book an appointment') }}</p>
                            </div>
                            <div v-if="categoryTags.length > 0" class="flex gap-1.5 flex-wrap">
                                <button
                                    @click="activeTag = null"
                                    :class="[
                                        'px-3.5 py-1.5 text-xs font-semibold rounded-full transition-all',
                                        activeTag === null
                                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    ]"
                                >{{ $t('All') }}</button>
                                <button
                                    v-for="tag in categoryTags"
                                    :key="tag"
                                    @click="activeTag = tag"
                                    :class="[
                                        'px-3.5 py-1.5 text-xs font-semibold rounded-full transition-all',
                                        activeTag === tag
                                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    ]"
                                >{{ tag }}</button>
                            </div>
                        </div>

                        <!-- Offering rows -->
                        <div class="space-y-3">
                            <div
                                v-for="offering in filteredOfferings"
                                :key="offering.id"
                                @click="toggleOffering(offering)"
                                class="bg-white rounded-2xl border-2 p-5 transition-all cursor-pointer group"
                                :class="selectedOffering?.id === offering.id
                                    ? 'border-blue-500 bg-blue-50/30 shadow-lg shadow-blue-100/50'
                                    : 'border-gray-100 hover:border-blue-200 hover:shadow-md'"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <h3
                                                class="font-bold text-sm"
                                                :class="selectedOffering?.id === offering.id ? 'text-blue-700' : 'text-gray-900'"
                                            >{{ offering.name }}</h3>
                                            <span v-if="offering.is_popular" class="text-[10px] text-amber-600 font-bold uppercase tracking-wider bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">{{ $t('★ Popular') }}</span>
                                        </div>
                                        <p v-if="offering.description" class="text-sm text-gray-500 mb-2.5 leading-relaxed">{{ offering.description }}</p>
                                        <div class="flex items-center gap-3 text-xs text-gray-400">
                                            <span class="flex items-center gap-1 bg-gray-50 px-2 py-1 rounded-md">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ offering.duration_minutes }} mins
                                            </span>
                                            <span v-if="offering.staff_level" class="flex items-center gap-1 bg-gray-50 px-2 py-1 rounded-md">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                {{ offering.staff_level }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Price + indicator -->
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span
                                            class="text-lg font-bold"
                                            :class="selectedOffering?.id === offering.id ? 'text-blue-600' : 'text-gray-900'"
                                        >
                                            {{ formatPrice(offering.price) }}
                                        </span>
                                        <div
                                            :class="[
                                                'w-8 h-8 rounded-full flex items-center justify-center transition-all',
                                                selectedOffering?.id === offering.id
                                                    ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md transform hover:-translate-y-0.5 transition-all hover:from-blue-700 hover:to-indigo-700'
                                                    : 'border-2 border-gray-200 text-gray-400 group-hover:border-blue-300 group-hover:text-blue-400'
                                            ]"
                                        >
                                            <svg v-if="selectedOffering?.id === offering.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p v-if="filteredOfferings.length === 0" class="text-center py-10 text-sm text-gray-400">{{ $t('No services found for this category.') }}</p>
                        </div>
                    </div>

                    <!-- ════════════════ REVIEWS ════════════════ -->
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">{{ $t('What people are saying') }}</h2>
                                <p class="text-sm text-gray-500 mt-0.5">{{ formattedReviews }} reviews from verified customers</p>
                            </div>
                            <button @click="showReviewModal = true" class="flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-xl">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>{{ $t('Write a review') }}</button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="review in mockReviews"
                                :key="review.id"
                                class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition-shadow"
                            >
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-xs font-bold text-white shrink-0 shadow-sm">
                                            {{ review.initials }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900 text-sm">{{ review.name }}</div>
                                            <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-0.5">
                                                <span>{{ review.date }}</span>
                                                <span v-if="review.verified" class="inline-flex items-center gap-1 text-green-600 font-medium">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $t('Verified') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <StarRating :rating="review.rating" size="sm" />
                                </div>
                                <p class="text-sm text-gray-600 leading-relaxed pl-[52px]">{{ review.text }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════ ABOUT ════════════════ -->
                    <div>
                        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                                <h2 class="text-lg font-bold text-gray-900">{{ $t('About') }}</h2>
                            </div>
                            <div class="p-6">
                                <p class="text-gray-600 leading-relaxed text-sm mb-6">{{ service.description }}</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Location -->
                                    <div v-if="service.address || service.city" class="flex gap-3.5 p-4 bg-gray-50 rounded-xl">
                                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-gray-900 text-sm mb-0.5">{{ $t('Location') }}</div>
                                            <div class="text-gray-500 text-sm">
                                                {{ service.address ?? (service.city + ', ' + service.state) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="service.is_online_only" class="flex gap-3.5 p-4 bg-blue-50 rounded-xl">
                                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-gray-900 text-sm mb-0.5">{{ $t('Online Service') }}</div>
                                            <div class="text-gray-500 text-sm">{{ $t('This service is provided remotely') }}</div>
                                        </div>
                                    </div>

                                    <!-- Business Hours -->
                                    <div v-if="businessHours.length > 0" class="flex gap-3.5 p-4 bg-gray-50 rounded-xl">
                                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold text-gray-900 text-sm mb-1.5">{{ $t('Business Hours') }}</div>
                                            <div class="space-y-0.5">
                                                <div v-for="bh in businessHours" :key="bh.day_of_week" class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-500 w-10">{{ DAY_NAMES_SHORT[bh.day_of_week] }}</span>
                                                    <span class="text-gray-700 font-medium">{{ formatTime(bh.time_from) }} – {{ formatTime(bh.time_to) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /left column -->

                <!-- ── Right sidebar ─────────────────────────────────────────── -->
                <div class="w-80 xl:w-[340px] shrink-0 sticky top-20">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-lg overflow-hidden">

                        <!-- Sidebar header -->
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-base">{{ $t('Book Appointment') }}</h3>
                                    <p class="text-xs text-blue-100">{{ $t('Pick a service, date & time') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 space-y-5">

                            <!-- Selected service chip -->
                            <div
                                v-if="selectedOffering"
                                class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl px-4 py-3"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 truncate">{{ selectedOffering.name }}</div>
                                        <div class="text-xs text-gray-500">{{ selectedOffering.duration_minutes }} mins · {{ formatPrice(selectedOffering.price) }}</div>
                                    </div>
                                </div>
                                <button @click="selectedOffering = null" class="text-gray-400 hover:text-red-500 ml-2 shrink-0 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div v-else class="flex items-center gap-3 text-sm text-gray-500 bg-gray-50 border border-dashed border-gray-300 rounded-xl px-4 py-4">
                                <div class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                    </svg>
                                </div>
                                <span>{{ $t('Select a service from the list') }}</span>
                            </div>

                            <!-- Select Date -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-bold text-gray-900">{{ $t('Select Date') }}</span>
                                    <span class="text-sm font-semibold text-blue-600">
                                        {{ MONTH_NAMES[calMonth] }} {{ calYear }}
                                    </span>
                                </div>

                                <!-- Calendar header -->
                                <div class="flex items-center justify-between mb-2 bg-gray-50 rounded-xl p-2">
                                    <button @click="prevMonth" class="p-1.5 rounded-lg hover:bg-white hover:shadow-sm text-gray-500 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <div class="grid grid-cols-7 flex-1 mx-2">
                                        <div v-for="d in ['S','M','T','W','T','F','S']" :key="d"
                                            class="text-center text-xs font-bold text-gray-400 py-1">{{ d }}</div>
                                    </div>
                                    <button @click="nextMonth" class="p-1.5 rounded-lg hover:bg-white hover:shadow-sm text-gray-500 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Calendar grid -->
                                <div class="grid grid-cols-7 gap-1">
                                    <div
                                        v-for="(day, idx) in calendarDays"
                                        :key="idx"
                                        class="aspect-square flex items-center justify-center p-0.5"
                                    >
                                        <button
                                            v-if="day"
                                            @click="isDayAvailable(day) ? (selectedDay = day, selectedTime = null) : null"
                                            :disabled="!isDayAvailable(day)"
                                            :class="[
                                                'w-full h-full rounded-full text-sm font-medium transition-all flex items-center justify-center',
                                                !isDayAvailable(day)
                                                    ? 'text-gray-300 cursor-not-allowed'
                                                    : selectedDay === day
                                                        ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg transform hover:-translate-y-0.5 transition-all hover:from-blue-700 hover:to-indigo-700'
                                                        : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'
                                            ]"
                                        >{{ day }}</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Available Times -->
                            <div>
                                <div class="text-sm font-bold text-gray-900 mb-3">{{ $t('Available Times') }}</div>

                                <div v-if="!selectedOffering" class="text-sm text-gray-500 italic py-2">{{ $t('Please select a service first.') }}</div>
                                <div v-else-if="!selectedDay" class="text-sm text-gray-500 italic py-2">{{ $t('Please select an available date.') }}</div>
                                <div v-else-if="availableTimeSlots.length === 0" class="text-sm text-gray-500 p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ $t('No slots available for this date.') }}</span>
                                </div>
                                <div v-else class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                    <button
                                        v-for="slot in availableTimeSlots"
                                        :key="slot"
                                        @click="selectedTime = slot"
                                        :class="[
                                            'py-2.5 text-sm font-semibold rounded-xl border-2 transition-all',
                                            selectedTime === slot
                                                ? 'border-blue-500 text-blue-600 bg-blue-50 shadow-sm'
                                                : 'border-gray-100 text-gray-600 hover:border-blue-200 hover:text-blue-500 hover:bg-blue-50/30'
                                        ]"
                                    >{{ slot }}</button>
                                </div>
                            </div>

                            <!-- Total Price -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm font-medium text-gray-500">{{ $t('Total') }}</span>
                                <span class="text-2xl font-bold text-gray-900">
                                    {{ selectedOffering ? formatPrice(selectedOffering.price) : '—' }}
                                </span>
                            </div>

                            <!-- CTA -->
                            <button
                                :disabled="!selectedOffering || !selectedTime || !selectedDay"
                                @click="goToBooking"
                                :class="[
                                    'w-full py-4 rounded-xl text-base font-bold transition-all duration-200 flex items-center justify-center gap-2',
                                    selectedOffering && selectedTime && selectedDay
                                        ? 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-lg shadow-blue-200/50 hover:shadow-xl hover:shadow-blue-300/50 transform hover:-translate-y-0.5'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                ]"
                            >
                                <span v-if="selectedOffering && selectedTime && selectedDay">{{ $t('Book Now') }}</span>
                                <span v-else-if="!selectedOffering">{{ $t('Select a Service') }}</span>
                                <span v-else-if="!selectedDay">{{ $t('Select a Date') }}</span>
                                <span v-else>{{ $t('Select a Time Slot') }}</span>
                                <svg v-if="selectedOffering && selectedTime && selectedDay" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </button>

                            <p class="text-xs text-gray-400 text-center leading-relaxed">{{ $t('Free cancellation up to 24 hours before your appointment') }}</p>
                        </div>
                    </div>
                </div><!-- /sidebar -->

            </div>
        </div>

        <!-- ── Review Modal ──────────────────────────────────────────────────── -->
        <teleport to="body">
            <transition
                enter-active-class="transition-opacity duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0"
            >
                <div v-if="showReviewModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showReviewModal = false">
                    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white">{{ $t('Write a Review') }}</h3>
                                    <p class="text-xs text-blue-100">Share your experience with {{ service.name }}</p>
                                </div>
                            </div>
                            <button @click="showReviewModal = false" class="text-white/70 hover:text-white transition-colors p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <form @submit.prevent="submitReview" class="p-6 space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $t('Rating') }}</label>
                                <div class="flex gap-1.5">
                                    <button
                                        v-for="star in 5"
                                        :key="star"
                                        type="button"
                                        @click="reviewForm.rating = star"
                                        class="transition-transform hover:scale-110"
                                    >
                                        <svg class="w-8 h-8" :class="star <= reviewForm.rating ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ $t('Your Review') }}</label>
                                <textarea v-model="reviewForm.text" rows="4" :placeholder="$t('Tell others about your experience...')" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm resize-none focus:bg-white transition-colors" required></textarea>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" @click="showReviewModal = false" class="flex-1 border border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold py-2.5 rounded-xl transition-colors text-sm">{{ $t('Cancel') }}</button>
                                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 rounded-xl transition-all shadow-md text-sm">{{ $t('Submit Review') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </transition>
        </teleport>

    </AppLayout>
</template>
