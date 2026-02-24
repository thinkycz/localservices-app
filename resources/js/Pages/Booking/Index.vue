<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    service:  { type: Object, required: true },
    offering: { type: Object, default: null },
    date:     { type: String, default: null },
    time:     { type: String, default: null },
});

// ── Form state ────────────────────────────────────────────────────────────────
const form = ref({
    firstName:    '',
    lastName:     '',
    email:        '',
    phone:        '',
    requirements: '',
});

const errors = ref({});

function validateForm() {
    const e = {};
    if (!form.value.firstName.trim()) e.firstName = 'First name is required.';
    if (!form.value.lastName.trim())  e.lastName  = 'Last name is required.';
    if (!form.value.email.trim())     e.email     = 'Email address is required.';
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email))
        e.email = 'Please enter a valid email address.';
    if (!form.value.phone.trim())     e.phone     = 'Phone number is required.';
    errors.value = e;
    return Object.keys(e).length === 0;
}

function continueToPayment() {
    if (!validateForm()) return;
    // TODO: navigate to payment step (Step 2)
    alert('Proceeding to payment step…');
}

// ── Derived booking info ──────────────────────────────────────────────────────
const SERVICE_FEE = 5;

const sessionFee = computed(() => props.offering?.price ?? 0);
const total      = computed(() => sessionFee.value + SERVICE_FEE);

function formatPrice(amount) {
    return `$${Number(amount).toFixed(2)}`;
}

// Parse date string (YYYY-MM-DD) → formatted label
const formattedDate = computed(() => {
    if (!props.date) return null;
    const d = new Date(props.date + 'T00:00:00');
    return d.toLocaleDateString('en-US', {
        weekday: 'long',
        month:   'short',
        day:     'numeric',
        year:    'numeric',
    });
});

// Compute end time from start time + duration
const timeRange = computed(() => {
    if (!props.time || !props.offering?.duration_minutes) return props.time ?? null;

    const [timePart, meridiem] = props.time.split(' ');
    let [hours, minutes] = timePart.split(':').map(Number);
    if (meridiem === 'PM' && hours !== 12) hours += 12;
    if (meridiem === 'AM' && hours === 12) hours = 0;

    const startTotal = hours * 60 + minutes;
    const endTotal   = startTotal + props.offering.duration_minutes;

    const endH   = Math.floor(endTotal / 60) % 24;
    const endM   = endTotal % 60;
    const endMer = endH >= 12 ? 'PM' : 'AM';
    const endH12 = endH % 12 || 12;
    const endStr = `${String(endH12).padStart(2, '0')}:${String(endM).padStart(2, '0')} ${endMer}`;

    return `${props.time} - ${endStr} EST`;
});

