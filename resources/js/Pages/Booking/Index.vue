<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    service:  { type: Object, required: true },
    offering: { type: Object, default: null },
    date:     { type: String, default: null },
    time:     { type: String, default: null },
    authUser: { type: Object, default: null },
    existingBookings: { type: Array, default: () => [] },
});

// ── Form state ────────────────────────────────────────────────────────────────
const form = useForm({
    service_id: props.service.id,
    service_offering_id: props.offering?.id || null,
    provider_id: props.service.user_id || props.service.owner?.id || null,
    booking_date: props.date || '',
    start_time: props.time || '',
    end_time: '',
    full_name: props.authUser?.name || '',
    email: props.authUser?.email || '',
    phone: props.authUser?.phone || '',
    customer_notes: '',
});

const errors = ref({});

function validateForm() {
    const e = {};
    if (!form.full_name?.trim()) e.full_name = 'Full name is required.';
    if (!form.email?.trim())      e.email      = 'Email address is required.';
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email))
        e.email = 'Please enter a valid email address.';
    if (!form.phone?.trim())      e.phone      = 'Phone number is required.';

    // Business hours validation
    const businessHours = props.service.business_hours ?? [];
    if (businessHours.length > 0 && form.booking_date) {
        const bookingDate = new Date(form.booking_date + 'T00:00:00');
        const dayOfWeek = bookingDate.getDay();
        const bh = businessHours.find(h => h.day_of_week === dayOfWeek);

        if (!bh) {
            e.booking_date = 'The service is not available on this day.';
        } else if (form.start_time) {
            let timeStr = form.start_time;
            let hours, minutes;
            const ampmMatch = timeStr.match(/(\d+):(\d+)\s*(AM|PM)/i);
            if (ampmMatch) {
                hours = parseInt(ampmMatch[1]);
                minutes = parseInt(ampmMatch[2]);
                if (ampmMatch[3].toUpperCase() === 'PM' && hours !== 12) hours += 12;
                if (ampmMatch[3].toUpperCase() === 'AM' && hours === 12) hours = 0;
            } else {
                const parts = timeStr.split(':');
                hours = parseInt(parts[0]);
                minutes = parseInt(parts[1]);
            }
            const startMinutes = hours * 60 + minutes;
            const [bhFromH, bhFromM] = bh.time_from.split(':').map(Number);
            const [bhToH, bhToM] = bh.time_to.split(':').map(Number);
            const bhFromMinutes = bhFromH * 60 + bhFromM;
            const bhToMinutes = bhToH * 60 + bhToM;

            if (startMinutes < bhFromMinutes || startMinutes >= bhToMinutes) {
                e.start_time = `Time must be between ${bh.time_from} and ${bh.time_to}.`;
            }
        }
    }

    // Existing booking conflict check
    if (!e.start_time && form.start_time && props.existingBookings.length > 0 && props.offering) {
        let timeStr = form.start_time;
        let hours, minutes;
        const ampmMatch = timeStr.match(/(\d+):(\d+)\s*(AM|PM)/i);
        if (ampmMatch) {
            hours = parseInt(ampmMatch[1]);
            minutes = parseInt(ampmMatch[2]);
            if (ampmMatch[3].toUpperCase() === 'PM' && hours !== 12) hours += 12;
            if (ampmMatch[3].toUpperCase() === 'AM' && hours === 12) hours = 0;
        } else {
            const parts = timeStr.split(':');
            hours = parseInt(parts[0]);
            minutes = parseInt(parts[1]);
        }
        const startStr = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
        const endMinutes = hours * 60 + minutes + props.offering.duration_minutes;
        const endH = Math.floor(endMinutes / 60);
        const endM = endMinutes % 60;
        const endStr = `${String(endH).padStart(2, '0')}:${String(endM).padStart(2, '0')}`;

        for (const booking of props.existingBookings) {
            if (startStr < booking.end_time && endStr > booking.start_time) {
                e.start_time = 'This time slot is already booked. Please choose a different time.';
                break;
            }
        }
    }

    errors.value = e;
    return Object.keys(e).length === 0;
}

function submitBooking() {
    if (!validateForm()) return;

    form.transform((data) => ({
        ...data,
        service_id: form.service_id,
        service_offering_id: form.service_offering_id || props.offering?.id,
        provider_id: form.provider_id,
        booking_date: form.booking_date,
        start_time: form.start_time,
        end_time: form.end_time,
        full_name: form.full_name,
        email: form.email,
        phone: form.phone,
        customer_notes: form.customer_notes,
    }));

    let url = '/bookings';
    try { url = route('bookings.store'); } catch (e) { /* fallback */ }

    form.post(url, {
        onError: (errs) => { errors.value = errs; },
    });
}

