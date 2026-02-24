<script setup>
import { ref, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import VendorLayout from '@/Layouts/VendorLayout.vue';

const props = defineProps({
    categories: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const searchQuery = ref(props.filters.search || '');
const sortBy = ref(props.filters.sort || 'newest');

// Modal state
const showModal = ref(false);
const modalMode = ref('create'); // 'create' or 'edit'
const editingCategory = ref(null);
const isSubmitting = ref(false);

// Form data
const formData = ref({
    name: '',
    icon: '',
});

// Search handler
function handleSearch() {
    router.get(route('vendor.categories.index'), {
        search: searchQuery.value,
        sort: sortBy.value,
    }, { preserveState: true, replace: true });
}

// Sort handler
function setSort(sort) {
    sortBy.value = sort;
    router.get(route('vendor.categories.index'), {
        search: searchQuery.value,
        sort: sort,
    }, { preserveState: true, replace: true });
}

// Open create modal
function openCreateModal() {
    modalMode.value = 'create';
    editingCategory.value = null;
    formData.value = { name: '', icon: '' };
    showModal.value = true;
}

// Open edit modal
function openEditModal(category) {
    modalMode.value = 'edit';
    editingCategory.value = category;
    formData.value = { name: category.name, icon: category.icon || '' };
    showModal.value = true;
}

// Close modal
function closeModal() {
    showModal.value = false;
    editingCategory.value = null;
    formData.value = { name: '', icon: '' };
}

// Submit form
function submitForm() {
    isSubmitting.value = true;
    
    if (modalMode.value === 'create') {
        router.post(route('vendor.categories.store'), formData.value, {
            onSuccess: () => {
                closeModal();
                isSubmitting.value = false;
            },
            onError: () => {
                isSubmitting.value = false;
            },
        });
    } else {
        router.put(route('vendor.categories.update', editingCategory.value.id), formData.value, {
            onSuccess: () => {
                closeModal();
                isSubmitting.value = false;
            },
            onError: () => {
                isSubmitting.value = false;
            },
        });
    }
}

// Delete category
function deleteCategory(category) {
    if (!confirm(`Are you sure you want to delete "${category.name}"?`)) {
        return;
    }
    
    if (category.services_count > 0) {
        alert('Cannot delete category with associated services. Please remove services from this category first.');
        return;
    }
    
    router.delete(route('vendor.categories.destroy', category.id));
}

// Common emojis for categories
const commonEmojis = ['🔧', '⚡', '❄️', '🧹', '🌿', '🎨', '🪚', '📦', '🚗', '🐕', '💼', '🎵', '📚', '🏠', '🧑‍⚕️', '👶'];
</script>

<template>
    <Head title="Categories" />

    <VendorLayout activePage="categories">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
                <p class="text-sm text-gray-500 mt-1">Manage service categories for your business</p>
            </div>
            <button
                @click="openCreateModal"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-medium text-sm flex items-center gap-2 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Category
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Total Categories</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.total_categories }}</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Total Services</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.total_services }}</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-sm text-gray-500 mb-1">Active Categories</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.categories_with_services }}</div>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
            <div class="flex items-center gap-4">
                <!-- Search -->
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search categories by name..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @keyup.enter="handleSearch"
                    />
                </div>

                <!-- Sort -->
                <select
                    v-model="sortBy"
                    @change="setSort(sortBy)"
                    class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
                    <option value="services_desc">Most Services</option>
                </select>
            </div>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div
                v-for="category in categories.data"
                :key="category.id"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow"
            >
                <!-- Category Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center text-2xl">
                            {{ category.icon || '📁' }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-sm">{{ category.name }}</h3>
                            <p class="text-xs text-gray-400">{{ category.slug }}</p>
                        </div>
                    </div>
                    <!-- Actions Dropdown -->
                    <div class="relative group">
                        <button class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                            </svg>
                        </button>
                        <div class="absolute right-0 top-full mt-1 w-32 bg-white rounded-xl shadow-lg border border-gray-100 py-1 hidden group-hover:block z-10">
                            <button
                                @click="openEditModal(category)"
                                class="flex items-center gap-2 w-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                            <button
                                @click="deleteCategory(category)"
                                class="flex items-center gap-2 w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Services Count -->
                <div class="flex items-center justify-between pt-3 border-t border-gray-50">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span class="text-sm text-gray-500">
                            {{ category.services_count }} {{ category.services_count === 1 ? 'service' : 'services' }}
                        </span>
                    </div>
                    <span class="text-xs text-gray-400">
                        {{ category.created_at }}
                    </span>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="categories.data.length === 0" class="col-span-full">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">No categories found</h3>
                    <p class="text-sm text-gray-500 mb-4">Start by adding your first category.</p>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Your First Category
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="categories.last_page > 1" class="flex items-center justify-between mt-6">
            <div class="text-sm text-gray-500">
                Showing {{ categories.from }} to {{ categories.to }} of {{ categories.total }} categories
            </div>
            <div class="flex items-center gap-2">
                <Link
                    v-if="categories.prev_page_url"
                    :href="categories.prev_page_url"
                    class="px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    Previous
                </Link>
                
                <template v-for="page in categories.last_page" :key="page">
                    <Link
                        v-if="page >= categories.current_page - 2 && page <= categories.current_page + 2"
                        :href="`?page=${page}&search=${filters.search || ''}&sort=${filters.sort || ''}`"
                        :class="[
                            'w-9 h-9 rounded-lg text-sm font-medium transition-colors flex items-center justify-center',
                            page === categories.current_page
                                ? 'bg-blue-600 text-white'
                                : 'text-gray-600 hover:bg-gray-100'
                        ]"
                    >
                        {{ page }}
                    </Link>
                </template>

                <Link
                    v-if="categories.next_page_url"
                    :href="categories.next_page_url"
                    class="px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    Next
                </Link>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
                <div class="fixed inset-0 bg-black/50 transition-opacity" @click="closeModal"></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ modalMode === 'create' ? 'Create Category' : 'Edit Category' }}
                            </h3>
                            <button
                                @click="closeModal"
                                class="p-1 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitForm">
                            <!-- Name -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Category Name</label>
                                <input
                                    v-model="formData.name"
                                    type="text"
                                    placeholder="e.g., Plumbing"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required
                                />
                            </div>

                            <!-- Icon -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Icon (Emoji)</label>
                                <div class="relative">
                                    <input
                                        v-model="formData.icon"
                                        type="text"
                                        placeholder="🔧"
                                        maxlength="10"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                    <div class="absolute right-2 top-1/2 -translate-y-1/2 text-2xl">
                                        {{ formData.icon || '📁' }}
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 mt-1.5">Common emojis:</p>
                                <div class="flex flex-wrap gap-1 mt-2">
                                    <button
                                        v-for="emoji in commonEmojis"
                                        :key="emoji"
                                        type="button"
                                        @click="formData.icon = emoji"
                                        :class="[
                                            'w-8 h-8 rounded-lg flex items-center justify-center text-lg hover:bg-gray-100 transition-colors',
                                            formData.icon === emoji ? 'bg-blue-100 ring-2 ring-blue-500' : ''
                                        ]"
                                    >
                                        {{ emoji }}
                                    </button>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="flex-1 px-4 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium text-sm transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium text-sm transition-colors disabled:opacity-50"
                                >
                                    {{ isSubmitting ? 'Saving...' : (modalMode === 'create' ? 'Create Category' : 'Save Changes') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </VendorLayout>
</template>

