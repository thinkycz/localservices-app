<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StarRating from '@/Components/StarRating.vue';

const props = defineProps({
    service: { type: Object, required: true },
    related:  { type: Array,  default: () => [] },
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
}
function nextMonth() {
    if (calMonth.value === 11) { calMonth.value = 0; calYear.value++; }
    else calMonth.value++;
    selectedDay.value = null;
}

const TIME_SLOTS = ['10:00 AM', '11:30 AM', '1:00 PM', '2:30 PM', '4:00 PM', '5:30 PM'];
const selectedTime = ref('1:00 PM');

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
    return price % 1 === 0 ? `$${price.toFixed(0)}.00` : `$${price.toFixed(2)}`;
}

// ── Mock reviews ──────────────────────────────────────────────────────────────
const mockReviews = [
    {
        id: 1, name: 'Marcus Thompson', initials: 'MT',
        date: '2 days ago', verified: true, rating: 5,
        text: '"Best service in the city. The attention to detail is incredible and the team is highly professional. Worth every penny."',
    },
    {
        id: 2, name: 'Sarah Johnson', initials: 'SJ',
        date: '1 week ago', verified: true, rating: 5,
        text: '"Absolutely fantastic experience from start to finish. Highly recommend to anyone looking for quality service."',
    },
    {
        id: 3, name: 'David Chen', initials: 'DC',
        date: '2 weeks ago', verified: false, rating: 4,
        text: '"Great service overall. Very professional and punctual. Will definitely book again."',
    },
];
</script>

