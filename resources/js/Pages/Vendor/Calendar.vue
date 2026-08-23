<script setup>
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, router, Link, useRemember } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import {
    CalendarDays,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Clock3,
    Eye,
    Mail,
    Phone,
    Tag,
    X,
} from '@lucide/vue';
import { layoutBookingsForDay } from '@/lib/calendarLayout';

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

// Status dropdown functionality
const showStatusDropdown = ref(false);

function getStatusConfig(status) {
    const config = {
        pending: { label: 'Čeká na potvrzení', bg: 'bg-amber-50', text: 'text-amber-800', ring: 'ring-amber-600/20', dot: 'bg-amber-500' },
        confirmed: { label: 'Potvrzeno', bg: 'bg-brand-50', text: 'text-brand-800', ring: 'ring-brand-700/20', dot: 'bg-brand-600' },
        completed: { label: 'Dokončeno', bg: 'bg-green-50', text: 'text-green-800', ring: 'ring-green-600/20', dot: 'bg-success' },
        cancelled: { label: 'Zrušeno', bg: 'bg-red-50', text: 'text-red-800', ring: 'ring-red-600/20', dot: 'bg-danger' },
    };
    return config[status] || config.pending;
}

function availableStatusActions(booking) {
    if (booking.status === 'pending') return ['confirmed'];
    if (booking.status !== 'confirmed') return [];

    const startsAt = new Date(
        Number(booking.fullDate.slice(0, 4)),
        Number(booking.fullDate.slice(5, 7)) - 1,
        Number(booking.fullDate.slice(8, 10)),
        booking.startHour,
        booking.startMin,
    );

    return startsAt <= new Date() ? ['completed'] : [];
}

function updateStatus(newStatus) {
    if (!selectedBooking.value) return;

    router.post(route('vendor.bookings.update', selectedBooking.value.id), {
        status: newStatus,
    }, {
        onSuccess: () => {
            showStatusDropdown.value = false;
        },
    });
}

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
        slots.push(`${String(h).padStart(2, '0')}:00`);
    }
    return slots;
});

const gridHeight = computed(() => (endHour.value - startHour.value) * hourHeight);

const bookings = computed(() => props.bookings || []);

const rememberedState = useRemember({
    selectedBookingId: null,
}, 'vendor-calendar:selected-booking');
const selectedBooking = computed(() => bookings.value.find((booking) => booking.id === rememberedState.value.selectedBookingId) || null);

function selectBooking(booking) {
    if (booking.colorType === 'blocked') return;
    rememberedState.value.selectedBookingId = booking.id;
}

function closeDetails() {
    rememberedState.value.selectedBookingId = null;
    showStatusDropdown.value = false;
}

watch(selectedBooking, (booking) => {
    if (!booking) {
        showStatusDropdown.value = false;
    }
});

