<script setup>
import { ref, computed } from 'vue';
import { router, Link, Head, usePage } from '@inertiajs/vue3';
import VendorLayout from '@/Layouts/VendorLayout.vue';

const props = defineProps({
    service: { type: Object, required: true },
    categories: { type: Array, required: true },
    stats: { type: Object, required: true },
});

const page = usePage();
const errors = computed(() => page.props.errors || {});

const showOfferingModal = ref(false);
const editingOffering = ref(null);
const offeringForm = ref({
    name: '', description: '', price: '', duration_minutes: '', is_popular: false, category_tag: '', staff_level: '',
});


function toggleAvailability() {
    router.post(route('vendor.services.toggle', props.service.id));
}

function openAddOffering() {
    editingOffering.value = null;
    offeringForm.value = { name: '', description: '', price: '', duration_minutes: '', is_popular: false, category_tag: '', staff_level: '' };
    showOfferingModal.value = true;
}

function openEditOffering(offering) {
    editingOffering.value = offering;
    offeringForm.value = {
        name: offering.name, description: offering.description || '', price: offering.price,
        duration_minutes: offering.duration_minutes, is_popular: offering.is_popular,
        category_tag: offering.category_tag || '', staff_level: offering.staff_level || '',
    };
    showOfferingModal.value = true;
}

function closeOfferingModal() {
    showOfferingModal.value = false;
    editingOffering.value = null;
}

function saveOffering() {
    const data = { ...offeringForm.value, price: parseFloat(offeringForm.value.price), duration_minutes: parseInt(offeringForm.value.duration_minutes) };
    if (editingOffering.value) {
        router.put(route('vendor.services.offerings.update', { serviceId: props.service.id, offeringId: editingOffering.value.id }), data, { onSuccess: closeOfferingModal });
    } else {
        router.post(route('vendor.services.offerings.store', { serviceId: props.service.id }), data, { onSuccess: closeOfferingModal });
    }
}

function deleteOffering(offeringId) {
    if (!confirm('Are you sure you want to delete this offering?')) return;
    router.delete(route('vendor.services.offerings.destroy', { serviceId: props.service.id, offeringId }));
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(price || 0);
}

function getBadgeClasses(color) {
    const c = { blue: 'bg-blue-50 text-blue-700 ring-blue-700/10', gray: 'bg-gray-50 text-gray-700 ring-gray-700/10', green: 'bg-green-50 text-green-700 ring-green-700/10' };
    return c[color] || c.gray;
}
</script>

