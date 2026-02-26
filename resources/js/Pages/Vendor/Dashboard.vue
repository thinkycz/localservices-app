<script setup>
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: { type: Array, default: () => [] },
    todayBookings: { type: Array, default: () => [] },
    weekStats: { type: Object, default: () => ({}) },
    servicePopularity: { type: Array, default: () => [] },
    monthlyRevenue: { type: Array, default: () => [] },
    recentBookings: { type: Array, default: () => [] },
    overview: { type: Object, default: () => ({}) },
});

// Alias for template compatibility
const appointments = computed(() => props.todayBookings);
const services = computed(() => props.servicePopularity.map(svc => ({
    name: svc.service,
    percent: svc.percentage,
    barColor: 'bg-blue-500',
})));

// Mini Calendar
const today = new Date();
const calendarMonth = ref(today.getMonth());
const calendarYear = ref(today.getFullYear());

const monthNames = [
    'January','February','March','April','May','June',
    'July','August','September','October','November','December',
];

const dayHeaders = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

// Build calendar grid
const calendarDays = computed(() => {
    const year = calendarYear.value;
    const month = calendarMonth.value;
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrev = new Date(year, month, 0).getDate();

    const cells = [];
    for (let i = firstDay - 1; i >= 0; i--) {
        cells.push({ day: daysInPrev - i, current: false });
    }
    for (let d = 1; d <= daysInMonth; d++) {
        cells.push({ day: d, current: true });
    }
    const remaining = 42 - cells.length;
    for (let d = 1; d <= remaining; d++) {
        cells.push({ day: d, current: false });
    }
    return cells;
});

const isToday = (cell) =>
    cell.current &&
    cell.day === today.getDate() &&
    calendarMonth.value === today.getMonth() &&
    calendarYear.value === today.getFullYear();

function prevMonth() {
    if (calendarMonth.value === 0) {
        calendarMonth.value = 11;
        calendarYear.value--;
    } else {
        calendarMonth.value--;
    }
}
function nextMonth() {
    if (calendarMonth.value === 11) {
        calendarMonth.value = 0;
        calendarYear.value++;
    } else {
        calendarMonth.value++;
    }
}

// Format today's date
const todayFormatted = computed(() => {
    return today.toLocaleDateString('en-US', { 
        weekday: 'long', 
        month: 'long', 
        day: 'numeric' 
    });
});

// Get today's schedule subtitle
const todayScheduleSubtitle = computed(() => {
    return today.toLocaleDateString('en-US', { 
        weekday: 'long', 
        month: 'long', 
        day: 'numeric' 
    });
});

// Get avatar background color based on name
function getAvatarColors(name) {
    const colors = [
        { bg: 'bg-pink-200', text: 'text-pink-700' },
        { bg: 'bg-blue-200', text: 'text-blue-700' },
        { bg: 'bg-green-200', text: 'text-green-700' },
        { bg: 'bg-purple-200', text: 'text-purple-700' },
        { bg: 'bg-orange-200', text: 'text-orange-700' },
        { bg: 'bg-teal-200', text: 'text-teal-700' },
    ];
    const index = name.charCodeAt(0) % colors.length;
    return colors[index];
}

function getStatusClasses(status) {
    const statusMap = {
        'PENDING': { bg: 'bg-orange-50', text: 'text-orange-500', border: 'border-orange-400' },
        'CONFIRMED': { bg: 'bg-green-50', text: 'text-green-600', border: 'border-blue-500' },
        'COMPLETED': { bg: 'bg-gray-100', text: 'text-gray-400', border: 'border-gray-300' },
        'CANCELLED': { bg: 'bg-red-50', text: 'text-red-500', border: 'border-red-400' },
    };
    return statusMap[status] || statusMap['PENDING'];
}
</script>

