<?php

namespace App\Http\Controllers;

use App\Mail\PaymentConfirmation;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Services\NotificationService;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * Show payment page for a booking.
     */
    public function show(Booking $booking): Response|RedirectResponse
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Check if already paid
        if ($booking->payment_status === 'paid') {
            return redirect()->route('bookings.confirmation', $booking->id)
                ->with('info', 'This booking has already been paid.');
        }

        return Inertia::render('Payment/Show', [
            'booking' => $booking->load(['service', 'offering', 'provider'])
        ]);
    }

    /**
     * Confirm mock payment and update booking.
     */
    public function confirmPayment(Request $request, Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized');
        }

        $validated = $request->validate([
            'payment_method' => 'required|string',
        ]);

        try {
            $booking->update([
                'payment_status' => 'paid',
                'payment_method' => $validated['payment_method'],
                'paid_at' => now(),
                'payment_notes' => 'Mock Payment Completed',
            ]);

            Mail::to($booking->customer->email)->send(new PaymentConfirmation($booking));
            NotificationService::paymentReceived($booking);

            return redirect()->route('bookings.confirmation', $booking->id);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Process mock refund for a booking.
     */
    public function refund(Booking $booking): RedirectResponse
    {
        // Ensure user owns this booking or is admin
        if ($booking->user_id !== Auth::id() && !Auth::user()?->is_admin) {
            abort(403, 'Unauthorized');
        }

        // Check if payment was made
        if ($booking->payment_status !== 'paid') {
            return back()->with('error', 'No payment to refund.');
        }

        try {
            $booking->update([
                'payment_status' => 'refunded',
                'payment_notes' => trim((string) $booking->payment_notes . ' | Refund manually triggered ' . now()),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

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
