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
            // Parse the start_time (could be 12h format like "1:00 PM" or 24h like "13:00")
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
    console.log('Button clicked!');
    console.log('Form data:', form.data());
    
    try {
        console.log('Route:', route('bookings.store'));
    } catch (e) {
        console.log('Route error:', e);
    }
    
    if (!validateForm()) {
        console.log('Validation failed:', errors.value);
        return;
    }
    
    console.log('Submitting form...');
    
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
    
    // Get the URL - try route helper first, fallback to hardcoded
    let url = '/bookings';
    try {
        url = route('bookings.store');
    } catch (e) {
        console.log('Route helper failed, using fallback URL');
    }
    
    console.log('Submitting to URL:', url);
    
    form.post(url, {
        onSuccess: (page) => {
            console.log('Success:', page);
        },
        onError: (errors) => {
            console.log('Error:', errors);
            errors.value = errors;
        },
        onFinish: () => {
            console.log('Form submission finished');
        }
    });
}

function formatPrice(amount) {
    return `$${Number(amount).toFixed(2)}`;
}
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link :href="route('services.show', service.slug)" class="text-blue-600 hover:text-blue-800 text-sm inline-flex items-center transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Service
                    </Link>
                    <h1 class="text-2xl font-bold text-gray-900 mt-2">Book Appointment</h1>
                </div>

                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Service Summary -->
                    <div class="lg:col-span-1 space-y-4">
                        <!-- Service Card -->
                        <div class="bg-white rounded-xl border border-gray-200 p-5">
                            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Service</h2>
                            <p class="font-semibold text-gray-900 text-lg">{{ service.name }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ service.category?.name }}</p>
                        </div>

                        <!-- Selected Package -->
                        <div v-if="offering" class="bg-white rounded-xl border border-gray-200 p-5">
                            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Selected Package</h2>
                            <p class="font-semibold text-gray-900">{{ offering.name }}</p>
                            <div class="flex items-center justify-between mt-3">
                                <span class="text-sm text-gray-500">{{ offering.duration_minutes }} min</span>
                                <span class="text-xl font-bold text-blue-600">{{ formatPrice(offering.price) }}</span>
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div v-if="date || time" class="bg-white rounded-xl border border-gray-200 p-5">
                            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Appointment</h2>
                            <div class="space-y-2">
                                <div v-if="date" class="flex items-center text-gray-700 text-sm">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ date }}
                                </div>
                                <div v-if="time" class="flex items-center text-gray-700 text-sm">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ time }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Booking Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-5">Your Information</h2>

                            <!-- Business hours / conflict warnings -->
                            <div v-if="errors.booking_date || errors.start_time || form.errors?.booking_date || form.errors?.start_time" class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name *</label>
                                    <input
                                        v-model="form.full_name"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                        :class="{ 'border-red-500': errors.full_name }"
                                        placeholder="Enter your full name"
                                    />
                                    <p v-if="errors.full_name" class="mt-1 text-sm text-red-600">{{ errors.full_name }}</p>
                                </div>

                                <!-- Email & Phone -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email *</label>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            :class="{ 'border-red-500': errors.email }"
                                            placeholder="you@example.com"
                                        />
                                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone *</label>
                                        <input
                                            v-model="form.phone"
                                            type="tel"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            :class="{ 'border-red-500': errors.phone }"
                                            placeholder="(555) 123-4567"
                                        />
                                        <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes (optional)</label>
                                    <textarea
                                        v-model="form.customer_notes"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none"
                                        placeholder="Any special requests..."
                                    ></textarea>
                                </div>

                                <!-- Submit -->
                                <div class="pt-2">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold py-2.5 px-4 rounded-lg transition text-sm"
                                    >
                                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ form.processing ? 'Processing...' : 'Confirm Booking' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
