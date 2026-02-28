<script setup>
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    bookings: { type: Array, default: () => [] },
    weekDays: { type: Array, default: () => [] },
    weekRange: { type: String, default: '' },
    weekStats: { type: Object, default: () => ({}) },
    currentView: { type: String, default: 'week' },
    filters: { type: Object, default: () => ({}) },
});

const currentView = ref(props.currentView || 'week');
watch(
    () => props.currentView,
    (v) => {
        currentView.value = v || 'week';
    }
);

const hourHeight = 80;

const startHour = computed(() => {
    if (!bookings.value.length) return 8;
    const minHour = Math.min(...bookings.value.map(b => b.startHour));
    return Math.min(minHour, 6);
});

const endHour = computed(() => {
    if (!bookings.value.length) return 18;
    const maxHour = Math.max(...bookings.value.map(b => {
        const endH = b.startHour + Math.ceil((b.startMin + b.duration) / 60);
        return endH;
    }));
    return Math.max(maxHour, 18);
});

const timeSlots = computed(() => {
    const slots = [];
    for (let h = startHour.value; h <= endHour.value; h++) {
        const period = h < 12 ? 'AM' : 'PM';
        const display = h > 12 ? h - 12 : h === 0 ? 12 : h;
        slots.push(`${String(display).padStart(2, '0')}:00 ${period}`);
    }
    return slots;
});

const gridHeight = computed(() => (endHour.value - startHour.value) * hourHeight);

const bookings = computed(() => props.bookings || []);

const selectedBooking = ref(null);

function selectBooking(booking) {
    if (booking.colorType === 'blocked') return;
    selectedBooking.value = booking;
}

function closeDetails() {
    selectedBooking.value = null;
}

function getBookingsForDay(day) {
    return bookings.value.filter((b) => b.fullDate === day.fullDate);
}

function parseISODate(iso) {
    return iso ? new Date(`${iso}T00:00:00`) : new Date();
}

function toISODate(date) {
    const d = new Date(date);
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().split('T')[0];
}

function addDays(date, days) {
    const d = new Date(date);
    d.setDate(d.getDate() + days);
    return d;
}

function addMonths(date, months) {
    const d = new Date(date);
    d.setMonth(d.getMonth() + months);
    return d;
}

function startOfWeekMonday(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = (day + 6) % 7;
    return addDays(d, -diff);
}

function startOfMonth(date) {
    const d = new Date(date);
    return new Date(d.getFullYear(), d.getMonth(), 1);
}

function endOfMonth(date) {
    const d = new Date(date);
    return new Date(d.getFullYear(), d.getMonth() + 1, 0);
}

function navigate(direction) {
    const dir = direction === 'prev' ? -1 : 1;
    const baseDate = parseISODate(props.filters.start_date);
    const viewForNav = currentView.value === 'today' ? 'day' : currentView.value;
    if (currentView.value === 'today') currentView.value = 'day';

    let start;
    let end;

    if (viewForNav === 'month') {
        const shifted = addMonths(baseDate, dir);
        start = startOfMonth(shifted);
        end = endOfMonth(shifted);
    } else if (viewForNav === 'day') {
        start = addDays(baseDate, dir);
        end = start;
    } else {
        start = addDays(baseDate, dir * 7);
        end = addDays(start, 6);
    }

    router.get(
        route('vendor.calendar'),
        {
            start_date: toISODate(start),
            end_date: toISODate(end),
            view: viewForNav,
        },
        { preserveState: true }
    );
}

function changeView(view) {
    currentView.value = view;

    const today = new Date();
    const baseDate = view === 'today' ? today : parseISODate(props.filters.start_date);

    let start;
    let end;

    if (view === 'today' || view === 'day') {
        start = baseDate;
        end = baseDate;
    } else if (view === 'month') {
        start = startOfMonth(baseDate);
        end = endOfMonth(baseDate);
    } else {
        start = startOfWeekMonday(baseDate);
        end = addDays(start, 6);
    }

    router.get(
        route('vendor.calendar'),
        {
            start_date: toISODate(start),
            end_date: toISODate(end),
            view,
        },
        { preserveState: true }
    );
}

function getBookingTop(booking) {
    return (booking.startHour - startHour.value + booking.startMin / 60) * hourHeight;
}

function getBookingHeight(booking) {
    return (booking.duration / 60) * hourHeight;
}

const currentTimeTop = computed(() => {
    const now = new Date();
    const currentHour = now.getHours();
    const currentMin = now.getMinutes();
    return (currentHour - startHour.value + currentMin / 60) * hourHeight;
});

const currentTimeLabel = computed(() => {
    const now = new Date();
    return now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
});

const lunchBreakTop = computed(() => (12 - startHour.value) * hourHeight);

