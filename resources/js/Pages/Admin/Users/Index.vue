<template>
  <AppLayout>
    <Head title="User Management" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
          <p class="mt-1 text-sm text-gray-500">Manage users, roles, and permissions</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm font-medium text-gray-500">Total Users</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm font-medium text-gray-500">Admins</p>
            <p class="text-2xl font-bold text-indigo-600">{{ stats.admins }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm font-medium text-gray-500">Vendors</p>
            <p class="text-2xl font-bold text-green-600">{{ stats.vendors }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm font-medium text-gray-500">New This Month</p>
            <p class="text-2xl font-bold text-blue-600">{{ stats.new_this_month }}</p>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
              <input
                v-model="search"
                type="text"
                placeholder="Search by name or email..."
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                @input="debouncedSearch"
              />
            </div>
            <select
              v-model="roleFilter"
              class="block w-full md:w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @change="applyFilters"
            >
              <option value="">All Roles</option>
              <option value="admin">Admins</option>
              <option value="vendor">Vendors</option>
              <option value="customer">Customers</option>
            </select>
            <select
              v-model="statusFilter"
              class="block w-full md:w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @change="applyFilters"
            >
              <option value="">All Status</option>
              <option value="verified">Verified</option>
              <option value="unverified">Unverified</option>
            </select>
          </div>
        </div>

        <!-- Bulk Actions -->
        <div v-if="selectedUsers.length > 0" class="bg-indigo-50 rounded-lg p-4 mb-6">
          <div class="flex items-center justify-between">
            <span class="text-sm text-indigo-900">{{ selectedUsers.length }} users selected</span>
            <div class="flex gap-2">
              <select v-model="bulkAction" class="text-sm rounded-md border-gray-300">
                <option value="">Bulk Actions...</option>
                <option value="verify">Verify Email</option>
                <option value="make_admin">Make Admin</option>
                <option value="remove_admin">Remove Admin</option>
                <option value="make_vendor">Make Vendor</option>
                <option value="remove_vendor">Remove Vendor</option>
                <option value="delete">Delete</option>
              </select>
              <button
                @click="executeBulkAction"
                class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700"
              >
                Apply
              </button>
            </div>
          </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left">
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    @change="toggleAll"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users.data" :key="user.id">
                <td class="px-6 py-4">
                  <input
                    type="checkbox"
                    :value="user.id"
                    v-model="selectedUsers"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                      <span class="text-indigo-600 font-medium">{{ getInitials(user.name) }}</span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex gap-1">
                    <span v-if="user.is_admin" class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Admin</span>
                    <span v-if="user.is_service_provider" class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Vendor</span>
                    <span v-if="!user.is_service_provider && !user.is_admin" class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Customer</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="user.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ formatDate(user.created_at) }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                  <Link :href="route('admin.users.show', user.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">View</Link>
                  <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
          <AppPagination :links="users.links" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppPagination from '@/Components/AppPagination.vue';
import { debounce } from 'lodash';

const props = defineProps({
  users: Object,
  stats: Object,
  filters: Object,
});

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');
const statusFilter = ref(props.filters.status || '');
const selectedUsers = ref([]);
const bulkAction = ref('');

const allSelected = computed(() => {
  return props.users.data.length > 0 && selectedUsers.value.length === props.users.data.length;
});

const debouncedSearch = debounce(() => {
  applyFilters();
}, 300);

const applyFilters = () => {
  router.get(route('admin.users.index'), {
    search: search.value,
    role: roleFilter.value,
    status: statusFilter.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const toggleAll = () => {
  if (allSelected.value) {
    selectedUsers.value = [];
  } else {
    selectedUsers.value = props.users.data.map(u => u.id);
  }
};

const executeBulkAction = () => {
  if (!bulkAction.value || selectedUsers.value.length === 0) return;

  const message = bulkAction.value === 'delete' 
    ? 'Are you sure you want to delete these users?' 
    : `Are you sure you want to ${bulkAction.value.replace('_', ' ')} these users?`;

  if (!confirm(message)) return;

  router.post(route('admin.users.bulk'), {
    ids: selectedUsers.value,
    action: bulkAction.value,
  }, {
    onSuccess: () => {
      selectedUsers.value = [];
      bulkAction.value = '';
    },
  });
};

const deleteUser = (user) => {
  if (!confirm(`Are you sure you want to delete ${user.name}?`)) return;
  
  router.delete(route('admin.users.destroy', user.id));
};

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};
</script>
