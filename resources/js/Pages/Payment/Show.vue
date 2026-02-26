<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-6">
            <h1 class="text-2xl font-bold text-white">Complete Your Payment</h1>
            <p class="text-green-100 mt-1">Secure payment powered by Stripe</p>
          </div>

          <div class="p-8">
            <!-- Booking Summary -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8">
              <h3 class="font-semibold text-gray-900 mb-4">Booking Summary</h3>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600">Service:</span>
                  <span class="font-medium">{{ booking.service.name }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Offering:</span>
                  <span class="font-medium">{{ booking.offering.name }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Date:</span>
                  <span class="font-medium">{{ formatDate(booking.booking_date) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Time:</span>
                  <span class="font-medium">{{ booking.start_time }} - {{ booking.end_time }}</span>
                </div>
                <div class="border-t pt-2 mt-2">
                  <div class="flex justify-between text-lg font-bold">
                    <span>Total:</span>
                    <span class="text-green-600">${{ booking.total_price }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Payment Form -->
            <div v-if="!paymentSuccess" class="space-y-6">
              <div id="payment-element" class="border rounded-lg p-4">
                <!-- Stripe Payment Element will be mounted here -->
                <div v-if="!stripeLoaded" class="text-center py-8">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto"></div>
                  <p class="text-gray-500 mt-2">Loading payment form...</p>
                </div>
              </div>

              <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-600 text-sm">{{ errorMessage }}</p>
              </div>

              <div class="flex items-center justify-between">
                <Link
                  :href="route('bookings.index')"
                  class="text-gray-600 hover:text-gray-900 font-medium"
                >
                  Cancel
                </Link>
                <PrimaryButton
                  @click="submitPayment"
                  :disabled="!stripeLoaded || processing"
                  class="bg-green-600 hover:bg-green-700"
                >
                  <span v-if="processing">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                  </span>
                  <span v-else>
                    Pay ${{ booking.total_price }}
                  </span>
                </PrimaryButton>
              </div>
            </div>

            <!-- Success Message -->
            <div v-else class="text-center py-8">
              <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Payment Successful!</h3>
              <p class="text-gray-600 mb-6">Your booking has been confirmed.</p>
              <Link
                :href="route('bookings.confirmation', booking.id)"
                class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
              >
                View Confirmation
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, onMounted } from 'vue';
import { loadStripe } from '@stripe/stripe-js';

const props = defineProps({
  booking: Object,
  stripeKey: String,
});

const stripeLoaded = ref(false);
const processing = ref(false);
const paymentSuccess = ref(false);
const errorMessage = ref('');
let stripe = null;
let elements = null;
let paymentElement = null;

onMounted(async () => {
  try {
    // Load Stripe
    stripe = await loadStripe(props.stripeKey);
    
    // Create payment intent
    const response = await fetch(`/api/payments/${props.booking.id}/intent`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    });
    
    const { clientSecret, error } = await response.json();
    
    if (error) {
      errorMessage.value = error;
      return;
    }

    // Create elements
    elements = stripe.elements({
      clientSecret,
      appearance: {
        theme: 'stripe',
        variables: {
          colorPrimary: '#16a34a',
        },
      },
    });

    // Create and mount payment element
    paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');
    
    stripeLoaded.value = true;
  } catch (err) {
    errorMessage.value = 'Failed to load payment form. Please try again.';
    console.error(err);
  }
});

const submitPayment = async () => {
  if (!stripe || !elements) return;
  
  processing.value = true;
  errorMessage.value = '';

  try {
    const { error, paymentIntent } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        return_url: window.location.origin + `/bookings/confirmation/${props.booking.id}`,
      },
      redirect: 'if_required',
    });

    if (error) {
      errorMessage.value = error.message;
      processing.value = false;
      return;
    }

    // Payment succeeded without redirect
    if (paymentIntent && paymentIntent.status === 'succeeded') {
      // Confirm with backend
      const response = await fetch(`/api/payments/${props.booking.id}/confirm`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
          payment_intent_id: paymentIntent.id,
          payment_method: paymentIntent.payment_method,
        }),
      });

      const result = await response.json();
      
      if (result.success) {
        paymentSuccess.value = true;
      } else {
        errorMessage.value = result.error || 'Payment confirmation failed';
      }
    }
  } catch (err) {
    errorMessage.value = 'Payment processing failed. Please try again.';
    console.error(err);
  } finally {
    processing.value = false;
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};
</script>
