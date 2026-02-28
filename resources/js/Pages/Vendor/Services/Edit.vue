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
                    :href="route('vendor.services.show', service.id)"
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
                    <p class="text-sm text-gray-500 mt-1">Update basic information for {{ service.name }}</p>
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

        <!-- Service Details Form -->
        <form @submit.prevent="updateService" class="mb-6">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Main Form (2 cols) -->
                <div class="xl:col-span-2 space-y-6">
                    <!-- Basic Info Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-900">Basic Information</h2>
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-4 py-2 rounded-xl font-medium text-sm flex items-center justify-center gap-2 transition-colors lg:hidden"
                            >
                                {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Service Name *</label>
                                <input
                                    v-model="serviceForm.name"
                                    type="text"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea
                                    v-model="serviceForm.description"
                                    rows="3"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Location Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Location</h2>
                        
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
                        </div>
                    </div>
                </div>

                <!-- Sidebar (1 col) -->
                <div class="space-y-6">
                    <!-- Badge Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Badge Overlay</h2>
                        
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

                    <!-- Global Save Action -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 hidden lg:block sticky top-6">
                        <div class="space-y-3">
                            <p class="text-sm text-gray-500 mb-2">Publish your latest changes to the main service listing.</p>
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-4 py-3 rounded-xl font-bold transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5"
                            >
                                {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </VendorLayout>
</template>

