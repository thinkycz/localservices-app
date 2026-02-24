<script setup>
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const currentView = ref('week');

const weekDays = [
    { day: 'MON', date: 23, dayIndex: 0, isToday: false },
    { day: 'TUE', date: 24, dayIndex: 1, isToday: false },
    { day: 'WED', date: 25, dayIndex: 2, isToday: true },
    { day: 'THU', date: 26, dayIndex: 3, isToday: false },
    { day: 'FRI', date: 27, dayIndex: 4, isToday: false },
    { day: 'SAT', date: 28, dayIndex: 5, isToday: false },
    { day: 'SUN', date: 29, dayIndex: 6, isToday: false },
];

const startHour = 8;
const endHour = 18;
const hourHeight = 80;

const timeSlots = computed(() => {
    const slots = [];
    for (let h = startHour; h <= endHour; h++) {
        const period = h < 12 ? 'AM' : 'PM';
        const display = h > 12 ? h - 12 : h === 0 ? 12 : h;
        slots.push(`${String(display).padStart(2, '0')}:00 ${period}`);
    }
    return slots;
});

const gridHeight = computed(() => (endHour - startHour) * hourHeight);

const bookings = ref([
    {
        id: 1,
        customer: 'John Cooper',
        service: 'Deep Tissue Massage',
        serviceDetail: 'Deep Tissue Massage',
        dayIndex: 0,
        startHour: 9,
        startMin: 0,
        duration: 60,
        colorType: 'blue',
        status: 'confirmed',
        initials: 'JC',
        avatarBg: 'bg-blue-200',
        avatarText: 'text-blue-700',
        dateStr: 'Mon, Oct 23',
        timeStr: '09:00 AM - 10:00 AM',
        price: '$75.00',
        customerType: 'Regular Customer',
        notes: '',
    },
    {
        id: 2,
        customer: 'Sarah Miller',
        service: 'Facial Therapy',
        serviceDetail: 'Facial Therapy (Deep Cleanse)',
        dayIndex: 2,
        startHour: 9,
        startMin: 30,
        duration: 60,
        colorType: 'yellow',
        status: 'pending',
        initials: 'SM',
        avatarBg: 'bg-gray-200',
        avatarText: 'text-gray-600',
        dateStr: 'Wed, Oct 25',
        timeStr: '09:30 AM - 10:30 AM',
        price: '$85.00',
        customerType: 'Regular Customer',
        notes: '"Please use unscented oil if possible. First-time visit for this service."',
    },
    {
        id: 3,
        customer: 'Robert Dow',
        service: 'Consultation',
        serviceDetail: 'Consultation',
        dayIndex: 4,
        startHour: 9,
        startMin: 0,
        duration: 60,
        colorType: 'blue',
        status: 'confirmed',
        initials: 'RD',
        avatarBg: 'bg-purple-200',
        avatarText: 'text-purple-700',
        dateStr: 'Fri, Oct 27',
        timeStr: '09:00 AM - 10:00 AM',
        price: '$50.00',
        customerType: 'New Customer',
        notes: '',
    },
    {
        id: 4,
        customer: 'Blocked: Maintenance',
        service: '',
        serviceDetail: '',
        dayIndex: 1,
        startHour: 10,
        startMin: 0,
        duration: 90,
        colorType: 'blocked',
        status: 'blocked',
        initials: '',
        avatarBg: '',
        avatarText: '',
        dateStr: '',
        timeStr: '',
        price: '',
        customerType: '',
        notes: '',
    },
    {
        id: 5,
        customer: 'Alex Reed',
        service: 'Spa Session',
        serviceDetail: 'Spa Session (Full Body)',
        dayIndex: 4,
        startHour: 10,
        startMin: 0,
        duration: 60,
        colorType: 'green',
        status: 'confirmed',
        initials: 'AR',
        avatarBg: 'bg-green-200',
        avatarText: 'text-green-700',
        dateStr: 'Fri, Oct 27',
        timeStr: '10:00 AM - 11:00 AM',
        price: '$95.00',
        customerType: 'Regular Customer',
        notes: '',
    },
    {
        id: 6,
        customer: 'Lisa Wang',
        service: 'Hot Stone',
        serviceDetail: 'Hot Stone Massage',
        dayIndex: 2,
        startHour: 11,
        startMin: 0,
        duration: 60,
        colorType: 'blue',
        status: 'confirmed',
        initials: 'LW',
        avatarBg: 'bg-pink-200',
        avatarText: 'text-pink-700',
        dateStr: 'Wed, Oct 25',
        timeStr: '11:00 AM - 12:00 PM',
        price: '$90.00',
        customerType: 'Regular Customer',
        notes: '',
    },
    {
        id: 7,
        customer: 'Marcus T.',
        service: 'Reiki',
        serviceDetail: 'Reiki Session',
        dayIndex: 4,
        startHour: 11,
        startMin: 0,
        duration: 60,
        colorType: 'blue',
        status: 'confirmed',
        initials: 'MT',
        avatarBg: 'bg-orange-200',
        avatarText: 'text-orange-700',
        dateStr: 'Fri, Oct 27',
        timeStr: '11:00 AM - 12:00 PM',
        price: '$70.00',
        customerType: 'New Customer',
        notes: '',
    },
]);

