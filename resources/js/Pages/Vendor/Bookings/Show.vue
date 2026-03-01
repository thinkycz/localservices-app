<script setup>
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    booking: { type: Object, default: () => ({}) },
    customerHistory: { type: Array, default: () => [] },
});

const showCancelModal = ref(false);
const cancellationReason = ref('');
const showNotesModal = ref(false);
const newNote = ref('');

function confirmBooking() {
    router.post(route('vendor.bookings.confirm', props.booking.id));
}

function completeBooking() {
    router.post(route('vendor.bookings.complete', props.booking.id));
}

function cancelBooking() {
    router.post(route('vendor.bookings.cancel', props.booking.id), {
        cancellation_reason: cancellationReason.value,
    }, {
        onSuccess: () => {
            showCancelModal.value = false;
            cancellationReason.value = '';
        },
    });
}

function addNote() {
    router.post(route('vendor.bookings.notes', props.booking.id), {
        notes: newNote.value,
    }, {
        onSuccess: () => {
            showNotesModal.value = false;
            newNote.value = '';
        },
    });
}

function getStatusConfig(status) {
    const config = {
        pending: { label: 'Pending', bg: 'bg-amber-50', text: 'text-amber-700', ring: 'ring-amber-600/20', dot: 'bg-amber-500' },
        confirmed: { label: 'Confirmed', bg: 'bg-blue-50', text: 'text-blue-700', ring: 'ring-blue-700/20', dot: 'bg-blue-500' },
        completed: { label: 'Completed', bg: 'bg-emerald-50', text: 'text-emerald-700', ring: 'ring-emerald-600/20', dot: 'bg-emerald-500' },
        cancelled: { label: 'Cancelled', bg: 'bg-red-50', text: 'text-red-700', ring: 'ring-red-600/20', dot: 'bg-red-500' },
    };
    return config[status] || config.pending;
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatShortDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function formatTime(time) {
    if (!time) return '';
    const s = String(time).trim();
    if (!s) return '';
    if (/[ap]m\b/.test(s) && !s.includes('T')) return s;

    const asDate = new Date(s);
    if (!Number.isNaN(asDate.getTime())) {
        return asDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    const m = s.match(/^(\d{1,2}):(\d{2})(?::(\d{2})(?:\.\d+)?)?$/);
    if (m) {
        const h = Number(m[1]);
        const min = Number(m[2]);
        const d = new Date(1970, 0, 1, h, min, 0);
        return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    return s;
}

const statusConfig = getStatusConfig(props.booking.status);
</script>

<template>
    <Head :title="`Booking #${booking.id}`" />

    <VendorLayout activePage="bookings">
        <div class="flex flex-col gap-6">

            <!-- Back link -->
            <div>
                <Link
                    :href="route('vendor.bookings.index')"
                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Bookings
                </Link>
            </div>

            <!-- Booking Header Card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-xl font-bold shadow-md flex-shrink-0">
                        #{{ booking.id }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3">
                            <h1 class="text-xl font-bold text-gray-900">{{ booking.service.name }}</h1>
                            <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 ring-inset', statusConfig.bg, statusConfig.text, statusConfig.ring]">
                                <span :class="['w-1.5 h-1.5 rounded-full', statusConfig.dot]"></span>
                                {{ statusConfig.label }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-400 mt-0.5">{{ booking.offering.name }} · {{ formatDate(booking.booking_date) }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <!-- Actions inline -->
                        <template v-if="booking.status === 'pending'">
                            <button
                                @click="confirmBooking"
                                class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md transform hover:-translate-y-0.5 text-white font-semibold text-sm rounded-xl transition-all duration-200"
                            >
                                Confirm
                            </button>
                            <button
                                @click="showCancelModal = true"
                                class="px-5 py-2.5 border border-red-200 hover:bg-red-50 text-red-600 font-semibold text-sm rounded-xl transition-colors"
                            >
                                Decline
                            </button>
                        </template>
                        <template v-else-if="booking.status === 'confirmed'">
                            <button
                                @click="completeBooking"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl transition-colors shadow-sm"
                            >
                                Mark Completed
                            </button>
                            <button
                                @click="showCancelModal = true"
                                class="px-5 py-2.5 border border-red-200 hover:bg-red-50 text-red-600 font-semibold text-sm rounded-xl transition-colors"
                            >
                                Cancel
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Info Cards Row -->
            <div class="grid grid-cols-5 gap-4">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">Date</div>
                    <div class="text-sm font-bold text-gray-900">{{ formatShortDate(booking.booking_date) }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-cyan-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">Time</div>
                    <div class="text-sm font-bold text-gray-900">{{ formatTime(booking.start_time) }} – {{ formatTime(booking.end_time) }}</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">Duration</div>
                    <div class="text-sm font-bold text-gray-900">{{ booking.offering.duration_minutes }} min</div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">Total Price</div>
                    <div class="text-lg font-bold text-blue-600">${{ Number(booking.total_price).toFixed(2) }}</div>
                </div>

                <!-- Customer mini-card -->
                <Link :href="route('vendor.customers.show', booking.customer.id)" class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-blue-200 hover:bg-blue-50/30 transition-colors group">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ booking.customer.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="text-xs text-gray-500">Customer</div>
                    </div>
                    <div class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors truncate">{{ booking.customer.name }}</div>
                </Link>
            </div>

            <!-- Customer Notes -->
            <div v-if="booking.customer_notes" class="bg-amber-50 rounded-2xl p-5 border border-amber-200/60">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    <h2 class="text-sm font-semibold text-amber-800">Customer Notes</h2>
                </div>
                <p class="text-sm text-amber-700 leading-relaxed">{{ booking.customer_notes }}</p>
            </div>

            <!-- Two-column: Contact + Notes side by side -->
            <div class="grid grid-cols-2 gap-6">
                <!-- Contact Actions -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Contact Customer</h2>
                    </div>
                    <div class="p-6 flex items-center gap-3">
                        <a
                            v-if="booking.customer.email"
                            :href="'mailto:' + booking.customer.email"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-200 rounded-xl text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ booking.customer.email }}
                        </a>
                        <a
                            v-if="booking.customer.phone"
                            :href="'tel:' + booking.customer.phone"
                            class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-200 rounded-xl text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ booking.customer.phone }}
                        </a>
                        <span v-if="!booking.customer.phone" class="text-sm text-gray-400">No phone number</span>
                    </div>
                </div>

                <!-- Internal Notes -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Internal Notes</h2>
                        <button
                            @click="showNotesModal = true"
                            class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Note
                        </button>
                    </div>
                    <div class="p-6">
                        <div v-if="booking.notes" class="bg-gray-50 rounded-xl p-4">
                            <pre class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed font-sans">{{ booking.notes }}</pre>
                        </div>
                        <div v-else class="text-center py-2">
                            <div class="text-sm text-gray-400">No notes added yet</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer History -->
            <div v-if="customerHistory.length > 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Other Bookings from {{ booking.customer.name }}</h2>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/30">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Service</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="history in customerHistory"
                            :key="history.id"
                            class="group hover:bg-blue-50/30 transition-colors cursor-pointer"
                            @click="router.visit(route('vendor.bookings.show', history.id))"
                        >
                            <td class="px-6 py-3.5">
                                <span class="text-sm font-medium text-gray-900">{{ history.service.name }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-sm text-gray-600">{{ formatShortDate(history.booking_date) }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 ring-inset',
                                    getStatusConfig(history.status).bg,
                                    getStatusConfig(history.status).text,
                                    getStatusConfig(history.status).ring]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', getStatusConfig(history.status).dot]"></span>
                                    {{ getStatusConfig(history.status).label }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <Link
                                    :href="route('vendor.bookings.show', history.id)"
                                    class="text-gray-400 group-hover:text-blue-600 transition-colors"
                                    @click.stop
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cancel Modal -->
        <teleport to="body">
            <transition
                enter-active-class="transition-opacity duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showCancelModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" @click.self="showCancelModal = false">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4 shadow-xl">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Cancel Booking</h3>
                                <p class="text-sm text-gray-500">This action cannot be undone.</p>
                            </div>
                        </div>
                        
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cancellation Reason <span class="text-gray-400 font-normal">(Optional)</span></label>
                            <textarea
                                v-model="cancellationReason"
                                rows="3"
                                class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm resize-none"
                                placeholder="Enter reason for cancellation..."
                            ></textarea>
                        </div>

                        <div class="flex gap-3">
                            <button
                                @click="showCancelModal = false"
                                class="flex-1 border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold py-2.5 rounded-xl transition-colors"
                            >
                                Keep Booking
                            </button>
                            <button
                                @click="cancelBooking"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-xl transition-colors shadow-sm"
                            >
                                Cancel Booking
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </teleport>

        <!-- Add Note Modal -->
        <teleport to="body">
            <transition
                enter-active-class="transition-opacity duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showNotesModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" @click.self="showNotesModal = false">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4 shadow-xl">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Add Note</h3>
                        </div>
                        
                        <div class="mb-5">
                            <textarea
                                v-model="newNote"
                                rows="4"
                                class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm resize-none"
                                placeholder="Enter your note..."
                            ></textarea>
                        </div>

                        <div class="flex gap-3">
                            <button
                                @click="showNotesModal = false"
                                class="flex-1 border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold py-2.5 rounded-xl transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                @click="addNote"
                                class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md transform hover:-translate-y-0.5 text-white font-semibold py-2.5 rounded-xl transition-all duration-200"
                            >
                                Add Note
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </teleport>
    </VendorLayout>
</template>