const cardStyles = {
    blue: {
        wrapper: 'bg-blue-50 border-l-4 border-blue-500',
        name: 'text-blue-800 font-semibold',
        service: 'text-blue-500',
    },
    yellow: {
        wrapper: 'bg-yellow-50 border-l-4 border-yellow-400',
        name: 'text-yellow-800 font-semibold',
        service: 'text-yellow-600',
    },
    green: {
        wrapper: 'bg-green-50 border-l-4 border-green-500',
        name: 'text-green-800 font-semibold',
        service: 'text-green-600',
    },
    red: {
        wrapper: 'bg-red-50 border-l-4 border-red-500',
        name: 'text-red-800 font-semibold',
        service: 'text-red-500',
    },
    blocked: {
        wrapper: 'border-l-4 border-gray-300',
        name: 'text-gray-500 font-medium',
        service: '',
    },
};

function getCardStyle(colorType) {
    return cardStyles[colorType] || cardStyles.blue;
}

const dayColumnClass = computed(() => (currentView.value === 'month' ? 'flex-none w-44' : 'flex-1'));
const calendarInnerClass = computed(() => (currentView.value === 'month' ? 'min-w-max' : 'min-w-full'));
</script>

<template>
    <Head title="Calendar" />

    <VendorLayout activePage="calendar">
        <div class="flex flex-col h-full gap-4">

            <!-- Toolbar -->
            <div class="flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                        <button @click="navigate('prev')" class="px-3 py-2 text-gray-500 hover:bg-gray-50 transition-colors border-r border-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button @click="navigate('next')" class="px-3 py-2 text-gray-500 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">{{ weekRange }}</h2>
                </div>

                <div class="flex items-center bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <button
                        class="px-4 py-2 text-sm font-semibold transition-colors"
                        :class="currentView === 'today' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:bg-gray-50'"
                        @click="changeView('today')"
                    >TODAY</button>
                    <button
                        class="px-4 py-2 text-sm font-semibold border-l border-gray-200 transition-colors"
                        :class="currentView === 'day' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:bg-gray-50'"
                        @click="changeView('day')"
                    >Day</button>
                    <button
                        class="px-4 py-2 text-sm font-semibold border-l border-gray-200 transition-colors"
                        :class="currentView === 'week' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:bg-gray-50'"
                        @click="changeView('week')"
                    >Week</button>
                    <button
                        class="px-4 py-2 text-sm font-semibold border-l border-gray-200 transition-colors"
                        :class="currentView === 'month' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:bg-gray-50'"
                        @click="changeView('month')"
                    >Month</button>
                </div>
            </div>

            <!-- Calendar + Details Panel -->
            <div class="flex flex-1 gap-4 overflow-hidden min-h-0">

                <!-- Calendar Grid -->
                <div class="flex-1 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col min-w-0">

                    <div class="flex-1 overflow-x-auto min-w-0">
                        <div :class="[calendarInnerClass, 'h-full flex flex-col']">

                            <!-- Day Header Row -->
                            <div class="flex border-b border-gray-100 flex-shrink-0">
                                <div class="w-20 flex-shrink-0 border-r border-gray-100"></div>
                                <div
                                    v-for="day in weekDays"
                                    :key="day.dayIndex"
                                    class="text-center py-3 border-r border-gray-100 last:border-r-0"
                                    :class="[dayColumnClass, day.isToday ? 'bg-blue-50' : '']"
                                >
                                    <div class="text-xs font-semibold text-gray-400 tracking-widest uppercase">{{ day.day }}</div>
                                    <div
                                        class="mt-1 text-xl font-bold w-9 h-9 rounded-full mx-auto flex items-center justify-center"
                                        :class="day.isToday ? 'bg-blue-600 text-white' : 'text-gray-800'"
                                    >{{ day.date }}</div>
                                </div>
                            </div>

                            <!-- Scrollable Time Grid -->
                            <div class="flex-1 overflow-y-auto min-h-0">
                                <div class="flex relative" :style="{ height: gridHeight + 'px' }">

                                    <!-- Time Labels -->
                                    <div class="w-20 flex-shrink-0 border-r border-gray-100 relative">
                                        <div
                                            v-for="(slot, idx) in timeSlots"
                                            :key="idx"
                                            class="absolute right-3 text-xs text-gray-400 font-medium whitespace-nowrap"
                                            :style="{ top: (idx * hourHeight - 8) + 'px' }"
                                        >{{ slot }}</div>
                                    </div>

                                    <!-- Day Columns -->
                                    <div class="flex flex-1 relative">
                                        <div
                                            v-for="day in weekDays"
                                            :key="day.dayIndex"
                                            class="relative border-r border-gray-100 last:border-r-0"
                                            :class="[dayColumnClass, day.isToday ? 'bg-blue-50/20' : '']"
                                        >
                                            <!-- Hour lines -->
                                            <div
                                                v-for="(_, idx) in timeSlots"
                                                :key="'hr-' + idx"
                                                class="absolute left-0 right-0 border-t border-gray-100"
                                                :style="{ top: (idx * hourHeight) + 'px' }"
                                            ></div>
                                            <!-- Half-hour lines -->
                                            <div
                                                v-for="(_, idx) in timeSlots"
                                                :key="'hf-' + idx"
                                                class="absolute left-0 right-0 border-t border-dashed border-gray-100"
                                                :style="{ top: (idx * hourHeight + hourHeight / 2) + 'px' }"
                                            ></div>

                                            <!-- Booking Cards -->
                                            <template v-for="booking in getBookingsForDay(day)" :key="booking.id">
                                                <!-- Blocked -->
                                                <div
                                                    v-if="booking.colorType === 'blocked'"
                                                    class="absolute left-1 right-1 rounded-lg overflow-hidden border-l-4 border-gray-300 cursor-default"
                                                    :style="{ top: (getBookingTop(booking) + 4) + 'px', height: (getBookingHeight(booking) - 8) + 'px' }"
                                                >
                                                    <div
                                                        class="w-full h-full flex items-center justify-center"
                                                        style="background-color:#f3f4f6;background-image:repeating-linear-gradient(45deg,transparent,transparent 6px,rgba(0,0,0,0.06) 6px,rgba(0,0,0,0.06) 12px);"
                                                    >
                                                        <span class="text-xs font-semibold text-gray-500 bg-white/80 px-2 py-0.5 rounded">
                                                            {{ booking.customer }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Regular -->
                                                <div
                                                    v-else
                                                    class="absolute left-1 right-1 rounded-lg px-2 py-1.5 cursor-pointer transition-all duration-150 hover:shadow-md"
                                                    :class="[
                                                        getCardStyle(booking.colorType).wrapper,
                                                        selectedBooking && selectedBooking.id === booking.id ? 'ring-2 ring-blue-400 ring-offset-1' : '',
                                                    ]"
                                                    :style="{ top: (getBookingTop(booking) + 4) + 'px', height: (getBookingHeight(booking) - 8) + 'px' }"
                                                    @click="selectBooking(booking)"
                                                >
                                                    <div class="flex items-start justify-between gap-1">
                                                        <span class="text-xs leading-tight truncate" :class="getCardStyle(booking.colorType).name">
                                                            {{ booking.customer }}
                                                        </span>
                                                        <span v-if="booking.status === 'pending'" class="text-amber-400 text-xs flex-shrink-0">★</span>
                                                    </div>
                                                    <div class="text-xs mt-0.5 leading-tight truncate" :class="getCardStyle(booking.colorType).service">
                                                        {{ booking.service }}
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Current time line (today only) -->
                                            <div
                                                v-if="day.isToday && currentTimeTop >= 0 && currentTimeTop <= gridHeight"
                                                class="absolute left-0 right-0 flex items-center pointer-events-none z-10"
                                                :style="{ top: currentTimeTop + 'px' }"
                                            >
                                                <div class="w-2.5 h-2.5 bg-blue-600 rounded-full -ml-1.5 flex-shrink-0"></div>
                                                <div class="flex-1 h-px bg-blue-500"></div>
                                            </div>
                                        </div>

                                        <!-- Lunch break overlay -->
                                        <div
                                            class="absolute left-0 right-0 flex items-center pointer-events-none z-20"
                                            :style="{ top: lunchBreakTop + 'px' }"
                                        >
                                            <div class="flex items-center gap-2 w-full px-2">
                                                <div class="h-px bg-gray-300 w-4 flex-shrink-0"></div>
                                                <span class="text-xs font-bold text-gray-400 tracking-widest uppercase whitespace-nowrap bg-white/90 px-1">
                                                    LUNCH BREAK
                                                </span>
                                                <div class="h-px bg-gray-300 flex-1"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Current time label in gutter -->
                                    <div
                                        v-if="currentTimeTop >= 0 && currentTimeTop <= gridHeight"
                                        class="absolute left-1 pointer-events-none z-20"
                                        :style="{ top: (currentTimeTop - 8) + 'px' }"
                                    >
                                        <span class="text-xs font-bold text-blue-600">{{ currentTimeLabel }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Details Panel -->
                <transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 translate-x-4"
                    enter-to-class="opacity-100 translate-x-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 translate-x-0"
                    leave-to-class="opacity-0 translate-x-4"
                >
                    <div
                        v-if="selectedBooking"
                        class="w-72 flex-shrink-0 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col overflow-hidden"
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
                            <h3 class="font-bold text-gray-900 text-base">Booking Details</h3>
                            <button
                                @click="closeDetails"
                                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Scrollable body -->
                        <div class="flex-1 overflow-y-auto">

                            <!-- Customer -->
                            <div class="px-5 py-4 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
                                        :class="[selectedBooking.avatarBg, selectedBooking.avatarText]"
                                    >{{ selectedBooking.initials }}</div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-sm">{{ selectedBooking.customer }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ selectedBooking.customerType }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Info rows -->
                            <div class="px-5 py-4 space-y-4 border-b border-gray-100">
                                <!-- Date & Time -->
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-0.5">Date &amp; Time</div>
                                        <div class="text-sm font-medium text-gray-800 leading-snug">{{ selectedBooking.dateStr }} • {{ selectedBooking.timeStr }}</div>
                                    </div>
                                </div>
                                <!-- Service Type -->
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-0.5">Service Type</div>
                                        <div class="text-sm font-medium text-gray-800">{{ selectedBooking.serviceDetail }}</div>
                                    </div>
                                </div>
                                <!-- Duration -->
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-0.5">Duration</div>
                                        <div class="text-sm font-medium text-gray-800">{{ selectedBooking.duration }} Minutes</div>
                                    </div>
                                </div>
                                <!-- Price -->
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-0.5">Price</div>
                                        <div class="text-sm font-bold text-blue-600">{{ selectedBooking.price }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div v-if="selectedBooking.notes" class="px-5 py-4 border-b border-gray-100">
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Customer Notes</div>
                                <div class="bg-gray-50 rounded-xl p-3 text-sm text-gray-600 leading-relaxed">
                                    {{ selectedBooking.notes }}
                                </div>
                            </div>

                            <!-- Action buttons -->
                            <div class="px-5 py-4 space-y-2 border-b border-gray-100">
                                <Link
                                    :href="route('vendor.bookings.show', selectedBooking.id)"
                                    class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View Booking
                                </Link>
                                <a
                                    v-if="selectedBooking.customerEmail"
                                    :href="'mailto:' + selectedBooking.customerEmail"
                                    class="w-full flex items-center justify-center gap-2 border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-sm py-2.5 rounded-xl transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Send Email
                                </a>
                                <a
                                    v-if="selectedBooking.customerPhone"
                                    :href="'tel:' + selectedBooking.customerPhone"
                                    class="w-full flex items-center justify-center gap-2 border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-sm py-2.5 rounded-xl transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    Call Customer
                                </a>
                            </div>

                            <!-- Status actions -->
                            <div class="px-5 py-4">
                                <!-- Pending: Approve + Decline -->
                                <template v-if="selectedBooking.status === 'pending'">
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs font-bold text-amber-600">Waiting for your approval</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <Link 
                                            :href="route('vendor.bookings.cancel', selectedBooking.id)" 
                                            method="post"
                                            as="button"
                                            class="flex-1 border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-sm py-2.5 rounded-xl transition-colors"
                                        >
                                            Decline
                                        </Link>
                                        <Link 
                                            :href="route('vendor.bookings.confirm', selectedBooking.id)" 
                                            method="post"
                                            as="button"
                                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors"
                                        >
                                            Approve
                                        </Link>
                                    </div>
                                </template>

                                <!-- Confirmed: Complete + Cancel -->
                                <template v-else-if="selectedBooking.status === 'confirmed'">
                                    <div class="flex gap-2">
                                        <Link 
                                            :href="route('vendor.bookings.cancel', selectedBooking.id)" 
                                            method="post"
                                            as="button"
                                            class="flex-1 border border-red-200 hover:bg-red-50 text-red-600 font-semibold text-sm py-2.5 rounded-xl transition-colors"
                                        >
                                            Cancel
                                        </Link>
                                        <Link 
                                            :href="route('vendor.bookings.complete', selectedBooking.id)" 
                                            method="post"
                                            as="button"
                                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors"
                                        >
                                            Mark Completed
                                        </Link>
                                    </div>
                                </template>

                                <!-- Completed -->
                                <template v-else-if="selectedBooking.status === 'completed'">
                                    <div class="flex items-center gap-2 text-green-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-xs font-bold">Completed</span>
                                    </div>
                                </template>

                                <!-- Cancelled -->
                                <template v-else-if="selectedBooking.status === 'cancelled'">
                                    <div class="flex items-center gap-2 text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-xs font-bold">Cancelled</span>
                                    </div>
                                </template>
                            </div>

                        </div>
                    </div>
                </transition>

            </div>
        </div>
    </VendorLayout>
</template>