const selectedBooking = ref(bookings.value.find((b) => b.id === 2));

function selectBooking(booking) {
    if (booking.colorType === 'blocked') return;
    selectedBooking.value = booking;
}

function closeDetails() {
    selectedBooking.value = null;
}

function getBookingsForDay(dayIndex) {
    return bookings.value.filter((b) => b.dayIndex === dayIndex);
}

function getBookingTop(booking) {
    return (booking.startHour - startHour + booking.startMin / 60) * hourHeight;
}

function getBookingHeight(booking) {
    return (booking.duration / 60) * hourHeight;
}

const currentTimeTop = computed(() => {
    return (11 - startHour + 15 / 60) * hourHeight;
});

const lunchBreakTop = computed(() => (12 - startHour) * hourHeight);

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
    blocked: {
        wrapper: 'border-l-4 border-gray-300',
        name: 'text-gray-500 font-medium',
        service: '',
    },
};

function getCardStyle(colorType) {
    return cardStyles[colorType] || cardStyles.blue;
}
</script>

<template>
    <Head title="Calendar" />

    <VendorLayout activePage="calendar">
        <div class="flex flex-col h-full gap-4">

            <!-- Toolbar -->
            <div class="flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                        <button class="px-3 py-2 text-gray-500 hover:bg-gray-50 transition-colors border-r border-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button class="px-3 py-2 text-gray-500 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">Oct 23 – Oct 29, 2023</h2>
                </div>

                <div class="flex items-center bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <button
                        class="px-4 py-2 text-sm font-semibold transition-colors"
                        :class="currentView === 'today' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:bg-gray-50'"
                        @click="currentView = 'today'"
                    >TODAY</button>
                    <button
                        class="px-4 py-2 text-sm font-semibold border-l border-gray-200 transition-colors"
                        :class="currentView === 'day' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:bg-gray-50'"
                        @click="currentView = 'day'"
                    >Day</button>
                    <button
                        class="px-4 py-2 text-sm font-semibold border-l border-gray-200 transition-colors"
                        :class="currentView === 'week' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:bg-gray-50'"
                        @click="currentView = 'week'"
                    >Week</button>
                    <button
                        class="px-4 py-2 text-sm font-semibold border-l border-gray-200 transition-colors"
                        :class="currentView === 'month' ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:bg-gray-50'"
                        @click="currentView = 'month'"
                    >Month</button>
                </div>
            </div>

            <!-- Calendar + Details Panel -->
            <div class="flex flex-1 gap-4 overflow-hidden min-h-0">

                <!-- Calendar Grid -->
                <div class="flex-1 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col min-w-0">

                    <!-- Day Header Row -->
                    <div class="flex border-b border-gray-100 flex-shrink-0">
                        <div class="w-20 flex-shrink-0 border-r border-gray-100"></div>
                        <div
                            v-for="day in weekDays"
                            :key="day.dayIndex"
                            class="flex-1 text-center py-3 border-r border-gray-100 last:border-r-0"
                            :class="day.isToday ? 'bg-blue-50' : ''"
                        >
                            <div class="text-xs font-semibold text-gray-400 tracking-widest uppercase">{{ day.day }}</div>
                            <div
                                class="mt-1 text-xl font-bold w-9 h-9 rounded-full mx-auto flex items-center justify-center"
                                :class="day.isToday ? 'bg-blue-600 text-white' : 'text-gray-800'"
                            >{{ day.date }}</div>
                        </div>
                    </div>

                    <!-- Scrollable Time Grid -->
                    <div class="flex-1 overflow-y-auto">
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
                                    class="flex-1 relative border-r border-gray-100 last:border-r-0"
                                    :class="day.isToday ? 'bg-blue-50/20' : ''"
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
                                    <template v-for="booking in getBookingsForDay(day.dayIndex)" :key="booking.id">
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
                                        v-if="day.isToday"
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
                                class="absolute left-1 pointer-events-none z-20"
                                :style="{ top: (currentTimeTop - 8) + 'px' }"
                            >
                                <span class="text-xs font-bold text-blue-600">11:15</span>
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
                                <button class="w-full flex items-center justify-center gap-2 border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-sm py-2.5 rounded-xl transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Send Email
                                </button>
                                <button class="w-full flex items-center justify-center gap-2 border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-sm py-2.5 rounded-xl transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    Call Customer
                                </button>
                            </div>

                            <!-- Approval section (pending only) -->
                            <div v-if="selectedBooking.status === 'pending'" class="px-5 py-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs font-bold text-amber-600">Waiting for your approval</span>
                                </div>
                                <div class="flex gap-2">
                                    <button class="flex-1 border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-sm py-2.5 rounded-xl transition-colors">
                                        Decline
                                    </button>
                                    <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors">
                                        Approve
                                    </button>
                                </div>
                                <button class="w-full text-center text-xs text-gray-400 hover:text-gray-600 mt-3 transition-colors">
                                    Reschedule booking
                                </button>
                            </div>

                        </div>
                    </div>
                </transition>

            </div>
        </div>
    </VendorLayout>
</template>

