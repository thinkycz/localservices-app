<script setup>
import { ref, computed } from 'vue';
import { router, Link, Head, usePage } from '@inertiajs/vue3';
import VendorLayout from '@/Layouts/VendorLayout.vue';

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const errors = computed(() => page.props.errors || {});

// Offering form state
const showOfferingModal = ref(false);
const editingOffering = ref(null);
const offeringForm = ref({
    name: '',
    description: '',
    price: '',
    duration_minutes: '',
    is_popular: false,
    category_tag: '',
    staff_level: '',
});

// Badge options
const badgeOptions = [
    { value: '', label: 'No Badge' },
    { value: 'EMERGENCY SERVICE', label: 'Emergency Service' },
    { value: 'CERTIFIED PRO', label: 'Certified Pro' },
    { value: 'ECO-FRIENDLY', label: 'Eco-Friendly' },
    { value: '24/7 AVAILABLE', label: '24/7 Available' },
    { value: 'BEST RATED', label: 'Best Rated' },
];

const badgeColorOptions = [
    { value: 'blue', label: 'Blue' },
    { value: 'gray', label: 'Gray' },
    { value: 'green', label: 'Green' },
];

const priceRangeOptions = [
    { value: 1, label: '$ (Budget)' },
    { value: 2, label: '$$ (Moderate)' },
    { value: 3, label: '$$$ (Premium)' },
    { value: 4, label: '$$$$ (Luxury)' },
];

const categoryTagOptions = [
    { value: '', label: 'No Tag' },
    { value: 'Drain', label: 'Drain' },
    { value: 'Repair', label: 'Repair' },
    { value: 'Install', label: 'Install' },
    { value: 'Emergency', label: 'Emergency' },
    { value: 'Inspection', label: 'Inspection' },
    { value: 'Maintenance', label: 'Maintenance' },
    { value: 'Upgrade', label: 'Upgrade' },
    { value: 'Smart', label: 'Smart' },
    { value: 'EV', label: 'EV' },
    { value: 'Solar', label: 'Solar' },
    { value: 'Home', label: 'Home' },
    { value: 'Office', label: 'Office' },
    { value: 'Deep', label: 'Deep' },
    { value: 'Specialty', label: 'Specialty' },
];

const staffLevelOptions = [
    { value: '', label: 'Any Staff' },
    { value: 'Plumber', label: 'Plumber' },
    { value: 'Licensed Plumber', label: 'Licensed Plumber' },
    { value: 'Senior Plumber', label: 'Senior Plumber' },
    { value: 'Master Plumber', label: 'Master Plumber' },
    { value: 'Certified Plumber', label: 'Certified Plumber' },
    { value: 'Electrician', label: 'Electrician' },
    { value: 'Licensed Electrician', label: 'Licensed Electrician' },
    { value: 'Master Electrician', label: 'Master Electrician' },
    { value: 'Certified Electrician', label: 'Certified Electrician' },
    { value: 'HVAC Technician', label: 'HVAC Technician' },
    { value: 'Senior HVAC Tech', label: 'Senior HVAC Tech' },
    { value: 'Master HVAC Tech', label: 'Master HVAC Tech' },
    { value: 'Cleaning Pro', label: 'Cleaning Pro' },
    { value: 'Senior Cleaner', label: 'Senior Cleaner' },
];

// Toggle availability
function toggleAvailability() {
    router.post(route('vendor.services.toggle', props.service.id), {}, {
        onSuccess: () => {
            // Inertia will automatically refresh props
        },
    });
}

// Offering modal functions
function openAddOffering() {
    editingOffering.value = null;
    offeringForm.value = {
        name: '',
        description: '',
        price: '',
        duration_minutes: '',
        is_popular: false,
        category_tag: '',
        staff_level: '',
    };
    showOfferingModal.value = true;
}

function openEditOffering(offering) {
    editingOffering.value = offering;
    offeringForm.value = {
        name: offering.name,
        description: offering.description || '',
        price: offering.price,
        duration_minutes: offering.duration_minutes,
        is_popular: offering.is_popular,
        category_tag: offering.category_tag || '',
        staff_level: offering.staff_level || '',
    };
    showOfferingModal.value = true;
}

function closeOfferingModal() {
    showOfferingModal.value = false;
    editingOffering.value = null;
}

