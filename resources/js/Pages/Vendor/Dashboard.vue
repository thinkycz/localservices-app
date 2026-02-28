<script setup>
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: { type: Array, default: () => [] },
    todayBookings: { type: Array, default: () => [] },
    weekStats: { type: Object, default: () => ({}) },
    servicePopularity: { type: Array, default: () => [] },
    monthlyRevenue: { type: Array, default: () => [] },
    recentBookings: { type: Array, default: () => [] },
    overview: { type: Object, default: () => ({}) },
});

const appointments = computed(() => props.todayBookings);

const barColors = [
    'bg-blue-500',
    'bg-emerald-500',
    'bg-violet-500',
    'bg-amber-500',
    'bg-rose-500',
];

const services = computed(() => props.servicePopularity.map((svc, i) => ({
    name: svc.service,
    percent: svc.percentage,
    barColor: barColors[i % barColors.length],
})));

const today = new Date();

function toISODateLocal(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

const todayIso = computed(() => toISODateLocal(today));

const weekStartIso = computed(() => {
    const d = new Date(today);
    const day = d.getDay();
    const diff = day === 0 ? -6 : 1 - day;
    d.setDate(d.getDate() + diff);
    return toISODateLocal(d);
});

const weekEndIso = computed(() => {
    const d = new Date(today);
    const day = d.getDay();
    const diff = day === 0 ? -6 : 1 - day;
    d.setDate(d.getDate() + diff + 6);
    return toISODateLocal(d);
});

function formatScheduleDate(date) {
    return new Intl.DateTimeFormat('en-US', {
        weekday: 'long',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(date);
}

const todayScheduleSubtitle = computed(() => formatScheduleDate(today));

function getAvatarColors(name) {
    const colors = [
        { bg: 'bg-pink-100', text: 'text-pink-600' },
        { bg: 'bg-blue-100', text: 'text-blue-600' },
        { bg: 'bg-emerald-100', text: 'text-emerald-600' },
        { bg: 'bg-violet-100', text: 'text-violet-600' },
        { bg: 'bg-amber-100', text: 'text-amber-600' },
        { bg: 'bg-teal-100', text: 'text-teal-600' },
    ];
    const index = name.charCodeAt(0) % colors.length;
    return colors[index];
}

function getStatusClasses(status) {
    const s = status.toUpperCase();
    const statusMap = {
        'PENDING': { bg: 'bg-amber-50', text: 'text-amber-600', border: 'border-amber-400', dot: 'bg-amber-400' },
        'CONFIRMED': { bg: 'bg-blue-50', text: 'text-blue-600', border: 'border-blue-500', dot: 'bg-blue-500' },
        'COMPLETED': { bg: 'bg-emerald-50', text: 'text-emerald-600', border: 'border-emerald-400', dot: 'bg-emerald-400' },
        'CANCELLED': { bg: 'bg-red-50', text: 'text-red-500', border: 'border-red-400', dot: 'bg-red-400' },
    };
    return statusMap[s] || statusMap['PENDING'];
}

function formatPrice(price) {
    return '$' + Number(price).toFixed(2);
}

function formatDate(dateStr) {
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
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
                class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200"
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
        <div class="grid grid-cols-3 gap-4 mb-6">
            <!-- Today's Schedule (spans 2 cols) -->
            <div class="col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-50">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Today's Schedule</h2>
                        <p class="text-sm text-gray-400 mt-0.5">{{ todayScheduleSubtitle }}</p>
                    </div>
                    <Link
                        :href="route('vendor.calendar', { start_date: weekStartIso, end_date: weekEndIso, view: 'week' })"
                        class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors"
                    >
                        View Week
                    </Link>
                </div>

                <!-- Appointment List -->
                <div class="flex-1 px-6 py-4 space-y-3">
                    <!-- Empty state -->
                    <div v-if="appointments.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-500 mb-1">No bookings today</h3>
                        <p class="text-xs text-gray-400">Your schedule is clear for today. Enjoy your free time!</p>
                    </div>

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
                        <Link
                            :href="route('vendor.bookings.show', appt.id)"
                            :class="[
                                'flex-1 rounded-xl border-l-4 bg-gray-50 hover:bg-gray-100 px-4 py-3 transition-colors cursor-pointer',
                                getStatusClasses(appt.status).border,
                                appt.completed ? 'opacity-70' : '',
                            ]"
                        >
                            <div class="flex items-center justify-between">
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
                            <div class="flex items-center mt-2">
                                <div class="flex items-center gap-2">
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
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Footer link -->
                <div v-if="appointments.length > 0" class="px-6 py-4 border-t border-gray-50 text-center">
                    <Link
                        :href="route('vendor.bookings.index', { date_from: todayIso, date_to: todayIso, sort: 'date_asc' })"
                        class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                    >
                        See All {{ todayBookings.length }} Bookings Today
                    </Link>
                </div>
            </div>

            <!-- ── Right Panel ──────────────────────────────────────── -->
            <div class="col-span-1 flex flex-col gap-4">


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
            </div>
        </div>

        <!-- ── Recent Bookings ──────────────────────────────────────── -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-50">
                <h2 class="text-lg font-bold text-gray-900">Recent Bookings</h2>
                <Link
                    :href="route('vendor.bookings.index')"
                    class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                >
                    View All
                </Link>
            </div>

            <div v-if="recentBookings.length === 0" class="px-6 py-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-500 mb-1">No bookings yet</h3>
                <p class="text-xs text-gray-400">Bookings will appear here once customers start booking your services.</p>
            </div>

            <table v-else class="w-full">
                <thead>
                    <tr class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Service</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Time</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr
                        v-for="booking in recentBookings"
                        :key="booking.id"
                        class="hover:bg-gray-50 transition-colors cursor-pointer"
                        @click="$inertia.visit(route('vendor.bookings.show', booking.id))"
                    >
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0',
                                        getAvatarColors(booking.customer_name).bg,
                                        getAvatarColors(booking.customer_name).text,
                                    ]"
                                >
                                    {{ booking.customer_name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() }}
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ booking.customer_name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="text-sm text-gray-600">{{ booking.offering_name }}</span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="text-sm text-gray-600">{{ formatDate(booking.date) }}</span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="text-sm text-gray-600">{{ booking.time }}</span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span
                                :class="[
                                    'inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full',
                                    getStatusClasses(booking.status).bg,
                                    getStatusClasses(booking.status).text,
                                ]"
                            >
                                <span :class="['w-1.5 h-1.5 rounded-full', getStatusClasses(booking.status).dot]"></span>
                                {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <span class="text-sm font-semibold text-gray-900">{{ formatPrice(booking.price) }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </VendorLayout>
</template>
