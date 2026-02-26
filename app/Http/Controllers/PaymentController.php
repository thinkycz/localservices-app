<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * Show payment page for a booking.
     */
    public function show(Booking $booking): Response
    {
        // Ensure user owns this booking
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Check if already paid
        if ($booking->payment_status === 'paid') {
            return redirect()->route('bookings.confirmation', $booking->id)
                ->with('info', 'This booking has already been paid.');
        }

        return Inertia::render('Payment/Show', [
            'booking' => $booking->load(['service', 'offering', 'provider']),
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    /**
     * Process payment intent for Stripe.
     */
    public function createPaymentIntent(Booking $booking): JsonResponse
    {
        // Ensure user owns this booking
        if ($booking->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if already paid
        if ($booking->payment_status === 'paid') {
            return response()->json(['error' => 'Booking already paid'], 400);
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int) ($booking->total_price * 100), // Convert to cents
                'currency' => 'usd',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'service' => $booking->service->name,
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Confirm payment and update booking.
     */
    public function confirmPayment(Request $request, Booking $booking): JsonResponse
    {
        // Ensure user owns this booking
        if ($booking->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = \Stripe\PaymentIntent::retrieve($validated['payment_intent_id']);

            if ($paymentIntent->status === 'succeeded') {
                $booking->update([
                    'payment_status' => 'paid',
                    'payment_method' => $validated['payment_method'],
                    'paid_at' => now(),
                    'payment_notes' => 'Stripe PaymentIntent: ' . $validated['payment_intent_id'],
                ]);

                // Send confirmation email
                // TODO: Dispatch job to send payment confirmation email

                return response()->json([
                    'success' => true,
                    'redirect' => route('bookings.confirmation', $booking->id),
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Payment not completed. Status: ' . $paymentIntent->status,
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Process refund for a booking.
     */
    public function refund(Booking $booking): RedirectResponse
    {
        // Ensure user owns this booking or is admin
        if ($booking->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }

        // Check if payment was made
        if ($booking->payment_status !== 'paid') {
            return back()->with('error', 'No payment to refund.');
        }

        // TODO: Implement Stripe refund logic
        // This would extract the payment intent from payment_notes and create a refund

        $booking->update([
            'payment_status' => 'refunded',
            'payment_notes' => $booking->payment_notes . ' | Refunded: ' . now(),
        ]);

        return back()->with('success', 'Refund processed successfully.');
    }

    /**
     * Get payment history for user.
     */
    public function history(Request $request): Response
    {
        $payments = Booking::where('user_id', $request->user()->id)
            ->whereIn('payment_status', ['paid', 'refunded', 'partially_refunded'])
            ->with(['service', 'offering'])
            ->orderBy('paid_at', 'desc')
            ->paginate(10);

        return Inertia::render('Payment/History', [
            'payments' => $payments,
        ]);
    }
}