function saveOffering() {
    const data = {
        ...offeringForm.value,
        price: parseFloat(offeringForm.value.price),
        duration_minutes: parseInt(offeringForm.value.duration_minutes),
    };

    if (editingOffering.value) {
        // Update existing
        router.put(
            route('vendor.services.offerings.update', { 
                serviceId: props.service.id, 
                offeringId: editingOffering.value.id 
            }),
            data,
            { onSuccess: closeOfferingModal }
        );
    } else {
        // Create new
        router.post(
            route('vendor.services.offerings.store', { serviceId: props.service.id }),
            data,
            { onSuccess: closeOfferingModal }
        );
    }
}

function deleteOffering(offeringId) {
    if (!confirm('Are you sure you want to delete this offering?')) return;
    
    router.delete(
        route('vendor.services.offerings.destroy', { 
            serviceId: props.service.id, 
            offeringId: offeringId 
        })
    );
}

// Format helpers
function formatPrice(price) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price || 0);
}

function getBadgeClasses(color) {
    const colors = {
        blue: 'bg-blue-100 text-blue-700',
        gray: 'bg-gray-100 text-gray-700',
        green: 'bg-green-100 text-green-700',
    };
    return colors[color] || 'bg-gray-100 text-gray-700';
}

// Business Hours
const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

const businessHours = ref(
    DAY_NAMES.map((name, index) => {
        const existing = props.service.business_hours?.find(h => h.day_of_week === index);
        return {
            day_of_week: index,
            label: name,
            enabled: !!existing,
            time_from: existing?.time_from || '09:00',
            time_to: existing?.time_to || '17:00',
        };
    })
);

const isSavingHours = ref(false);

function saveBusinessHours() {
    isSavingHours.value = true;
    const hours = businessHours.value
        .filter(h => h.enabled)
        .map(h => ({
            day_of_week: h.day_of_week,
            time_from: h.time_from,
            time_to: h.time_to,
        }));

    router.post(route('vendor.services.business-hours.store', { serviceId: props.service.id }), { hours }, {
        onFinish: () => {
            isSavingHours.value = false;
        },
    });
}
</script>

