<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BookmarkButton from '@/Components/BookmarkButton.vue';
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
    
    // Auto-select first available day in the new month
    selectedDay.value = null;
    selectedTime.value = null;
}
function nextMonth() {
    if (calMonth.value === 11) { calMonth.value = 0; calYear.value++; }
    else calMonth.value++;
    
    // Auto-select first available day in the new month
    selectedDay.value = null;
    selectedTime.value = null;
}

// ── Business Hours Logic ──────────────────────────────────────────────────────
const businessHours = computed(() => props.service.business_hours || []);

function isDayAvailable(day) {
    if (!day) return false;
    
    // Create date object for the day we're checking
    const checkDate = new Date(calYear.value, calMonth.value, day);
    
    // Strip time from today for comparison
    const todayStr = new Date(today.getFullYear(), today.getMonth(), today.getDate()).getTime();
    
    // Past days are not available
    if (checkDate.getTime() < todayStr) return false;
    
    // 0 = Sunday, 1 = Monday, etc. matching the DB
    const dayOfWeek = checkDate.getDay();
    
    // Check if the service has hours for this day of the week
    return businessHours.value.some(h => h.day_of_week === dayOfWeek);
}

const availableTimeSlots = computed(() => {
    if (!selectedDay.value || !selectedOffering.value || businessHours.value.length === 0) return [];
    
    const selectedDate = new Date(calYear.value, calMonth.value, selectedDay.value);
    const dayOfWeek = selectedDate.getDay();
    
    const dayHours = businessHours.value.find(h => h.day_of_week === dayOfWeek);
    if (!dayHours) return []; // No hours for this day
    
    const fromParts = dayHours.time_from.split(':').map(Number);
    const toParts = dayHours.time_to.split(':').map(Number);
    
    const startMins = fromParts[0] * 60 + fromParts[1];
    const endMins = toParts[0] * 60 + toParts[1];
    
    const duration = selectedOffering.value.duration_minutes || 60; // fallback if missing
    const interval = 30; // 30-minute booking intervals
    
    // If selecting today, filter out past time slots (with a 30m grace period)
    const isToday = today.getFullYear() === calYear.value && 
                    today.getMonth() === calMonth.value && 
                    today.getDate() === selectedDay.value;
                    
    const currentMins = today.getHours() * 60 + today.getMinutes();
    
    const slots = [];
    let currentSlotMins = startMins;
    
    while (currentSlotMins + duration <= endMins) {
        // Skip if it's today and the slot is too soon or in the past
        const isSlotValid = !isToday || (currentSlotMins > currentMins + 30);
        
        if (isSlotValid) {
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

// Auto-select first available date if nothing is selected
if (!selectedDay.value || !isDayAvailable(selectedDay.value)) {
    const nextAvailable = calendarDays.value.find(d => typeof d === 'number' && isDayAvailable(d));
    if (nextAvailable) {
        selectedDay.value = nextAvailable;
    } else {
        selectedDay.value = null; // Whole month might be booked/unavailable
    }
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
                    <BookmarkButton
                        :service-id="service.id"
                        :initial-bookmarked="service.is_bookmarked"
                        size="md"
                    />
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
                                @click="toggleOffering(offering)"
                                class="bg-white rounded-xl border p-5 transition-all cursor-pointer hover:shadow-md"
                                :class="selectedOffering?.id === offering.id
                                    ? 'border-blue-400 bg-blue-50/40 shadow-md'
                                    : 'border-gray-200 hover:border-blue-300'"
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
                                                'w-8 h-8 rounded-lg flex items-center justify-center transition',
                                                selectedOffering?.id === offering.id
                                                    ? 'bg-blue-600 text-white'
                                                    : 'border border-gray-300 text-gray-400'
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

                            <p v-if="filteredOfferings.length === 0" class="text-center py-10 text-sm text-gray-400">
                                No services found for this category.
                            </p>
                        </div>
                    </div>

                    <!-- ════════════════ REVIEWS ════════════════ -->
                    <div class="mt-10 mb-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-base font-bold text-gray-900">What people are saying</h2>
                            <button class="text-sm text-blue-600 font-medium hover:underline">Write a review</button>
                        </div>

                        <div class="space-y-5">
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
                    <div class="mt-10">

                        <!-- About the Shop card -->
                        <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl border border-gray-200 p-8 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h2 class="text-lg font-bold text-gray-900">About the Shop</h2>
                            </div>
                            
                            <p class="text-gray-600 leading-relaxed mb-8 text-base">{{ service.description }}</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Location -->
                                <div class="flex gap-4 p-4 bg-white rounded-xl border border-gray-100">
                                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 mb-1">Location</div>
                                        <div class="text-gray-600 text-sm mb-2">
                                            {{ service.address ?? (service.city + ', ' + service.state) }}
                                        </div>
                                        <a href="#" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                            View on Map
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>

                                <!-- Opening Hours -->
                                <div class="flex gap-4 p-4 bg-white rounded-xl border border-gray-100">
                                    <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 mb-1">Opening Hours</div>
                                        <div class="text-gray-600 text-sm leading-relaxed">
                                            {{ service.opening_hours ?? 'Mon - Fri: 9:00 AM - 6:00 PM' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Badge pill -->
                            <div v-if="service.badge" class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-500">Recognition:</span>
                                    <span :class="badgeClasses" class="text-xs font-semibold px-3 py-1.5 rounded-full uppercase tracking-wide">
                                        {{ service.badge }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /left column -->

                <!-- ── Right sidebar ─────────────────────────────────────────── -->
                <div class="w-72 xl:w-80 shrink-0 sticky top-20">
                    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">

                        <div class="flex items-center gap-2 mb-5 pb-4 border-b border-gray-100">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-gray-900 text-base">Book Appointment</h3>
                        </div>

                        <!-- Selected service chip -->
                        <div
                            v-if="selectedOffering"
                            class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 mb-5"
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
                                    <div class="text-xs text-gray-500">{{ selectedOffering.duration_minutes }} mins • {{ formatPrice(selectedOffering.price) }}</div>
                                </div>
                            </div>
                            <button @click="selectedOffering = null" class="text-gray-400 hover:text-red-500 ml-2 shrink-0 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div v-else class="flex items-center gap-3 text-sm text-gray-500 bg-gray-50 border border-dashed border-gray-300 rounded-xl px-4 py-4 mb-5">
                            <div class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span>Select a service from the list</span>
                        </div>

                        <!-- Select Date -->
                        <div class="mb-5">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-semibold text-gray-800">Select Date</span>
                                <span class="text-sm font-bold text-blue-600">
                                    {{ MONTH_NAMES[calMonth] }} {{ calYear }}
                                </span>
                            </div>

                            <!-- Calendar header -->
                            <div class="flex items-center justify-between mb-2 bg-gray-50 rounded-lg p-2">
                                <button @click="prevMonth" class="p-1.5 rounded-lg hover:bg-white hover:shadow-sm text-gray-600 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <div class="grid grid-cols-7 flex-1 mx-2">
                                    <div v-for="d in ['S','M','T','W','T','F','S']" :key="d"
                                        class="text-center text-xs font-semibold text-gray-500 py-1">{{ d }}</div>
                                </div>
                                <button @click="nextMonth" class="p-1.5 rounded-lg hover:bg-white hover:shadow-sm text-gray-600 transition-all">
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
                                        @click="isDayAvailable(day) ? selectedDay = day : null"
                                        :disabled="!isDayAvailable(day)"
                                        :class="[
                                            'w-full h-full rounded-full text-sm font-medium transition-all flex items-center justify-center',
                                            !isDayAvailable(day)
                                                ? 'text-gray-300 cursor-not-allowed'
                                                : selectedDay === day
                                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-200'
                                                    : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'
                                        ]"
                                    >{{ day }}</button>
                                </div>
                            </div>
                        </div>

                        <!-- Available Times -->
                        <div class="mb-5">
                            <div class="text-sm font-semibold text-gray-800 mb-3">Available Times</div>
                            
                            <div v-if="!selectedOffering" class="text-sm text-gray-500 italic py-2">
                                Please select a service package first.
                            </div>
                            <div v-else-if="!selectedDay" class="text-sm text-gray-500 italic py-2">
                                Please select an available date.
                            </div>
                            <div v-else-if="availableTimeSlots.length === 0" class="text-sm text-gray-500 py-2 p-3 bg-gray-50 rounded-lg border border-gray-100 flex items-start gap-2">
                                <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>No time slots available for this date. The selected package takes {{ selectedOffering.duration_minutes }} mins.</span>
                            </div>
                            <div v-else class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                <button
                                    v-for="slot in availableTimeSlots"
                                    :key="slot"
                                    @click="selectedTime = slot"
                                    :class="[
                                        'py-2 text-sm font-medium rounded-xl border-2 transition-all',
                                        selectedTime === slot
                                            ? 'border-blue-600 text-blue-600 bg-blue-50 shadow-sm'
                                            : 'border-gray-200 text-gray-600 hover:border-blue-300 hover:text-blue-500 hover:bg-gray-50'
                                    ]"
                                >{{ slot }}</button>
                            </div>
                        </div>

                        <!-- Total Price -->
                        <div class="flex items-center justify-between mb-5 pt-4 border-t border-gray-200">
                            <span class="text-sm font-medium text-gray-600">Total Price</span>
                            <span class="text-2xl font-bold text-blue-600">
                                {{ selectedOffering ? formatPrice(selectedOffering.price) : '—' }}
                            </span>
                        </div>

                        <!-- CTA -->
                        <button
                            :disabled="!selectedOffering || !selectedTime || !selectedDay"
                            @click="goToBooking"
                            :class="[
                                'w-full py-4 rounded-xl text-base font-bold transition-all duration-200 flex items-center justify-center gap-2 shadow-sm',
                                selectedOffering && selectedTime && selectedDay
                                    ? 'bg-blue-600 hover:bg-blue-700 hover:shadow-md hover:shadow-blue-200 text-white transform hover:-translate-y-0.5'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            ]"
                        >
                            <span v-if="selectedOffering && selectedTime && selectedDay">Confirm &amp; Pay</span>
                            <span v-else-if="!selectedOffering">Select a Service</span>
                            <span v-else-if="!selectedDay">Select a Date</span>
                            <span v-else>Select a Time Slot</span>
                            <svg v-if="selectedOffering && selectedTime && selectedDay" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>

                        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500 text-center leading-relaxed">
                                By booking, you agree to our <span class="font-medium text-gray-700">24-hour cancellation policy</span>. No-show fees may apply.
                            </p>
                        </div>

                        <!-- Secure badge -->
                        <div class="flex items-center justify-center gap-2 mt-4 pt-3 border-t border-gray-100">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span class="text-xs font-medium text-gray-500">Secure SSL Encrypted Payment</span>
                        </div>

                    </div>
                </div><!-- /sidebar -->

            </div>
        </div>
    </AppLayout>
</template>
