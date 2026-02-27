<template>
  <AppLayout>
    <Head :title="`User: ${user.name}`" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ user.name }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ user.email }}</p>
          </div>
          <Link
            :href="route('admin.users.index')"
            class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Back to Users
          </Link>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
          <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm font-medium text-gray-500">Bookings</p>
            <p class="text-2xl font-bold text-gray-900">{{ activity.total_bookings }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm font-medium text-gray-500">Services</p>
            <p class="text-2xl font-bold text-gray-900">{{ activity.total_services }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm font-medium text-gray-500">Reviews</p>
            <p class="text-2xl font-bold text-gray-900">{{ activity.total_reviews }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm font-medium text-gray-500">Member Since</p>
            <p class="text-sm font-semibold text-gray-900 mt-2">{{ activity.member_since }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm font-medium text-gray-500">Last Login</p>
            <p class="text-sm font-semibold text-gray-900 mt-2">{{ activity.last_login }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">User Details</h2>

            <form @submit.prevent="submit" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                  <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                  <input
                    v-model="form.email"
                    type="email"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                  <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                  <input
                    v-model="form.phone"
                    type="text"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                  <p v-if="form.errors.phone" class="text-sm text-red-600 mt-1">{{ form.errors.phone }}</p>
                </div>

                <div class="flex items-center gap-6 pt-6">
                  <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input v-model="form.is_admin" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    Admin
                  </label>
                  <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input v-model="form.is_service_provider" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    Vendor
                  </label>
                </div>
              </div>

              <div class="flex items-center justify-between pt-2">
                <div class="flex gap-2">
                  <button
                    type="button"
                    @click="toggleAdmin"
                    class="px-3 py-2 rounded-md text-sm font-medium border border-gray-200 hover:bg-gray-50"
                  >
                    Toggle Admin
                  </button>
                  <button
                    type="button"
                    @click="toggleVendor"
                    class="px-3 py-2 rounded-md text-sm font-medium border border-gray-200 hover:bg-gray-50"
                  >
                    Toggle Vendor
                  </button>
                </div>

                <button
                  type="submit"
                  :disabled="form.processing"
                  class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                  Save Changes
                </button>
              </div>
            </form>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Roles</h2>
            <div class="flex flex-wrap gap-2">
              <span v-if="user.is_admin" class="px-2.5 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Admin</span>
              <span v-if="user.is_service_provider" class="px-2.5 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Vendor</span>
              <span v-if="!user.is_service_provider && !user.is_admin" class="px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Customer</span>
              <span
                :class="user.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                class="px-2.5 py-1 text-xs font-medium rounded-full"
              >
                {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
              </span>
            </div>

            <div class="mt-6">
              <button
                @click="deleteUser"
                class="w-full px-4 py-2 border border-red-200 text-red-700 text-sm font-medium rounded-md hover:bg-red-50"
              >
                Delete User
              </button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Services</h2>
            <div v-if="user.services?.length" class="space-y-3">
              <div
                v-for="service in user.services"
                :key="service.id"
                class="flex items-center justify-between gap-4 border border-gray-100 rounded-lg p-4"
              >
                <div class="min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ service.name }}</p>
                  <p class="text-xs text-gray-500 mt-0.5">{{ service.slug }}</p>
                </div>
                <div class="flex items-center gap-2">
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="service.is_available ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                  >
                    {{ service.is_available ? 'Available' : 'Unavailable' }}
                  </span>
                  <span class="text-xs text-gray-600">{{ Number(service.rating ?? 0).toFixed(1) }}</span>
                </div>
              </div>
            </div>
            <p v-else class="text-sm text-gray-500">No services.</p>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Bookings</h2>
            <div v-if="user.bookings?.length" class="space-y-3">
              <div
                v-for="booking in user.bookings"
                :key="booking.id"
                class="flex items-center justify-between gap-4 border border-gray-100 rounded-lg p-4"
              >
                <div class="min-w-0">
                  <p class="text-sm font-medium text-gray-900">Booking #{{ booking.id }}</p>
                  <p class="text-xs text-gray-500 mt-0.5">{{ booking.booking_date }}</p>
                </div>
                <div class="flex items-center gap-2">
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                    {{ booking.status }}
                  </span>
                  <span class="text-xs text-gray-600">${{ Number(booking.total_price ?? 0).toFixed(2) }}</span>
                </div>
              </div>
            </div>
            <p v-else class="text-sm text-gray-500">No bookings.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
  user: { type: Object, required: true },
  activity: { type: Object, required: true },
});

const form = useForm({
  name: props.user.name ?? '',
  email: props.user.email ?? '',
  phone: props.user.phone ?? '',
  is_admin: !!props.user.is_admin,
  is_service_provider: !!props.user.is_service_provider,
});

const submit = () => {
  form.patch(route('admin.users.update', props.user.id));
};

const toggleAdmin = () => {
  router.post(route('admin.users.toggle-admin', props.user.id));
};

const toggleVendor = () => {
  router.post(route('admin.users.toggle-vendor', props.user.id));
};

const deleteUser = () => {
  if (!confirm('Are you sure you want to delete this user?')) return;
  router.delete(route('admin.users.destroy', props.user.id));
};
</script>
