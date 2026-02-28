<script setup>
import { ref, computed } from 'vue';
import { router, Link, Head, usePage } from '@inertiajs/vue3';
import VendorLayout from '@/Layouts/VendorLayout.vue';

const props = defineProps({
    categories: { type: Array, required: true },
});

const page = usePage();
const errors = computed(() => page.props.errors || {});

const form = ref({
    name: '', category_id: '', description: '', price_range: 1,
    badge: '', badge_color: 'blue', address: '', city: '', state: '',
    is_available: true, is_online_only: false,
});

const badgeOptions = [
    { value: '', label: 'No Badge' }, { value: 'EMERGENCY SERVICE', label: 'Emergency Service' },
    { value: 'CERTIFIED PRO', label: 'Certified Pro' }, { value: 'ECO-FRIENDLY', label: 'Eco-Friendly' },
    { value: '24/7 AVAILABLE', label: '24/7 Available' }, { value: 'BEST RATED', label: 'Best Rated' },
];

const badgeColorOptions = [
    { value: 'blue', label: 'Blue' }, { value: 'gray', label: 'Gray' }, { value: 'green', label: 'Green' },
];

const priceRangeOptions = [
    { value: 1, label: '$ (Budget)' }, { value: 2, label: '$$ (Moderate)' }, { value: 3, label: '$$$ (Premium)' }, { value: 4, label: '$$$$ (Luxury)' },
];

const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const DAY_SHORT = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const businessHours = ref(
    DAY_NAMES.map((name, index) => ({
        day_of_week: index, label: name, short: DAY_SHORT[index],
        enabled: index >= 1 && index <= 5, // Mon-Fri on by default
        time_from: '09:00', time_to: '17:00',
    }))
);

const isSubmitting = ref(false);

function handleSubmit() {
    isSubmitting.value = true;
    const hours = businessHours.value.filter(h => h.enabled).map(h => ({ day_of_week: h.day_of_week, time_from: h.time_from, time_to: h.time_to }));
    router.post(route('vendor.services.store'), { ...form.value, business_hours: hours }, { onFinish: () => { isSubmitting.value = false; } });
}
</script>

<template>
    <Head title="Add Service" />

    <VendorLayout activePage="services">
        <div class="flex flex-col gap-6">

            <!-- Back link -->
            <div>
                <Link :href="route('vendor.services.index')" class="inline-flex items-center gap-2 text-sm font-medium text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to Services
                </Link>
            </div>

            <!-- Header Card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-5">
                    <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-md flex-shrink-0">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl font-bold text-gray-900">Add New Service</h1>
                        <p class="text-sm text-gray-400 mt-0.5">Create a new service to offer to customers</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="flex flex-col gap-6">

                <!-- Basic Information -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Basic Information</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service Name *</label>
                            <input v-model="form.name" type="text" placeholder="e.g., Precision Plumbing & Drain" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" :class="{ 'border-red-300 focus:ring-red-500': errors.name }" />
                            <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select v-model="form.category_id" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" :class="{ 'border-red-300': errors.category_id }">
                                    <option value="" disabled>Select a category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                </select>
                                <p v-if="errors.category_id" class="mt-1 text-xs text-red-500">{{ errors.category_id }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price Range</label>
                                <select v-model="form.price_range" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option v-for="opt in priceRangeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="form.description" rows="3" placeholder="Describe your service..." class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Location</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-700">Online Only</div>
                                <div class="text-xs text-gray-500 mt-0.5">This service is provided remotely / online</div>
                            </div>
                            <button
                                type="button"
                                @click="form.is_online_only = !form.is_online_only"
                                :class="['relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2', form.is_online_only ? 'bg-blue-600' : 'bg-gray-200']"
                            >
                                <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', form.is_online_only ? 'translate-x-5' : 'translate-x-0']" />
                            </button>
                        </div>
                        <template v-if="!form.is_online_only">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <input v-model="form.address" type="text" placeholder="e.g., 45 Water St, Suite 101" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                    <input v-model="form.city" type="text" placeholder="e.g., New York" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                    <input v-model="form.state" type="text" placeholder="e.g., NY" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Business Hours -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Business Hours</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div
                            v-for="day in businessHours"
                            :key="day.day_of_week"
                            class="flex items-center gap-4 px-6 py-3"
                        >
                            <button
                                type="button"
                                @click="day.enabled = !day.enabled"
                                :class="['relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out', day.enabled ? 'bg-blue-600' : 'bg-gray-300']"
                            >
                                <span :class="['pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', day.enabled ? 'translate-x-4' : 'translate-x-0']" />
                            </button>
                            <span class="w-24 text-sm font-medium" :class="day.enabled ? 'text-gray-900' : 'text-gray-400'">{{ day.label }}</span>
                            <template v-if="day.enabled">
                                <input v-model="day.time_from" type="time" class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 w-32" />
                                <span class="text-xs text-gray-400">to</span>
                                <input v-model="day.time_to" type="time" class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 w-32" />
                            </template>
                            <span v-else class="text-sm text-gray-400 italic">Closed</span>
                        </div>
                    </div>
                </div>

                <!-- Badge & Status -->
                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Badge</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Badge</label>
                                <select v-model="form.badge" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option v-for="opt in badgeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                            </div>
                            <div v-if="form.badge">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Badge Color</label>
                                <select v-model="form.badge_color" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option v-for="opt in badgeColorOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Status</h2>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-700">Available</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Customers can book this service</div>
                                </div>
                                <button
                                    type="button"
                                    @click="form.is_available = !form.is_available"
                                    :class="['relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2', form.is_available ? 'bg-blue-600' : 'bg-gray-200']"
                                >
                                    <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', form.is_available ? 'translate-x-5' : 'translate-x-0']" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3 justify-end">
                    <Link :href="route('vendor.services.index')" class="px-5 py-2.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-semibold text-sm rounded-xl transition-colors">Cancel</Link>
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold text-sm rounded-xl transition-colors shadow-sm flex items-center gap-2"
                    >
                        <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isSubmitting ? 'Creating...' : 'Create Service' }}
                    </button>
                </div>
            </form>
        </div>
    </VendorLayout>
</template>