// Cancel → back to service page
function cancel() {
    router.visit(route('services.show', props.service.slug));
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">

        <!-- ── Header ──────────────────────────────────────────────────────── -->
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Logo -->
                <Link :href="route('home')" class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center">
                        <!-- calendar-check icon -->
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">ReserveNow</span>
                </Link>

                <!-- Right actions -->
                <div class="flex items-center gap-4 text-sm">
                    <a href="#" class="text-gray-600 hover:text-gray-900 font-medium">Need help?</a>
                    <span class="text-gray-300">|</span>
                    <button @click="cancel" class="text-gray-600 hover:text-gray-900 font-medium">Cancel</button>
                </div>
            </div>
        </header>

        <!-- ── Step Progress ────────────────────────────────────────────────── -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-center gap-0 max-w-lg mx-auto">

                    <!-- Step 1: SERVICE (active) -->
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow">
                            1
                        </div>
                        <span class="text-xs font-bold text-blue-600 mt-1.5 tracking-wide uppercase">Service</span>
                    </div>

                    <!-- Connector 1→2 -->
                    <div class="flex-1 h-0.5 bg-blue-600 mx-2 mb-5" />

                    <!-- Step 2: PAYMENT -->
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full border-2 border-gray-300 text-gray-400 flex items-center justify-center font-bold text-sm">
                            2
                        </div>
                        <span class="text-xs font-medium text-gray-400 mt-1.5 tracking-wide uppercase">Payment</span>
                    </div>

                    <!-- Connector 2→3 -->
                    <div class="flex-1 h-0.5 bg-gray-300 mx-2 mb-5" />

                    <!-- Step 3: CONFIRM -->
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full border-2 border-gray-300 text-gray-400 flex items-center justify-center font-bold text-sm">
                            3
                        </div>
                        <span class="text-xs font-medium text-gray-400 mt-1.5 tracking-wide uppercase">Confirm</span>
                    </div>

                </div>
            </div>
        </div>

        <!-- ── Main Content ─────────────────────────────────────────────────── -->
        <main class="flex-1 max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row gap-6 items-start">

                <!-- ── Left: Form ──────────────────────────────────────────── -->
                <div class="flex-1 min-w-0 bg-white rounded-2xl border border-gray-200 p-8">
                    <h1 class="text-2xl font-bold text-gray-900 mb-1">Confirm Service &amp; Details</h1>
                    <p class="text-sm text-gray-500 mb-7">Please provide your contact information to finalize the booking.</p>

                    <form @submit.prevent="continueToPayment" novalidate>

                        <!-- First Name + Last Name -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">First Name</label>
                                <input
                                    v-model="form.firstName"
                                    type="text"
                                    placeholder="John"
                                    :class="[
                                        'w-full px-4 py-3 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition',
                                        errors.firstName ? 'border-red-400 bg-red-50' : 'border-gray-300'
                                    ]"
                                />
                                <p v-if="errors.firstName" class="text-xs text-red-500 mt-1">{{ errors.firstName }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Last Name</label>
                                <input
                                    v-model="form.lastName"
                                    type="text"
                                    placeholder="Doe"
                                    :class="[
                                        'w-full px-4 py-3 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition',
                                        errors.lastName ? 'border-red-400 bg-red-50' : 'border-gray-300'
                                    ]"
                                />
                                <p v-if="errors.lastName" class="text-xs text-red-500 mt-1">{{ errors.lastName }}</p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="john.doe@example.com"
                                :class="[
                                    'w-full px-4 py-3 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition',
                                    errors.email ? 'border-red-400 bg-red-50' : 'border-gray-300'
                                ]"
                            />
                            <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</p>
                        </div>

                        <!-- Phone -->
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                            <div class="flex">
                                <span
                                    :class="[
                                        'inline-flex items-center px-3 border border-r-0 rounded-l-lg text-sm text-gray-500 bg-gray-50',
                                        errors.phone ? 'border-red-400' : 'border-gray-300'
                                    ]"
                                >+1</span>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    placeholder="(555) 000-0000"
                                    :class="[
                                        'flex-1 px-4 py-3 border rounded-r-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition',
                                        errors.phone ? 'border-red-400 bg-red-50' : 'border-gray-300'
                                    ]"
                                />
                            </div>
                            <p v-if="errors.phone" class="text-xs text-red-500 mt-1">{{ errors.phone }}</p>
                        </div>

                        <!-- Special Requirements -->
                        <div class="mb-8">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Special Requirements
                                <span class="text-gray-400 font-normal">(Optional)</span>
                            </label>
                            <textarea
                                v-model="form.requirements"
                                rows="4"
                                placeholder="Any specific requests or needs..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-y"
                            />
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-8 py-4 rounded-xl transition"
                        >
                            Continue to Payment
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </button>

                    </form>
                </div>

                <!-- ── Right: Summary Card ──────────────────────────────────── -->
                <div class="w-full lg:w-80 xl:w-96 shrink-0">
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

                        <!-- Service image -->
                        <div class="h-48 bg-gray-200 overflow-hidden">
                            <img
                                v-if="service.image"
                                :src="service.image"
                                :alt="service.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full bg-gradient-to-br from-gray-300 to-gray-400" />
                        </div>

                        <div class="p-6">

                            <!-- Category badge -->
                            <div class="mb-3">
                                <span class="inline-block bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">
                                    {{ offering?.category_tag ?? service.category?.name ?? 'Service' }}
                                </span>
                            </div>

                            <!-- Service name -->
                            <h2 class="text-xl font-bold text-gray-900 leading-snug mb-2">
                                {{ offering?.name ?? service.name }}
                            </h2>

                            <!-- Duration -->
                            <div v-if="offering?.duration_minutes" class="flex items-center gap-1.5 text-sm text-gray-500 mb-5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ offering.duration_minutes }} Minutes
                            </div>

                            <!-- Divider -->
                            <div class="border-t border-gray-100 mb-4" />

                            <!-- Date & Time -->
                            <div v-if="formattedDate || timeRange" class="flex items-start gap-3 mb-3">
                                <svg class="w-5 h-5 text-gray-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ formattedDate }}</div>
                                    <div class="text-sm text-gray-500">{{ timeRange }}</div>
                                </div>
                            </div>

                            <!-- Consultant / Staff -->
                            <div v-if="offering?.staff_level" class="flex items-start gap-3 mb-5">
                                <svg class="w-5 h-5 text-gray-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <div>
                                    <div class="text-sm font-bold text-gray-900">Consultant</div>
                                    <div class="text-sm text-gray-500">{{ offering.staff_level }}</div>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="border-t border-gray-100 mb-4" />

                            <!-- Fees breakdown -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center justify-between text-sm text-gray-600">
                                    <span>Session Fee</span>
                                    <span>{{ formatPrice(sessionFee) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm text-gray-600">
                                    <span>Service Fee</span>
                                    <span>{{ formatPrice(SERVICE_FEE) }}</span>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="flex items-center justify-between mb-5">
                                <span class="text-base font-bold text-gray-900">Total</span>
                                <span class="text-2xl font-bold text-blue-600">{{ formatPrice(total) }}</span>
                            </div>

                            <!-- Info note -->
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <p class="text-xs text-blue-700 leading-relaxed">
                                    You won't be charged yet. Final confirmation happens after payment details are entered in the next step.
                                </p>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </main>

        <!-- ── Footer ──────────────────────────────────────────────────────── -->
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-400">© {{ new Date().getFullYear() }} ReserveNow Booking Systems. All rights reserved.</p>
                <div class="flex items-center gap-5">
                    <a href="#" class="text-xs text-gray-500 hover:text-gray-700 transition">Privacy Policy</a>
                    <a href="#" class="text-xs text-gray-500 hover:text-gray-700 transition">Terms of Service</a>
                    <a href="#" class="text-xs text-gray-500 hover:text-gray-700 transition">Cookie Settings</a>
                </div>
            </div>
        </footer>

    </div>
</template>