function formatPrice(amount) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00');
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
}
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Back link -->
                <div class="mb-6">
                    <Link :href="route('services.show', service.slug)" class="inline-flex items-center gap-2 text-sm font-medium text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Back to {{ service.name }}
                    </Link>
                </div>

                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                    <!-- Left Column: Summary (2 cols) -->
                    <div class="lg:col-span-2 space-y-4">

                        <!-- Service Card -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-white font-bold text-base">{{ service.name }}</h2>
                                        <p class="text-blue-100 text-xs">{{ service.category?.name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Package -->
                        <div v-if="offering" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Selected Package</span>
                            </div>
                            <h3 class="font-bold text-gray-900 text-sm mb-3">{{ offering.name }}</h3>
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl">
                                <span class="text-sm text-gray-600 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ offering.duration_minutes }} mins
                                </span>
                                <span class="text-lg font-bold text-blue-600">{{ formatPrice(offering.price) }}</span>
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div v-if="date || time" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-7 h-7 rounded-lg bg-green-50 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Appointment</span>
                            </div>
                            <div class="space-y-2">
                                <div v-if="date" class="flex items-center gap-2.5 text-sm p-3 bg-gray-50 rounded-xl">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="font-semibold text-gray-900">{{ formatDate(date) }}</span>
                                </div>
                                <div v-if="time" class="flex items-center gap-2.5 text-sm p-3 bg-gray-50 rounded-xl">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-semibold text-gray-900">{{ time }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Cancellation info -->
                        <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-4 flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-xs text-gray-600 leading-relaxed">
                                <span class="font-semibold text-gray-700">Free cancellation</span> up to 24 hours before your appointment. No-show fees may apply.
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Booking Form (3 cols) -->
                    <div class="lg:col-span-3">
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                            <!-- Form Header -->
                            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-bold text-gray-900">Your Information</h2>
                                        <p class="text-xs text-gray-500">Fill in your details to complete the booking</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">

                                <!-- Business hours / conflict warnings -->
                                <div v-if="errors.booking_date || errors.start_time || form.errors?.booking_date || form.errors?.start_time" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                                    <div class="flex items-start gap-2.5">
                                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                        <div class="text-sm text-red-700">
                                            <p v-if="errors.booking_date">{{ errors.booking_date }}</p>
                                            <p v-if="errors.start_time">{{ errors.start_time }}</p>
                                            <p v-if="form.errors?.booking_date">{{ form.errors.booking_date }}</p>
                                            <p v-if="form.errors?.start_time">{{ form.errors.start_time }}</p>
                                        </div>
                                    </div>
                                </div>

                                <form @submit.prevent="submitBooking" class="space-y-5">
                                    <!-- Full Name -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name <span class="text-red-400">*</span></label>
                                        <input
                                            v-model="form.full_name"
                                            type="text"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm focus:bg-white transition-colors"
                                            :class="{ 'border-red-300 focus:ring-red-500': errors.full_name }"
                                            placeholder="Enter your full name"
                                        />
                                        <p v-if="errors.full_name" class="mt-1.5 text-xs text-red-500">{{ errors.full_name }}</p>
                                    </div>

                                    <!-- Email & Phone -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                                            <input
                                                v-model="form.email"
                                                type="email"
                                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm focus:bg-white transition-colors"
                                                :class="{ 'border-red-300 focus:ring-red-500': errors.email }"
                                                placeholder="you@example.com"
                                            />
                                            <p v-if="errors.email" class="mt-1.5 text-xs text-red-500">{{ errors.email }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Phone <span class="text-red-400">*</span></label>
                                            <input
                                                v-model="form.phone"
                                                type="tel"
                                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm focus:bg-white transition-colors"
                                                :class="{ 'border-red-300 focus:ring-red-500': errors.phone }"
                                                placeholder="(555) 123-4567"
                                            />
                                            <p v-if="errors.phone" class="mt-1.5 text-xs text-red-500">{{ errors.phone }}</p>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Special Requests <span class="text-gray-400 font-normal">(optional)</span></label>
                                        <textarea
                                            v-model="form.customer_notes"
                                            rows="3"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm resize-none focus:bg-white transition-colors"
                                            placeholder="Any special requests or notes for the provider..."
                                        ></textarea>
                                    </div>

                                    <!-- Summary bar -->
                                    <div v-if="offering" class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                                        <div class="text-sm text-gray-600">
                                            <span class="font-semibold text-gray-900">{{ offering.name }}</span>
                                            <span class="mx-1.5 text-gray-300">·</span>
                                            {{ offering.duration_minutes }} mins
                                        </div>
                                        <span class="text-xl font-bold text-gray-900">{{ formatPrice(offering.price) }}</span>
                                    </div>

                                    <!-- Submit -->
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:from-blue-400 disabled:to-indigo-400 text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-blue-200/50 hover:shadow-xl hover:shadow-blue-300/50 text-sm flex items-center justify-center gap-2 transform hover:-translate-y-0.5 disabled:transform-none disabled:shadow-none"
                                    >
                                        <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <template v-if="form.processing">Processing...</template>
                                        <template v-else>
                                            Confirm Booking
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                            </svg>
                                        </template>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