<template>
    <Head title="Manage Service" />

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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3">
                            <h1 class="text-xl font-bold text-gray-900 truncate">{{ service.name }}</h1>
                            <span v-if="service.badge" :class="[getBadgeClasses(service.badge_color), 'text-xs font-medium px-2.5 py-1 rounded-full ring-1 ring-inset']">{{ service.badge }}</span>
                        </div>
                        <p class="text-sm text-gray-400 mt-0.5">{{ service.category?.name || 'Uncategorized' }} · {{ service.offerings?.length || 0 }} offerings</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button
                            @click="toggleAvailability"
                            :class="['px-4 py-2.5 text-sm font-medium rounded-xl transition-colors', service.is_available ? 'bg-green-50 text-green-600 hover:bg-green-100 ring-1 ring-inset ring-green-600/20' : 'bg-gray-50 text-gray-500 hover:bg-gray-100 ring-1 ring-inset ring-gray-500/10']"
                        >{{ service.is_available ? '✓ Active' : '○ Inactive' }}</button>
                        <Link
                            :href="route('vendor.services.edit', service.id)"
                            class="px-4 py-2.5 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-200 rounded-xl text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors"
                        >Edit Details</Link>
                    </div>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">Total Bookings</div>
                    <div class="text-2xl font-bold text-gray-900">{{ stats.total_bookings }}</div>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">Completed</div>
                    <div class="text-2xl font-bold text-emerald-600">{{ stats.completed_bookings }}</div>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">Cancelled</div>
                    <div class="text-2xl font-bold text-red-600">{{ stats.cancelled_bookings }}</div>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-xs text-gray-500 mb-0.5">Total Revenue</div>
                    <div class="text-2xl font-bold text-gray-900">{{ formatPrice(stats.total_revenue) }}</div>
                </div>
            </div>

            <!-- Offerings Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Service Offerings</h2>
                    <button @click="openAddOffering" class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Offering
                    </button>
                </div>

                <div v-if="service.offerings?.length">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Offering</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Staff Level</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="offering in service.offerings"
                                :key="offering.id"
                                class="group hover:bg-blue-50/30 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-900 text-sm">{{ offering.name }}</span>
                                        <span v-if="offering.is_popular" class="text-[10px] text-blue-600 font-bold uppercase tracking-wide bg-blue-100 px-1.5 py-0.5 rounded">Popular</span>
                                    </div>
                                    <p v-if="offering.description" class="text-xs text-gray-500 mt-0.5 max-w-xs truncate">{{ offering.description }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600">{{ offering.duration_minutes }} mins</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600">{{ offering.category_tag || '—' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600">{{ offering.staff_level || '—' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-semibold text-green-600">{{ formatPrice(offering.price) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        <button @click="openEditOffering(offering)" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button @click="deleteOffering(offering.id)" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-12 px-6">
                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 mb-1">No offerings yet</h3>
                    <p class="text-sm text-gray-500 mb-4">Add offerings to let customers book specific jobs.</p>
                    <button @click="openAddOffering" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Your First Offering
                    </button>
                </div>
            </div>
        </div>

        <!-- Offering Modal -->
        <teleport to="body">
            <transition
                enter-active-class="transition-opacity duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0"
            >
                <div v-if="showOfferingModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="closeOfferingModal">
                    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
                        <!-- Modal Header -->
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white">{{ editingOffering ? 'Edit Offering' : 'New Offering' }}</h3>
                                    <p class="text-xs text-blue-100">{{ editingOffering ? 'Update the details below' : 'Add a new service offering' }}</p>
                                </div>
                            </div>
                            <button @click="closeOfferingModal" class="text-white/70 hover:text-white transition-colors p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <form @submit.prevent="saveOffering" class="p-6 space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Offering Name <span class="text-red-400">*</span></label>
                                <input v-model="offeringForm.name" type="text" placeholder="e.g., Basic Drain Cleaning" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm focus:bg-white transition-colors" required />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
                                <textarea v-model="offeringForm.description" rows="2" placeholder="Describe what's included in this offering..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm resize-none focus:bg-white transition-colors"></textarea>
                            </div>

                            <!-- Price & Duration -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Price <span class="text-red-400">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">$</span>
                                        <input v-model="offeringForm.price" type="number" step="0.01" min="0" placeholder="0.00" class="w-full pl-8 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm focus:bg-white transition-colors" required />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Duration <span class="text-red-400">*</span></label>
                                    <div class="relative">
                                        <input v-model="offeringForm.duration_minutes" type="number" min="1" placeholder="60" class="w-full pl-4 pr-14 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm focus:bg-white transition-colors" required />
                                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-medium">mins</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Category Tag</label>
                                    <input v-model="offeringForm.category_tag" type="text" placeholder="e.g., Repair" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm focus:bg-white transition-colors" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Staff Level</label>
                                    <input v-model="offeringForm.staff_level" type="text" placeholder="e.g., Senior" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm focus:bg-white transition-colors" />
                                </div>
                            </div>

                            <!-- Popular Toggle -->
                            <div class="flex items-center justify-between p-3 bg-amber-50/50 border border-amber-100 rounded-xl">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-800">Mark as Popular</div>
                                        <div class="text-xs text-gray-500">Highlight this offering for customers</div>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="offeringForm.is_popular = !offeringForm.is_popular"
                                    :class="['relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out', offeringForm.is_popular ? 'bg-amber-500' : 'bg-gray-200']"
                                >
                                    <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', offeringForm.is_popular ? 'translate-x-5' : 'translate-x-0']" />
                                </button>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3 pt-1">
                                <button type="button" @click="closeOfferingModal" class="flex-1 border border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold py-2.5 rounded-xl transition-colors text-sm">Cancel</button>
                                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg text-sm">
                                    {{ editingOffering ? 'Save Changes' : 'Add Offering' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </transition>
        </teleport>
    </VendorLayout>
</template>