<template>
    <Head title="Dashboard" />

    <VendorLayout activePage="dashboard">
        <!-- ── Stats Row ──────────────────────────────────────────────── -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100"
            >
                <div class="flex items-start justify-between mb-4">
                    <!-- Icon -->
                    <div
                        :class="[stat.iconBg, 'w-11 h-11 rounded-xl flex items-center justify-center']"
                    >
                        <!-- Calendar Check (Total Bookings) -->
                        <template v-if="stat.icon === 'calendar-check'">
                            <svg :class="[stat.iconColor, 'w-5 h-5']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </template>
                        <!-- Calendar X (Cancellations) -->
                        <template v-else-if="stat.icon === 'calendar-x'">
                            <svg :class="[stat.iconColor, 'w-5 h-5']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l-3 3m0-3l3 3"/>
                            </svg>
                        </template>
                        <!-- User Plus (New Customers) -->
                        <template v-else-if="stat.icon === 'user-plus'">
                            <svg :class="[stat.iconColor, 'w-5 h-5']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </template>
                        <!-- Cash (Revenue) -->
                        <template v-else-if="stat.icon === 'cash'">
                            <svg :class="[stat.iconColor, 'w-5 h-5']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </template>
                    </div>

                    <!-- Change badge -->
                    <span
                        :class="[
                            'text-xs font-semibold px-2 py-0.5 rounded-full',
                            stat.positive
                                ? 'text-green-600 bg-green-50'
                                : 'text-red-500 bg-red-50',
                        ]"
                    >
                        {{ stat.change }}
                    </span>
                </div>

                <div class="text-sm text-gray-500 mb-1">{{ stat.label }}</div>
                <div class="text-2xl font-bold text-gray-900">{{ stat.value }}</div>
            </div>
        </div>

        <!-- ── Main Grid: Schedule + Right Panel ──────────────────────── -->
        <div class="grid grid-cols-3 gap-4">
            <!-- Today's Schedule (spans 2 cols) -->
            <div class="col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-50">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Today's Schedule</h2>
                    <p class="text-sm text-gray-400 mt-0.5">{{ todayScheduleSubtitle }}</p>
                </div>

                    <div class="flex items-center gap-3">
                        <!-- Filter icon -->
                        <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                            </svg>
                        </button>
                        <!-- View Week button -->
                        <button class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                            View Week
                        </button>
                    </div>
                </div>

                <!-- Appointment List -->
                <div class="flex-1 px-6 py-4 space-y-4">
                    <div
                        v-for="appt in appointments"
                        :key="appt.id"
                        class="flex gap-4 items-stretch"
                    >
                        <!-- Time column -->
                        <div class="w-20 flex-shrink-0 text-right pt-3">
                            <div
                                :class="[
                                    'text-sm font-semibold',
                                    appt.completed ? 'text-gray-400' : 'text-gray-700',
                                ]"
                            >
                                {{ appt.time }}
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ appt.duration }}</div>
                        </div>

                        <!-- Card -->
                        <div
                            :class="[
                                'flex-1 rounded-xl border-l-4 bg-gray-50 px-4 py-3',
                                getStatusClasses(appt.status).border,
                                appt.completed ? 'opacity-70' : '',
                            ]"
                        >
                            <div class="flex items-start justify-between">
                                <!-- Title + Status -->
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span
                                        :class="[
                                            'font-semibold text-sm',
                                            appt.completed
                                                ? 'line-through text-gray-400'
                                                : 'text-gray-900',
                                        ]"
                                    >
                                        {{ appt.title }}
                                    </span>
                                    <span
                                        :class="[
                                            'text-xs font-bold px-2.5 py-0.5 rounded-full tracking-wide',
                                            getStatusClasses(appt.status).bg,
                                            getStatusClasses(appt.status).text,
                                        ]"
                                    >
                                        {{ appt.status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Customer row -->
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center gap-2">
                                    <!-- Avatar -->
                                    <div
                                        :class="[
                                            'w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0',
                                            getAvatarColors(appt.customer).bg,
                                            getAvatarColors(appt.customer).text,
                                        ]"
                                    >
                                        {{ appt.customer_initials }}
                                    </div>
                                    <span
                                        :class="[
                                            'text-sm',
                                            appt.completed ? 'text-gray-400' : 'text-gray-600',
                                        ]"
                                    >
                                        {{ appt.customer }}
                                    </span>
                                </div>

                                <!-- Action icons -->
                                <div class="flex items-center gap-2">
                                    <button
                                        :class="[
                                            'p-1.5 rounded-lg transition-colors',
                                            appt.completed
                                                ? 'text-gray-300'
                                                : 'text-blue-400 hover:bg-blue-50',
                                        ]"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                    </button>
                                    <button
                                        :class="[
                                            'p-1.5 rounded-lg transition-colors',
                                            appt.completed
                                                ? 'text-gray-300'
                                                : 'text-gray-400 hover:bg-gray-100',
                                        ]"
                                    >
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="5" r="1.5"/>
                                            <circle cx="12" cy="12" r="1.5"/>
                                            <circle cx="12" cy="19" r="1.5"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer link -->
                <div class="px-6 py-4 border-t border-gray-50 text-center">
                    <a :href="route('vendor.calendar')" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                        See All {{ todayBookings.length }} Bookings Today
                    </a>
                </div>
            </div>

            <!-- ── Right Panel ──────────────────────────────────────── -->
            <div class="col-span-1 flex flex-col gap-4">

                <!-- Mini Calendar -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <!-- Month header -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-900 text-sm">
                            {{ monthNames[calendarMonth] }} {{ calendarYear }}
                        </h3>
                        <div class="flex items-center gap-1">
                            <button
                                @click="prevMonth"
                                class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button
                                @click="nextMonth"
                                class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Day headers -->
                    <div class="grid grid-cols-7 mb-2">
                        <div
                            v-for="d in dayHeaders"
                            :key="d + Math.random()"
                            class="text-center text-xs font-medium text-gray-400 py-1"
                        >
                            {{ d }}
                        </div>
                    </div>

                    <!-- Day cells -->
                    <div class="grid grid-cols-7 gap-y-1">
                        <div
                            v-for="(cell, idx) in calendarDays"
                            :key="idx"
                            class="flex items-center justify-center"
                        >
                            <span
                                :class="[
                                    'w-7 h-7 flex items-center justify-center rounded-full text-xs font-medium cursor-pointer transition-colors',
                                    isToday(cell)
                                        ? 'bg-blue-600 text-white font-bold'
                                        : cell.current
                                        ? 'text-gray-700 hover:bg-gray-100'
                                        : 'text-gray-300',
                                ]"
                            >
                                {{ cell.day }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Service Popularity -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-900 text-sm mb-4">Service Popularity</h3>
                    <div class="space-y-4">
                        <div v-for="svc in services" :key="svc.name">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-bold text-gray-500 tracking-wide">
                                    {{ svc.name }}
                                </span>
                                <span class="text-xs font-bold text-gray-700">
                                    {{ svc.percent }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div
                                    :class="[svc.barColor, 'h-2 rounded-full transition-all duration-500']"
                                    :style="{ width: svc.percent + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <div v-if="services.length === 0" class="text-center py-4 text-gray-400 text-sm">
                        No booking data available yet
                    </div>
                </div>

                <!-- Business Tip -->
                <div class="bg-blue-600 rounded-2xl p-5 text-white">
                    <div class="flex items-start gap-3 mb-3">
                        <!-- Lightbulb icon -->
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-base leading-tight">Business Tip</h3>
                    </div>
                    <p class="text-sm text-blue-100 leading-relaxed mb-4">
                        {{ overview.returning_customers > 0 
                            ? `You have ${overview.returning_customers} returning customers. Offer them a loyalty discount to increase retention.` 
                            : 'Build customer loyalty by offering discounts to returning customers and asking for reviews.' }}
                    </p>
                    <button class="w-full bg-blue-500 hover:bg-blue-400 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Action Tips
                    </button>
                </div>
            </div>
        </div>
    </VendorLayout>
</template>
