<script setup>
import { ref, computed } from 'vue';
import { router, Link, Head, usePage } from '@inertiajs/vue3';
import VendorLayout from '@/Layouts/VendorLayout.vue';

const props = defineProps({
    service: { type: Object, required: true },
    categories: { type: Array, required: true },
});

const page = usePage();
const errors = computed(() => page.props.errors || {});

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
    is_available: props.service.is_available,
    is_online_only: props.service.is_online_only || false,
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

const businessHours = ref(
    DAY_NAMES.map((name, index) => {
        const existing = props.service.business_hours?.find(h => h.day_of_week === index);
        return {
            day_of_week: index, label: name,
            enabled: !!existing,
            time_from: existing?.time_from || '09:00',
            time_to: existing?.time_to || '17:00',
        };
    })
);

const isSubmitting = ref(false);

function updateService() {
    isSubmitting.value = true;
    const hours = businessHours.value.filter(h => h.enabled).map(h => ({ day_of_week: h.day_of_week, time_from: h.time_from, time_to: h.time_to }));
    router.put(route('vendor.services.update', props.service.id), { ...serviceForm.value, business_hours: hours }, { onFinish: () => { isSubmitting.value = false; } });
}

function toggleAvailability() {
    router.post(route('vendor.services.toggle', props.service.id), {}, {
        onSuccess: () => { serviceForm.value.is_available = !serviceForm.value.is_available; },
    });
}

function getBadgeClasses(color) {
    const c = { blue: 'bg-blue-50 text-blue-700 ring-blue-700/10', gray: 'bg-gray-50 text-gray-700 ring-gray-700/10', green: 'bg-green-50 text-green-700 ring-green-700/10' };
    return c[color] || c.gray;
}
</script>

<template>
    <Head :title="$t('Edit Service')" />

    <VendorLayout activePage="services">
        <div class="flex flex-col gap-6">

            <!-- Back link -->
            <div>
                <Link :href="route('vendor.services.show', service.id)" class="inline-flex items-center gap-2 text-sm font-medium text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to {{ service.name }}
                </Link>
            </div>

            <!-- Header Card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-5">
                    <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-md flex-shrink-0">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3">
                            <h1 class="text-xl font-bold text-gray-900 truncate">Edit {{ service.name }}</h1>
                            <span v-if="service.badge" :class="[getBadgeClasses(service.badge_color), 'text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset']">{{ service.badge }}</span>
                        </div>
                        <p class="text-sm text-gray-400 mt-0.5">{{ $t('Update service details and settings') }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button
                            @click="toggleAvailability"
                            :class="['px-4 py-2.5 text-sm font-medium rounded-xl transition-colors', serviceForm.is_available ? 'bg-green-50 text-green-600 hover:bg-green-100 ring-1 ring-inset ring-green-600/20' : 'bg-gray-50 text-gray-500 hover:bg-gray-100 ring-1 ring-inset ring-gray-500/10']"
                        >{{ serviceForm.is_available ? '✓ Active' : '○ Inactive' }}</button>
                    </div>
                </div>
            </div>

            <form @submit.prevent="updateService" class="flex flex-col gap-6">

                <!-- Basic Information -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">{{ $t('Basic Information') }}</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('Service Name *') }}</label>
                            <input v-model="serviceForm.name" type="text" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('Category *') }}</label>
                                <select v-model="serviceForm.category_id" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('Price Range') }}</label>
                                <select v-model="serviceForm.price_range" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option v-for="opt in priceRangeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('Description') }}</label>
                            <textarea v-model="serviceForm.description" rows="3" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">{{ $t('Location') }}</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-700">{{ $t('Online Only') }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $t('This service is provided remotely / online') }}</div>
                            </div>
                            <button
                                type="button"
                                @click="serviceForm.is_online_only = !serviceForm.is_online_only"
                                :class="['relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2', serviceForm.is_online_only ? 'bg-blue-600' : 'bg-gray-200']"
                            >
                                <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', serviceForm.is_online_only ? 'translate-x-5' : 'translate-x-0']" />
                            </button>
                        </div>
                        <template v-if="!serviceForm.is_online_only">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('Address') }}</label>
                                <input v-model="serviceForm.address" type="text" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('City') }}</label>
                                    <input v-model="serviceForm.city" type="text" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('State') }}</label>
                                    <input v-model="serviceForm.state" type="text" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
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

                <!-- Badge -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Badge Overlay</h2>
                    </div>
                    <div class="p-6 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Badge</label>
                            <select v-model="serviceForm.badge" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option v-for="opt in badgeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                        </div>
                        <div v-if="serviceForm.badge">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Badge Color</label>
                            <select v-model="serviceForm.badge_color" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option v-for="opt in badgeColorOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3 justify-end">
                    <Link :href="route('vendor.services.show', service.id)" class="px-5 py-2.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-semibold text-sm rounded-xl transition-colors">Cancel</Link>
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none text-white font-semibold text-sm rounded-xl transition-all duration-200 flex items-center gap-2"
                    >
                        <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </VendorLayout>
</template>