<template>
    <AppLayout>

        <!-- ── Hero Banner ──────────────────────────────────────────────────── -->
        <div class="relative h-72 bg-gray-800 overflow-hidden">
            <img
                v-if="service.image"
                :src="service.image"
                :alt="service.name"
                class="w-full h-full object-cover opacity-70"
            />
            <div v-else class="w-full h-full bg-gradient-to-br from-gray-600 to-gray-900" />

            <!-- Gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent" />

            <!-- Hero content -->
            <div class="absolute bottom-0 left-0 right-0 px-6 pb-5 flex items-end justify-between max-w-7xl mx-auto w-full" style="left:50%;transform:translateX(-50%)">
                <!-- Provider logo + info -->
                <div class="flex items-end gap-4">
                    <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center shadow-lg shrink-0 border border-gray-100">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white leading-tight">{{ service.name }}</h1>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <StarRating :rating="service.rating" size="sm" />
                            <span class="text-white font-semibold text-sm">{{ service.rating }}</span>
                            <span class="text-gray-300 text-sm">({{ formattedReviews }} reviews)</span>
                            <span class="text-gray-400">•</span>
                            <span class="text-gray-200 text-sm">{{ service.category?.name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Share + Favorite -->
                <div class="flex items-center gap-2 shrink-0">
                    <button class="flex items-center gap-1.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white text-sm font-medium px-4 py-2 rounded-lg transition border border-white/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        Share
                    </button>
                    <button class="flex items-center gap-1.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        Favorite
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Page body ────────────────────────────────────────────────────── -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex gap-6 items-start">

                <!-- ── Left / main column ───────────────────────────────────── -->
                <div class="flex-1 min-w-0">

                    <!-- ════════════════ SERVICES ════════════════ -->
                    <div>

                        <!-- Header + filter tabs -->
                        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                            <h2 class="text-base font-bold text-gray-900">Services &amp; Pricing</h2>
                            <div v-if="categoryTags.length > 0" class="flex gap-1 flex-wrap">
                                <button
                                    @click="activeTag = null"
                                    :class="[
                                        'px-3 py-1 text-xs font-medium rounded-full transition',
                                        activeTag === null
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    ]"
                                >All</button>
                                <button
                                    v-for="tag in categoryTags"
                                    :key="tag"
                                    @click="activeTag = tag"
                                    :class="[
                                        'px-3 py-1 text-xs font-medium rounded-full transition',
                                        activeTag === tag
                                            ? 'bg-blue-600 text-white'
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
                                class="bg-white rounded-xl border p-5 transition-colors"
                                :class="selectedOffering?.id === offering.id
                                    ? 'border-blue-400 bg-blue-50/40'
                                    : 'border-gray-200'"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3
                                                class="font-bold text-sm"
                                                :class="selectedOffering?.id === offering.id ? 'text-blue-600' : 'text-gray-900'"
                                            >{{ offering.name }}</h3>
                                            <span v-if="offering.is_popular" class="text-xs text-blue-600 font-medium">• Popular</span>
                                        </div>
                                        <p class="text-sm text-gray-500 mb-2 leading-relaxed">{{ offering.description }}</p>
                                        <div class="flex items-center gap-3 text-xs text-gray-400">
                                            <span class="flex items-center gap-1">
                                                <!-- clock -->
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ offering.duration_minutes }} mins
                                            </span>
                                            <span v-if="offering.staff_level" class="flex items-center gap-1">
                                                <!-- person -->
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                {{ offering.staff_level }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Price + toggle -->
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span
                                            class="text-lg font-bold"
                                            :class="selectedOffering?.id === offering.id ? 'text-blue-600' : 'text-gray-900'"
                                        >
                                            {{ formatPrice(offering.price) }}
                                        </span>
                                        <button
                                            @click="toggleOffering(offering)"
                                            :class="[
                                                'w-8 h-8 rounded-lg flex items-center justify-center transition',
                                                selectedOffering?.id === offering.id
                                                    ? 'bg-blue-600 text-white'
                                                    : 'border border-gray-300 text-gray-400 hover:border-blue-400 hover:text-blue-500'
                                            ]"
                                        >
                                            <svg v-if="selectedOffering?.id === offering.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <p v-if="filteredOfferings.length === 0" class="text-center py-10 text-sm text-gray-400">
                                No services found for this category.
                            </p>
                        </div>
                    </div>

                    <!-- ════════════════ REVIEWS ════════════════ -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-base font-bold text-gray-900">What people are saying</h2>
                            <button class="text-sm text-blue-600 font-medium hover:underline">Write a review</button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="review in mockReviews"
                                :key="review.id"
                                class="bg-white rounded-xl border border-gray-200 p-5"
                            >
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-sm font-bold text-gray-600 shrink-0">
                                            {{ review.initials }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900 text-sm">{{ review.name }}</div>
                                            <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-0.5">
                                                <span>{{ review.date }}</span>
                                                <span v-if="review.verified" class="text-green-600 font-medium">• Verified Booking</span>
                                            </div>
                                        </div>
                                    </div>
                                    <StarRating :rating="review.rating" size="sm" />
                                </div>
                                <p class="text-sm text-gray-700 leading-relaxed">{{ review.text }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════ ABOUT ════════════════ -->
                    <div>

                        <!-- About the Shop card -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                            <h2 class="text-base font-bold text-gray-900 mb-3">About the Shop</h2>
                            <p class="text-sm text-gray-600 leading-relaxed mb-5">{{ service.description }}</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <!-- Location -->
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-800 mb-0.5">Location</div>
                                        <div class="text-sm text-gray-600">
                                            {{ service.address ?? (service.city + ', ' + service.state) }}
                                        </div>
                                        <a href="#" class="text-sm text-blue-600 hover:underline mt-1 inline-block">View on Map</a>
                                    </div>
                                </div>

                                <!-- Opening Hours -->
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-800 mb-0.5">Opening Hours</div>
                                        <div class="text-sm text-gray-600 leading-relaxed">
                                            {{ service.opening_hours ?? 'Mon - Fri: 9:00 AM - 6:00 PM' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Badge pill -->
                        <div v-if="service.badge">
                            <span :class="badgeClasses" class="text-xs font-semibold px-3 py-1.5 rounded-full uppercase tracking-wide">
                                {{ service.badge }}
                            </span>
                        </div>
                    </div>

                </div><!-- /left column -->

                <!-- ── Right sidebar ─────────────────────────────────────────── -->
                <div class="w-72 xl:w-80 shrink-0 sticky top-20">
                    <div class="bg-white rounded-xl border border-gray-200 p-5">

                        <h3 class="font-bold text-gray-900 text-base">Book Appointment</h3>

                        <!-- Selected service chip -->
                        <div
                            v-if="selectedOffering"
                            class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 mb-4"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" />
                                </svg>
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold text-gray-800 truncate">{{ selectedOffering.name }}</div>
                                    <div class="text-xs text-gray-500">{{ selectedOffering.duration_minutes }} mins • {{ formatPrice(selectedOffering.price) }}</div>
                                </div>
                            </div>
                            <button @click="selectedOffering = null" class="text-gray-400 hover:text-gray-600 ml-2 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div v-else class="text-xs text-gray-400 bg-gray-50 border border-dashed border-gray-200 rounded-lg px-3 py-3 mb-4 text-center">
                            Select a service from the Services tab
                        </div>

                        <!-- Select Date -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-700">Select Date</span>
                                <span class="text-xs font-semibold text-blue-600">
                                    {{ MONTH_NAMES[calMonth] }} {{ calYear }}
                                </span>
                            </div>

                            <!-- Calendar header -->
                            <div class="flex items-center justify-between mb-1">
                                <button @click="prevMonth" class="p-1 rounded hover:bg-gray-100 text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <div class="grid grid-cols-7 flex-1 mx-1">
                                    <div v-for="d in ['S','M','T','W','T','F','S']" :key="d"
                                        class="text-center text-xs font-medium text-gray-400 py-1">{{ d }}</div>
                                </div>
                                <button @click="nextMonth" class="p-1 rounded hover:bg-gray-100 text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Calendar grid -->
                            <div class="grid grid-cols-7 gap-0.5">
                                <div
                                    v-for="(day, idx) in calendarDays"
                                    :key="idx"
                                    class="aspect-square flex items-center justify-center"
                                >
                                    <button
                                        v-if="day"
                                        @click="selectedDay = day"
                                        :class="[
                                            'w-7 h-7 rounded-full text-xs font-medium transition',
                                            selectedDay === day
                                                ? 'bg-blue-600 text-white'
                                                : 'text-gray-700 hover:bg-gray-100'
                                        ]"
                                    >{{ day }}</button>
                                </div>
                            </div>
                        </div>

                        <!-- Available Times -->
                        <div class="mb-4">
                            <div class="text-xs font-semibold text-gray-700 mb-2">Available Times</div>
                            <div class="grid grid-cols-3 gap-1.5">
                                <button
                                    v-for="slot in TIME_SLOTS"
                                    :key="slot"
                                    @click="selectedTime = slot"
                                    :class="[
                                        'py-1.5 text-xs font-medium rounded-lg border transition',
                                        selectedTime === slot
                                            ? 'border-blue-600 text-blue-600 bg-blue-50'
                                            : 'border-gray-200 text-gray-600 hover:border-blue-300 hover:text-blue-500'
                                    ]"
                                >{{ slot }}</button>
                            </div>
                        </div>

                        <!-- Total Price -->
                        <div class="flex items-center justify-between mb-4 pt-3 border-t border-gray-100">
                            <span class="text-sm font-medium text-gray-700">Total Price</span>
                            <span class="text-xl font-bold text-blue-600">
                                {{ selectedOffering ? formatPrice(selectedOffering.price) : '—' }}
                            </span>
                        </div>

                        <!-- CTA -->
                        <button
                            :disabled="!selectedOffering"
                            @click="goToBooking"
                            :class="[
                                'w-full py-3 rounded-xl text-sm font-bold transition flex items-center justify-center gap-2',
                                selectedOffering
                                    ? 'bg-blue-600 hover:bg-blue-700 text-white'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            ]"
                        >
                            Confirm &amp; Pay
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </button>

                        <p class="text-xs text-gray-400 text-center mt-3 leading-relaxed">
                            By booking, you agree to our 24-hour cancellation policy. No-show fees may apply.
                        </p>

                        <!-- Secure badge -->
                        <div class="flex items-center justify-center gap-1.5 mt-3 text-xs text-gray-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Secure Booking Powered by LocalServices
                        </div>

                    </div>
                </div><!-- /sidebar -->

            </div>
        </div>
    </AppLayout>
</template>