<template>
    <Head title="Manage Service" />

    <VendorLayout activePage="services">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <Link
                    :href="route('vendor.services.index')"
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">{{ service.name }}</h1>
                        <span
                            v-if="service.badge"
                            :class="[getBadgeClasses(service.badge_color), 'text-xs font-medium px-2.5 py-1 rounded-full']"
                        >
                            {{ service.badge }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Manage offerings and schedule</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <Link
                    :href="route('vendor.services.edit', service.id)"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-xl font-medium text-sm transition-colors"
                >
                    Edit Details
                </Link>

                <!-- Availability Toggle -->
                <button
                    @click="toggleAvailability"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                        service.is_available
                            ? 'bg-green-50 text-green-600 hover:bg-green-100'
                            : 'bg-gray-50 text-gray-500 hover:bg-gray-100'
                    ]"
                >
                    {{ service.is_available ? '✓ Active' : '○ Inactive' }}
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="text-sm text-gray-500 mb-1">Total Bookings</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.total_bookings }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="text-sm text-gray-500 mb-1">Completed</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.completed_bookings }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="text-sm text-gray-500 mb-1">Cancelled</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.cancelled_bookings }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="text-sm text-gray-500 mb-1">Total Revenue</div>
                <div class="text-2xl font-bold text-gray-900">{{ formatPrice(stats.total_revenue) }}</div>
            </div>
        </div>

        <!-- Service Offerings Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Service Offerings</h2>
                    <p class="text-sm text-gray-500">Manage what individual variations customers can book</p>
                </div>
                <button
                    @click="openAddOffering"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-medium text-sm flex items-center justify-center gap-2 transition-colors shrink-0"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Offering
                </button>
            </div>

            <!-- Offerings Grid -->
            <div v-if="service.offerings?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="offering in service.offerings"
                    :key="offering.id"
                    class="flex flex-col bg-white border border-gray-200 rounded-2xl p-4 hover:border-blue-300 transition-colors group"
                >
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-bold text-gray-900 text-[15px] leading-tight">{{ offering.name }}</h3>
                            <span v-if="offering.is_popular" class="text-[10px] text-blue-600 font-bold uppercase tracking-wide bg-blue-100 px-1.5 py-0.5 rounded shrink-0">Popular</span>
                        </div>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button
                                @click="openEditOffering(offering)"
                                class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button
                                @click="deleteOffering(offering.id)"
                                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-3 flex-wrap">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ offering.duration_minutes }} mins
                        </span>
                        <span v-if="offering.category_tag" class="text-gray-300">•</span>
                        <span v-if="offering.category_tag">{{ offering.category_tag }}</span>
                        <span v-if="offering.staff_level" class="text-gray-300">•</span>
                        <span v-if="offering.staff_level">{{ offering.staff_level }}</span>
                    </div>

                    <div class="mt-auto flex items-end justify-between border-t border-gray-100 pt-3">
                        <span class="text-gray-500 text-xs">Price</span>
                        <span class="font-bold text-gray-900 text-base">{{ formatPrice(offering.price) }}</span>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm border border-gray-100">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="text-[15px] font-semibold text-gray-900 mb-1">No offerings yet</h3>
                <p class="text-sm text-gray-500 mb-4 max-w-sm mx-auto">Add offerings to let customers book specific jobs from this service category.</p>
                <button
                    @click="openAddOffering"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Your First Offering
                </button>
            </div>
        </div>

        <!-- Business Hours Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Business Hours</h2>
                    <p class="text-sm text-gray-500">Master schedule when this service can be booked</p>
                </div>
                <button
                    @click="saveBusinessHours"
                    :disabled="isSavingHours"
                    class="bg-blue-600 hover:bg-blue-700 w-full sm:w-auto disabled:bg-blue-400 text-white px-4 py-2 rounded-xl font-medium text-sm flex items-center justify-center gap-2 transition-colors shrink-0"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ isSavingHours ? 'Saving...' : 'Save Hours' }}
                </button>
            </div>

            <!-- Business Hours Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                <div
                    v-for="day in businessHours"
                    :key="day.day_of_week"
                    class="flex flex-col p-3 rounded-xl border transition-colors"
                    :class="day.enabled ? 'bg-blue-50/30 border-blue-200' : 'bg-gray-50 border-gray-200'"
                >
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[15px] font-bold" :class="day.enabled ? 'text-gray-900' : 'text-gray-400'">{{ day.label }}</span>
                        <!-- Toggle -->
                        <button
                            @click="day.enabled = !day.enabled"
                            :class="[
                                'relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                                day.enabled ? 'bg-blue-600' : 'bg-gray-300'
                            ]"
                        >
                            <span
                                :class="[
                                    'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    day.enabled ? 'translate-x-4' : 'translate-x-0'
                                ]"
                            />
                        </button>
                    </div>

                    <!-- Time Inputs -->
                    <div v-if="day.enabled" class="flex flex-col gap-2 mt-auto">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 w-8 font-medium">From</span>
                            <input
                                v-model="day.time_from"
                                type="time"
                                class="flex-1 px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 w-8 font-medium">To</span>
                            <input
                                v-model="day.time_to"
                                type="time"
                                class="flex-1 px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm"
                            />
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-400 italic mt-auto flex h-[76px] items-center justify-center bg-gray-100/50 rounded-lg">
                        Closed
                    </div>
                </div>
            </div>
        </div>

        <!-- Offering Modal -->
        <div v-if="showOfferingModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50" @click="closeOfferingModal"></div>
                
                <!-- Modal -->
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        {{ editingOffering ? 'Edit Offering' : 'Add Offering' }}
                    </h3>
                    
                    <form @submit.prevent="saveOffering" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input
                                v-model="offeringForm.name"
                                type="text"
                                placeholder="e.g., Basic Drain Cleaning"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea
                                v-model="offeringForm.description"
                                rows="2"
                                placeholder="Describe what's included..."
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                                <input
                                    v-model="offeringForm.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Duration (mins) *</label>
                                <input
                                    v-model="offeringForm.duration_minutes"
                                    type="number"
                                    min="1"
                                    placeholder="60"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category Tag</label>
                                <select
                                    v-model="offeringForm.category_tag"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option v-for="opt in categoryTagOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Staff Level</label>
                                <select
                                    v-model="offeringForm.staff_level"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option v-for="opt in staffLevelOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                v-model="offeringForm.is_popular"
                                type="checkbox"
                                id="is_popular"
                                class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                            />
                            <label for="is_popular" class="text-sm text-gray-700">Mark as Popular</label>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button
                                type="button"
                                @click="closeOfferingModal"
                                class="flex-1 px-4 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-xl font-medium text-sm transition-colors border border-gray-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium text-sm transition-colors"
                            >
                                {{ editingOffering ? 'Update' : 'Add' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </VendorLayout>
</template>

