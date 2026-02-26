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

function getStatusClasses(status) {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        confirmed: 'bg-blue-100 text-blue-800 border-blue-200',
        completed: 'bg-green-100 text-green-800 border-green-200',
        cancelled: 'bg-red-100 text-red-800 border-red-200',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 border-gray-200';
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatTime(time) {
    return time;
}
</script>

<template>
    <Head :title="`Booking #${booking.id}`" />

    <VendorLayout activePage="bookings">
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('vendor.bookings.index')"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Booking #{{ booking.id }}</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ formatDate(booking.booking_date) }}</p>
                    </div>
                </div>
                <span
                    :class="['px-4 py-2 rounded-full text-sm font-medium border', getStatusClasses(booking.status)]"
                >
                    {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
                </span>
            </div>

            <div class="grid grid-cols-3 gap-6">
                <!-- Main Info -->
                <div class="col-span-2 space-y-6">
                    <!-- Customer Info -->
                    <div class="bg-white rounded-xl p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h2>
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-2xl font-bold text-blue-700">
                                {{ booking.customer.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900 text-lg">{{ booking.customer.name }}</div>
                                <div class="text-gray-500 mt-1">{{ booking.customer.email }}</div>
                                <div class="text-gray-500">{{ booking.customer.phone || 'No phone number' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Service Info -->
                    <div class="bg-white rounded-xl p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Service Details</h2>
                        <div class="space-y-4">
                            <div class="flex justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-500">Service</span>
                                <span class="font-medium text-gray-900">{{ booking.service.name }}</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-500">Offering</span>
                                <span class="font-medium text-gray-900">{{ booking.offering.name }}</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-500">Date</span>
                                <span class="font-medium text-gray-900">{{ formatDate(booking.booking_date) }}</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-500">Time</span>
                                <span class="font-medium text-gray-900">{{ formatTime(booking.start_time) }} - {{ formatTime(booking.end_time) }}</span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-500">Duration</span>
                                <span class="font-medium text-gray-900">{{ booking.offering.duration_minutes }} minutes</span>
                            </div>
                            <div class="flex justify-between py-3">
                                <span class="text-gray-500">Total Price</span>
                                <span class="font-bold text-xl text-blue-600">${{ Number(booking.total_price).toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Notes -->
                    <div v-if="booking.customer_notes" class="bg-yellow-50 rounded-xl p-6 border border-yellow-200">
                        <h2 class="text-lg font-semibold text-yellow-800 mb-2">Customer Notes</h2>
                        <p class="text-yellow-700">{{ booking.customer_notes }}</p>
                    </div>

                    <!-- Vendor Notes -->
                    <div class="bg-white rounded-xl p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Internal Notes</h2>
                            <button
                                @click="showNotesModal = true"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                + Add Note
                            </button>
                        </div>
                        <div v-if="booking.notes" class="space-y-3">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ booking.notes }}</pre>
                            </div>
                        </div>
                        <div v-else class="text-gray-500 text-sm">No notes added yet</div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Actions -->
                    <div class="bg-white rounded-xl p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                        
                        <div v-if="booking.status === 'pending'" class="space-y-3">
                            <button
                                @click="confirmBooking"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition-colors"
                            >
                                Confirm Booking
                            </button>
                            <button
                                @click="showCancelModal = true"
                                class="w-full border border-red-200 hover:bg-red-50 text-red-600 font-semibold py-3 rounded-xl transition-colors"
                            >
                                Decline Booking
                            </button>
                        </div>

                        <div v-else-if="booking.status === 'confirmed'" class="space-y-3">
                            <button
                                @click="completeBooking"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl transition-colors"
                            >
                                Mark as Completed
                            </button>
                            <button
                                @click="showCancelModal = true"
                                class="w-full border border-red-200 hover:bg-red-50 text-red-600 font-semibold py-3 rounded-xl transition-colors"
                            >
                                Cancel Booking
                            </button>
                        </div>

                        <div v-else class="text-gray-500 text-sm">
                            This booking is {{ booking.status }} and cannot be modified.
                        </div>
                    </div>

                    <!-- Customer History -->
                    <div v-if="customerHistory.length > 0" class="bg-white rounded-xl p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer History</h2>
                        <div class="space-y-3">
                            <div
                                v-for="history in customerHistory"
                                :key="history.id"
                                class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0"
                            >
                                <div>
                                    <div class="font-medium text-gray-900">{{ history.service.name }}</div>
                                    <div class="text-sm text-gray-500">{{ formatDate(history.booking_date) }}</div>
                                </div>
                                <span
                                    :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusClasses(history.status)]"
                                >
                                    {{ history.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div v-if="showCancelModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Cancel Booking</h3>
                <p class="text-gray-500 mb-4">Are you sure you want to cancel this booking? This action cannot be undone.</p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cancellation Reason (Optional)</label>
                    <textarea
                        v-model="cancellationReason"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-xl transition-colors"
                    >
                        Cancel Booking
                    </button>
                </div>
            </div>
        </div>

        <!-- Add Note Modal -->
        <div v-if="showNotesModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Note</h3>
                
                <div class="mb-4">
                    <textarea
                        v-model="newNote"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl transition-colors"
                    >
                        Add Note
                    </button>
                </div>
            </div>
        </div>
    </VendorLayout>
</template>
