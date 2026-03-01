<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  payments: { type: Object, required: true },
});

function formatPrice(amount) {
  return `$${Number(amount).toFixed(2)}`;
}

function formatDateTime(value) {
  if (!value) return '—';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function statusClasses(status) {
  return {
    paid: 'bg-green-100 text-green-800',
    refunded: 'bg-red-100 text-red-800',
    partially_refunded: 'bg-yellow-100 text-yellow-800',
  }[status] || 'bg-gray-100 text-gray-800';
}
</script>

<template>
  <AppLayout>
    <Head title="Payment History" />

    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Payment History</h1>
          <p class="text-gray-600 mt-2">View past payments and refunds</p>
        </div>

        <div v-if="payments.data?.length" class="space-y-4">
          <div
            v-for="booking in payments.data"
            :key="booking.id"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
          >
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div class="min-w-0">
                <div class="flex items-center gap-3 mb-2">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="statusClasses(booking.payment_status)"
                  >
                    {{ String(booking.payment_status).replace('_', ' ') }}
                  </span>
                  <span class="text-xs text-gray-500">Paid: {{ formatDateTime(booking.paid_at) }}</span>
                </div>

                <p class="text-lg font-semibold text-gray-900 truncate">
                  {{ booking.service?.name ?? `Booking #${booking.id}` }}
                </p>
                <p class="text-sm text-gray-500 mt-1">
                  {{ booking.offering?.name ?? '—' }}
                </p>
              </div>

              <div class="flex sm:flex-col items-start sm:items-end gap-3">
                <p class="text-2xl font-bold text-gray-900">{{ formatPrice(booking.total_price) }}</p>
                <div class="flex gap-2">
                  <Link
                    :href="route('bookings.confirmation', booking.id)"
                    class="text-sm text-gray-600 hover:text-gray-900 px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                  >
                    View Booking
                  </Link>
                  <Link
                    v-if="booking.payment_status === 'paid'"
                    :href="route('bookings.invoice', booking.id)"
                    class="text-sm text-blue-600 hover:text-blue-700 px-3 py-2 rounded-lg border border-blue-200 hover:bg-blue-50 transition"
                  >
                    Invoice
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
          <h3 class="text-xl font-semibold text-gray-900 mb-2">No payments yet</h3>
          <p class="text-gray-600 mb-6">You’ll see completed payments and refunds here.</p>
          <Link
            :href="route('services.index')"
            class="inline-block bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-xl font-medium shadow-md transform hover:-translate-y-0.5 transition-all duration-200 hover:from-blue-700 hover:to-indigo-700"
          >
            Browse Services
          </Link>
        </div>

        <div v-if="payments.links && payments.links.length > 3" class="mt-8 flex justify-center">
          <div class="flex gap-2">
            <Link
              v-for="(link, index) in payments.links"
              :key="index"
              :href="link.url"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium',
                link.active
                  ? 'bg-blue-600 text-white'
                  : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                !link.url && 'opacity-50 cursor-not-allowed',
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
