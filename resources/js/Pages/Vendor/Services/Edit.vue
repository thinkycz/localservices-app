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

// Form state for service
const serviceForm = ref({
    name: props.service.name,
    category_id: props.service.category_id,
    description: props.service.description || '',
    price_range: props.service.price_range || 1,
    badge: props.service.badge || '',
    badge_color: props.service.badge_color || 'blue',
    address: props.service.address || '',
    city: props.service.city || '',
    state: props.service.state || '',
    opening_hours: props.service.opening_hours || '',
    is_available: props.service.is_available,
});

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

const isSubmitting = ref(false);

// Service update
function updateService() {
    isSubmitting.value = true;
    router.put(route('vendor.services.update', props.service.id), serviceForm.value, {
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

// Toggle availability
function toggleAvailability() {
    router.post(route('vendor.services.toggle', props.service.id), {}, {
        onSuccess: () => {
            serviceForm.value.is_available = !serviceForm.value.is_available;
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
</script>

<template>
    <Head title="Edit Service" />

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
                    <p class="text-sm text-gray-500 mt-1">Edit your service and offerings</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Availability Toggle -->
                <button
                    @click="toggleAvailability"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-xl transition-colors',
                        serviceForm.is_available
                            ? 'bg-green-50 text-green-600 hover:bg-green-100'
                            : 'bg-gray-50 text-gray-500 hover:bg-gray-100'
                    ]"
                >
                    {{ serviceForm.is_available ? '✓ Active' : '○ Inactive' }}
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
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Service Offerings</h2>
                    <p class="text-sm text-gray-500">Manage what customers can book</p>
                </div>
                <button
                    @click="openAddOffering"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-medium text-sm flex items-center gap-2 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Offering
                </button>
            </div>

            <!-- Offerings List -->
            <div v-if="service.offerings?.length" class="space-y-3">
                <div
                    v-for="offering in service.offerings"
                    :key="offering.id"
                    class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-900 text-sm">{{ offering.name }}</span>
                                <span v-if="offering.is_popular" class="text-xs text-blue-600 font-medium">Popular</span>
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ offering.duration_minutes }} mins
                                <span v-if="offering.category_tag"> • {{ offering.category_tag }}</span>
                                <span v-if="offering.staff_level"> • {{ offering.staff_level }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <span class="font-bold text-gray-900">{{ formatPrice(offering.price) }}</span>
                        <div class="flex items-center gap-1">
                            <button
                                @click="openEditOffering(offering)"
                                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button
                                @click="deleteOffering(offering.id)"
                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">No offerings yet</h3>
                <p class="text-sm text-gray-500 mb-4">Add offerings to let customers book your services.</p>
                <button
                    @click="openAddOffering"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Your First Offering
                </button>
            </div>
        </div>

        <!-- Service Details Form -->
        <form @submit.prevent="updateService">
            <div class="grid grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="col-span-2 space-y-6">
                    <!-- Basic Info Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Basic Information</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Service Name *</label>
                                <input
                                    v-model="serviceForm.name"
                                    type="text"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select
                                    v-model="serviceForm.category_id"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea
                                    v-model="serviceForm.description"
                                    rows="4"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price Range</label>
                                <select
                                    v-model="serviceForm.price_range"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option v-for="opt in priceRangeOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Location Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Location & Hours</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <input
                                    v-model="serviceForm.address"
                                    type="text"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                    <input
                                        v-model="serviceForm.city"
                                        type="text"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                    <input
                                        v-model="serviceForm.state"
                                        type="text"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Opening Hours</label>
                                <input
                                    v-model="serviceForm.opening_hours"
                                    type="text"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Badge Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Badge</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Badge</label>
                                <select
                                    v-model="serviceForm.badge"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option v-for="opt in badgeOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="serviceForm.badge">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Badge Color</label>
                                <select
                                    v-model="serviceForm.badge_color"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option v-for="opt in badgeColorOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="space-y-3">
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-4 py-2.5 rounded-xl font-medium text-sm flex items-center justify-center gap-2 transition-colors"
                            >
                                {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

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

