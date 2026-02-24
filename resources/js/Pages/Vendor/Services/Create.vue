<script setup>
import { ref, computed } from 'vue';
import { router, Link, Head, usePage } from '@inertiajs/vue3';
import VendorLayout from '@/Layouts/VendorLayout.vue';

const props = defineProps({
    categories: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const errors = computed(() => page.props.errors || {});

// Form state
const form = ref({
    name: '',
    category_id: '',
    description: '',
    price_range: 1,
    badge: '',
    badge_color: 'blue',
    address: '',
    city: '',
    state: '',
    opening_hours: '',
    is_available: true,
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

function handleSubmit() {
    isSubmitting.value = true;
    router.post(route('vendor.services.store'), form.value, {
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}
</script>

<template>
    <Head title="Add Service" />

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
                    <h1 class="text-2xl font-bold text-gray-900">Add New Service</h1>
                    <p class="text-sm text-gray-500 mt-1">Create a new service to offer to customers</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="col-span-2 space-y-6">
                    <!-- Basic Info Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Basic Information</h2>
                        
                        <div class="space-y-4">
                            <!-- Service Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Service Name *</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="e.g., Precision Plumbing & Drain"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-300': errors.name }"
                                />
                                <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name }}</p>
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select
                                    v-model="form.category_id"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-300': errors.category_id }"
                                >
                                    <option value="" disabled>Select a category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="errors.category_id" class="mt-1 text-xs text-red-500">{{ errors.category_id }}</p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    placeholder="Describe your service..."
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                ></textarea>
                            </div>

                            <!-- Price Range -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price Range</label>
                                <select
                                    v-model="form.price_range"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                            <!-- Address -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <input
                                    v-model="form.address"
                                    type="text"
                                    placeholder="e.g., 45 Water St, Suite 101, New York, NY 10004"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <!-- City & State -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                    <input
                                        v-model="form.city"
                                        type="text"
                                        placeholder="e.g., New York"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                    <input
                                        v-model="form.state"
                                        type="text"
                                        placeholder="e.g., NY"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                            </div>

                            <!-- Opening Hours -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Opening Hours</label>
                                <input
                                    v-model="form.opening_hours"
                                    type="text"
                                    placeholder="e.g., Mon - Fri: 7:00 AM - 8:00 PM | Sat: 8:00 AM - 5:00 PM"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Status</h2>
                        
                        <div class="space-y-4">
                            <!-- Badge -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Badge</label>
                                <select
                                    v-model="form.badge"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option v-for="opt in badgeOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Badge Color -->
                            <div v-if="form.badge">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Badge Color</label>
                                <select
                                    v-model="form.badge_color"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option v-for="opt in badgeColorOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Availability Toggle -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-700">Available</div>
                                    <div class="text-xs text-gray-500">Customers can book this service</div>
                                </div>
                                <button
                                    type="button"
                                    @click="form.is_available = !form.is_available"
                                    :class="[
                                        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2',
                                        form.is_available ? 'bg-blue-600' : 'bg-gray-200'
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                            form.is_available ? 'translate-x-5' : 'translate-x-0'
                                        ]"
                                    />
                                </button>
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
                                <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ isSubmitting ? 'Creating...' : 'Create Service' }}
                            </button>
                            
                            <Link
                                :href="route('vendor.services.index')"
                                class="w-full bg-gray-50 hover:bg-gray-100 text-gray-700 px-4 py-2.5 rounded-xl font-medium text-sm flex items-center justify-center gap-2 transition-colors border border-gray-200"
                            >
                                Cancel
                            </Link>
                        </div>
                        
                        <p class="text-xs text-gray-500 text-center mt-4">
                            After creating, you can add service offerings.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </VendorLayout>
</template>

