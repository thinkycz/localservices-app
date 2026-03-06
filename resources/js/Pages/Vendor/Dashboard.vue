<script setup>
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: { type: Array, default: () => [] },
    todayBookings: { type: Array, default: () => [] },
    weekStats: { type: Object, default: () => ({}) },

    monthlyRevenue: { type: Array, default: () => [] },
    recentBookings: { type: Array, default: () => [] },
    overview: { type: Object, default: () => ({}) },
});

const appointments = computed(() => props.todayBookings);



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



function formatDate(dateStr) {
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}
</script>

<template>
    <Head :title="$t('Dashboard')" />

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
                    <!-- Calendar Check (Total Bookings) -->
                    <div v-if="stat.icon === 'calendar-check'" class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4"/>
                        </svg>
                    </div>
                    <!-- Calendar X (Cancellations) -->
                    <div v-else-if="stat.icon === 'calendar-x'" class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l4 4m0-4l-4 4"/>
                        </svg>
                    </div>
                    <!-- User Plus (New Customers) -->
                    <div v-else-if="stat.icon === 'user-plus'" class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <!-- Cash (Revenue) -->
                    <div v-else-if="stat.icon === 'cash'" class="w-11 h-11 rounded-xl bg-purple-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
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

        <!-- ── Main Grid: Recent Bookings (left, bigger) + Today's Schedule (right) ── -->
        <div class="grid grid-cols-3 gap-4">

            <!-- ── Recent Bookings (spans 2 cols) ──────────────────────── -->
            <div class="col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100">
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
                    <p class="text-xs text-gray-400">Bookings will appear here once customers start booking your shops.</p>
                </div>

                <table v-else class="w-full">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-3">Customer</th>
                            <th class="px-6 py-3">Service</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Time</th>
                            <th class="px-6 py-3">Status</th>
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
                                <span class="text-sm text-gray-600">{{ booking.service_name }}</span>
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
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ── Today's Schedule (1 col, right) ─────────────────────── -->
            <div class="col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-gray-50">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Today's Schedule</h2>
                        <p class="text-xs text-gray-400 mt-0.5">{{ todayScheduleSubtitle }}</p>
                    </div>
                    <Link
                        :href="route('vendor.calendar', { start_date: weekStartIso, end_date: weekEndIso, view: 'week' })"
                        class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                    >
                        View Week
                    </Link>
                </div>

                <!-- Appointment List -->
                <div class="flex-1 px-4 py-3 space-y-2 overflow-y-auto max-h-[400px]">
                    <!-- Empty state -->
                    <div v-if="appointments.length === 0" class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-500 mb-1">No bookings today</h3>
                        <p class="text-xs text-gray-400">Your schedule is clear!</p>
                    </div>

                    <Link
                        v-for="appt in appointments"
                        :key="appt.id"
                        :href="route('vendor.bookings.show', appt.id)"
                        :class="[
                            'block rounded-xl border-l-4 bg-gray-50 hover:bg-gray-100 px-3 py-2.5 transition-colors',
                            getStatusClasses(appt.status).border,
                            appt.completed ? 'opacity-70' : '',
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                :class="[
                                    'font-semibold text-sm',
                                    appt.completed ? 'line-through text-gray-400' : 'text-gray-900',
                                ]"
                            >
                                {{ appt.title }}
                            </span>
                            <span
                                :class="[
                                    'text-[10px] font-bold px-2 py-0.5 rounded-full',
                                    getStatusClasses(appt.status).bg,
                                    getStatusClasses(appt.status).text,
                                ]"
                            >
                                {{ appt.status }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 mt-1.5 text-xs text-gray-500">
                            <span class="font-medium">{{ appt.time }}</span>
                            <span>·</span>
                            <span>{{ appt.duration }}</span>
                            <span>·</span>
                            <div class="flex items-center gap-1">
                                <div
                                    :class="[
                                        'w-4 h-4 rounded-full flex items-center justify-center text-[8px] font-semibold',
                                        getAvatarColors(appt.customer).bg,
                                        getAvatarColors(appt.customer).text,
                                    ]"
                                >
                                    {{ appt.customer_initials }}
                                </div>
                                <span>{{ appt.customer }}</span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Footer link -->
                <div v-if="appointments.length > 0" class="px-5 py-3 border-t border-gray-50 text-center">
                    <Link
                        :href="route('vendor.bookings.index', { date_from: todayIso, date_to: todayIso, sort: 'date_asc' })"
                        class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                    >
                        See All {{ todayBookings.length }} Bookings Today
                    </Link>
                </div>
            </div>
        </div>
    </VendorLayout>
</template>