function getBookingsForDay(day) {
    return layoutBookingsForDay(bookings.value, day.fullDate);
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

function getBookingHorizontalStyle(booking) {
    if (booking.colorType === 'blocked' || !booking.columnCount || booking.columnCount <= 1) {
        return {
            left: '4px',
            right: '4px',
            zIndex: 1,
        };
    }

    const gap = 6;
    const width = `calc((100% - ${(booking.columnCount + 1) * gap}px) / ${booking.columnCount})`;
    const left = `calc(${gap}px + ${booking.columnIndex} * (${width} + ${gap}px))`;

    return {
        left,
        width,
        zIndex: booking.columnIndex + 1,
    };
}

const currentTimeTop = computed(() => {
    const now = new Date();
    const currentHour = now.getHours();
    const currentMin = now.getMinutes();
    return (currentHour - startHour.value + currentMin / 60) * hourHeight;
});

const currentTimeLabel = computed(() => {
    const now = new Date();
    return now.toLocaleTimeString('cs-CZ', { hour: '2-digit', minute: '2-digit' });
});

const cardStyles = {
    blue: {
        wrapper: 'bg-brand-50 border-l-4 border-brand-600',
        name: 'text-brand-900 font-semibold',
        shop: 'text-brand-700',
    },
    yellow: {
        wrapper: 'bg-amber-50 border-l-4 border-amber-500',
        name: 'text-amber-900 font-semibold',
        shop: 'text-amber-700',
    },
    green: {
        wrapper: 'bg-green-50 border-l-4 border-green-500',
        name: 'text-green-800 font-semibold',
        shop: 'text-green-700',
    },
    red: {
        wrapper: 'bg-red-50 border-l-4 border-red-500',
        name: 'text-red-800 font-semibold',
        shop: 'text-red-700',
    },
    blocked: {
        wrapper: 'border-l-4 border-gray-300',
        name: 'text-gray-500 font-medium',
        shop: '',
    },
};

function getCardStyle(colorType) {
    return cardStyles[colorType] || cardStyles.blue;
}

const dayColumnClass = computed(() => (currentView.value === 'month' ? 'flex-none w-44' : 'flex-1'));
const calendarInnerClass = computed(() => (currentView.value === 'month' ? 'min-w-max' : 'min-w-full'));

onMounted(() => {
    const hasExplicitView = new URLSearchParams(window.location.search).has('view');
    if (window.innerWidth < 768 && currentView.value === 'week' && !hasExplicitView) {
        const today = toISODate(new Date());
        router.get(
            route('vendor.calendar'),
            { start_date: today, end_date: today, view: 'day' },
            { preserveState: true, replace: true },
        );
    }
});
</script>

<template>
    <Head :title="$t('Calendar')" />

    <VendorLayout activePage="calendar">
        <div class="flex min-h-[calc(100dvh-8rem)] flex-col gap-4">

            <!-- Toolbar -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex flex-none items-center overflow-hidden rounded-xl border border-line bg-white shadow-sm">
                        <button type="button" aria-label="Předchozí období" @click="navigate('prev')" class="flex min-h-11 min-w-11 items-center justify-center border-r border-line text-muted transition hover:bg-gray-50 hover:text-ink">
                            <ChevronLeft :size="18" aria-hidden="true" />
                        </button>
                        <button type="button" aria-label="Následující období" @click="navigate('next')" class="flex min-h-11 min-w-11 items-center justify-center text-muted transition hover:bg-gray-50 hover:text-ink">
                            <ChevronRight :size="18" aria-hidden="true" />
                        </button>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-brand-700">Kalendář</p>
                        <h1 class="truncate text-lg font-extrabold text-ink">{{ weekRange }}</h1>
                    </div>
                </div>

                <div class="flex max-w-full items-center overflow-x-auto rounded-xl border border-line bg-white p-1 shadow-sm">
                    <button
                        class="min-h-10 flex-none rounded-lg px-3 text-sm font-bold transition-colors"
                        :class="currentView === 'today' ? 'bg-brand-50 text-brand-800' : 'text-muted hover:bg-gray-50'"
                        @click="changeView('today')"
                    >Dnes</button>
                    <button
                        class="min-h-10 flex-none rounded-lg px-3 text-sm font-bold transition-colors"
                        :class="currentView === 'day' ? 'bg-brand-50 text-brand-800' : 'text-muted hover:bg-gray-50'"
                        @click="changeView('day')"
                    >Den</button>
                    <button
                        class="min-h-10 flex-none rounded-lg px-3 text-sm font-bold transition-colors"
                        :class="currentView === 'week' ? 'bg-brand-50 text-brand-800' : 'text-muted hover:bg-gray-50'"
                        @click="changeView('week')"
                    >Týden</button>
                    <button
                        class="min-h-10 flex-none rounded-lg px-3 text-sm font-bold transition-colors"
                        :class="currentView === 'month' ? 'bg-brand-50 text-brand-800' : 'text-muted hover:bg-gray-50'"
                        @click="changeView('month')"
                    >Měsíc</button>
                </div>
            </div>

            <!-- Calendar + Details Panel -->
            <div class="flex min-h-0 flex-1 gap-4 overflow-hidden">

                <!-- Calendar Grid -->
                <div class="flex min-h-[38rem] min-w-0 flex-1 flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-sm">

                    <div class="min-w-0 flex-1 overflow-x-auto" tabindex="0" aria-label="Vodorovné posouvání kalendáře">
                        <div :class="[calendarInnerClass, 'h-full flex flex-col']">

                            <!-- Day Header Row -->
                            <div class="flex border-b border-gray-100 flex-shrink-0">
                                <div class="w-20 flex-shrink-0 border-r border-gray-100"></div>
                                <div
                                    v-for="day in weekDays"
                                    :key="day.dayIndex"
                                    class="text-center py-3 border-r border-gray-100 last:border-r-0"
                                    :class="[dayColumnClass, day.isToday ? 'bg-brand-50' : '']"
                                >
                                    <div class="text-xs font-semibold uppercase tracking-widest" :class="day.isToday ? 'text-brand-800' : 'text-muted'">{{ day.day }}</div>
                                    <div
                                        class="mt-1 text-xl font-bold w-9 h-9 rounded-full mx-auto flex items-center justify-center"
                                        :class="day.isToday ? 'bg-brand-700 text-white' : 'text-ink'"
                                    >{{ day.date }}</div>
                                </div>
                            </div>

                            <!-- Scrollable Time Grid -->
                            <div class="min-h-0 flex-1 overflow-y-auto" tabindex="0" aria-label="Časová osa kalendáře">
                                <div class="flex relative" :style="{ height: gridHeight + 'px' }">

                                    <!-- Time Labels -->
                                    <div class="w-20 flex-shrink-0 border-r border-gray-100 relative">
                                        <div
                                            v-for="(slot, idx) in timeSlots"
                                            :key="idx"
                                            class="absolute right-3 whitespace-nowrap text-xs font-medium text-muted"
                                            :style="{ top: (idx * hourHeight - 8) + 'px' }"
                                        >{{ slot }}</div>
                                    </div>

                                    <!-- Day Columns -->
                                    <div class="flex flex-1 relative">
                                        <div
                                            v-for="day in weekDays"
                                            :key="day.dayIndex"
                                            class="relative border-r border-gray-100 last:border-r-0"
                                            :class="[dayColumnClass, day.isToday ? 'bg-brand-50/40' : '']"
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
                                                    class="absolute cursor-default overflow-hidden rounded-lg border border-dashed border-gray-300 bg-gray-100"
                                                    :style="{ ...getBookingHorizontalStyle(booking), top: (getBookingTop(booking) + 4) + 'px', height: (getBookingHeight(booking) - 8) + 'px' }"
                                                >
                                                    <div
                                                        class="flex h-full w-full items-center justify-center"
                                                    >
                                                        <span class="text-xs font-semibold text-gray-500 bg-white/80 px-2 py-0.5 rounded">
                                                            {{ booking.customer }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Regular -->
                                                <button
                                                    v-else
                                                    type="button"
                                                    class="group absolute min-h-11 cursor-pointer rounded-lg px-2 py-1.5 text-left transition-all duration-150 hover:z-30 hover:min-w-[12rem] hover:scale-[1.02] hover:shadow-md focus-visible:z-30 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-200"
                                                    :class="[
                                                        getCardStyle(booking.colorType).wrapper,
                                                        selectedBooking && selectedBooking.id === booking.id ? 'ring-2 ring-brand-500 ring-offset-1' : '',
                                                    ]"
                                                    :style="{ ...getBookingHorizontalStyle(booking), top: (getBookingTop(booking) + 4) + 'px', height: (getBookingHeight(booking) - 8) + 'px' }"
                                                    @click="selectBooking(booking)"
                                                >
                                                    <div class="h-full overflow-hidden">
                                                    <div class="flex items-start justify-between gap-1">
                                                        <span class="min-w-0 text-xs leading-tight truncate group-hover:whitespace-normal group-hover:break-words" :class="getCardStyle(booking.colorType).name">
                                                            {{ booking.customer }}
                                                        </span>
                                                        <span v-if="booking.status === 'pending'" class="h-2 w-2 flex-none rounded-full bg-amber-500" aria-hidden="true"></span>
                                                        <span v-if="booking.status === 'pending'" class="sr-only">Čeká na potvrzení</span>
                                                    </div>
                                                    <div class="min-w-0 text-xs mt-0.5 leading-tight truncate group-hover:whitespace-normal group-hover:break-words" :class="getCardStyle(booking.colorType).service">
                                                        {{ booking.shop }}
                                                    </div>
                                                    </div>
                                                </button>
                                            </template>

                                            <!-- Current time line (today only) -->
                                            <div
                                                v-if="day.isToday && currentTimeTop >= 0 && currentTimeTop <= gridHeight"
                                                class="absolute left-0 right-0 flex items-center pointer-events-none z-10"
                                                :style="{ top: currentTimeTop + 'px' }"
                                            >
                                                <div class="-ml-1.5 h-2.5 w-2.5 flex-none rounded-full bg-brand-700"></div>
                                                <div class="h-px flex-1 bg-brand-600"></div>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Current time label in gutter -->
                                    <div
                                        v-if="currentTimeTop >= 0 && currentTimeTop <= gridHeight"
                                        class="absolute left-1 pointer-events-none z-20"
                                        :style="{ top: (currentTimeTop - 8) + 'px' }"
                                    >
                                        <span class="text-xs font-bold text-brand-700">{{ currentTimeLabel }}</span>
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
                        class="fixed inset-x-4 bottom-20 top-24 z-50 flex flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-lift lg:static lg:inset-auto lg:z-auto lg:w-80 lg:flex-none lg:shadow-sm"
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
                            <h2 class="text-base font-extrabold text-ink">Detail rezervace</h2>
                            <button
                                type="button"
                                @click="closeDetails"
                                class="flex min-h-11 min-w-11 items-center justify-center rounded-xl text-muted transition-colors hover:bg-gray-100 hover:text-ink"
                                aria-label="Zavřít detail"
                            >
                                <X :size="19" aria-hidden="true" />
                            </button>
                        </div>

                        <!-- Scrollable body -->
                        <div class="flex-1 overflow-y-auto" tabindex="0" aria-label="Detail rezervace">

                            <!-- Customer -->
                            <div class="px-5 py-4 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
                                        :class="[selectedBooking.avatarBg, selectedBooking.avatarText]"
                                    >{{ selectedBooking.initials }}</div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-sm">{{ selectedBooking.customer }}</div>
                                        <div class="mt-0.5 text-xs text-muted">{{ selectedBooking.customerType === 'Regular Customer' ? 'Vracející se zákazník' : 'Nový zákazník' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Info rows -->
                            <div class="px-5 py-4 space-y-4 border-b border-gray-100">
                                <!-- Date & Time -->
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <CalendarDays :size="17" class="text-muted" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <div class="mb-0.5 text-xs font-bold uppercase tracking-wide text-muted">Datum a čas</div>
                                        <div class="text-sm font-medium text-gray-800 leading-snug">{{ selectedBooking.dateStr }} • {{ selectedBooking.timeStr }}</div>
                                    </div>
                                </div>
                                <!-- Shop Type -->
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <Tag :size="17" class="text-muted" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <div class="mb-0.5 text-xs font-bold uppercase tracking-wide text-muted">Služba</div>
                                        <div class="text-sm font-medium text-gray-800">{{ selectedBooking.serviceDetail }}</div>
                                    </div>
                                </div>
                                <!-- Duration -->
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <Clock3 :size="17" class="text-muted" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <div class="mb-0.5 text-xs font-bold uppercase tracking-wide text-muted">Délka</div>
                                        <div class="text-sm font-medium text-gray-800">{{ selectedBooking.duration }} minut</div>
                                    </div>
                                </div>

                            </div>

                            <!-- Notes -->
                            <div v-if="selectedBooking.notes" class="px-5 py-4 border-b border-gray-100">
                                <div class="mb-2 text-xs font-bold uppercase tracking-wide text-muted">Poznámka zákazníka</div>
                                <div class="bg-gray-50 rounded-xl p-3 text-sm text-gray-600 leading-relaxed">
                                    {{ selectedBooking.notes }}
                                </div>
                            </div>

                            <!-- Action buttons -->
                            <div class="px-5 py-4 space-y-2 border-b border-gray-100">
                                <Link
                                    :href="route('vendor.bookings.show', selectedBooking.id)"
                                    class="ui-button ui-button-primary w-full"
                                >
                                    <Eye :size="18" aria-hidden="true" />
                                    Otevřít rezervaci
                                </Link>
                                <a
                                    v-if="selectedBooking.customerEmail"
                                    :href="'mailto:' + selectedBooking.customerEmail"
                                    class="ui-button ui-button-secondary w-full"
                                >
                                    <Mail :size="18" aria-hidden="true" />
                                    Napsat e-mail
                                </a>
                                <a
                                    v-if="selectedBooking.customerPhone"
                                    :href="'tel:' + selectedBooking.customerPhone"
                                    class="ui-button ui-button-secondary w-full"
                                >
                                    <Phone :size="18" aria-hidden="true" />
                                    Zavolat zákazníkovi
                                </a>
                            </div>

                            <!-- Status actions -->
                            <div v-if="availableStatusActions(selectedBooking).length" class="px-5 py-4">
                                <!-- Status dropdown -->
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click="showStatusDropdown = !showStatusDropdown"
                                        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-line px-4 text-sm font-bold text-muted transition-colors hover:bg-gray-50 hover:text-ink"
                                    >
                                        <span :class="['w-2 h-2 rounded-full', getStatusConfig(selectedBooking.status).dot]"></span>
                                        <span class="ml-2">{{ getStatusConfig(selectedBooking.status).label }}</span>
                                        <ChevronDown :size="16" aria-hidden="true" />
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <div v-if="showStatusDropdown" class="absolute right-0 top-full mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-10">
                                        <button
                                            v-for="status in availableStatusActions(selectedBooking)"
                                            :key="status"
                                            @click="updateStatus(status)"
                                            class="flex min-h-11 w-full items-center gap-2 px-4 text-left text-sm font-bold transition-colors hover:bg-gray-50"
                                        >
                                            <span :class="['w-2 h-2 rounded-full', getStatusConfig(status).dot]"></span>
                                            {{ getStatusConfig(status).label }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </transition>

            </div>
        </div>
    </VendorLayout>
</template>
