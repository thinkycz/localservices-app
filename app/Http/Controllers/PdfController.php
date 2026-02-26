<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PdfController extends Controller
{
    /**
     * Generate PDF for booking confirmation.
     */
    public function bookingConfirmation(Request $request, int $bookingId)
    {
        $booking = Booking::with(['service', 'offering', 'customer', 'provider'])
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->orWhere('provider_id', $request->user()->id);
            })
            ->findOrFail($bookingId);

        $pdf = Pdf::loadView('pdfs.booking-confirmation', [
            'booking' => $booking,
            'qrData' => route('bookings.confirmation', $booking->id),
        ]);

        return $pdf->download('booking-confirmation-' . $booking->id . '.pdf');
    }

    /**
     * Generate PDF invoice for a booking.
     */
    public function invoice(Request $request, int $bookingId)
    {
        $booking = Booking::with(['service', 'offering', 'customer', 'provider'])
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->orWhere('provider_id', $request->user()->id);
            })
            ->findOrFail($bookingId);

        // Only generate invoice for paid bookings
        if ($booking->payment_status !== 'paid') {
            abort(403, 'Invoice only available for paid bookings');
        }

        $pdf = Pdf::loadView('pdfs.invoice', [
            'booking' => $booking,
            'invoiceNumber' => 'INV-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
            'invoiceDate' => now()->format('Y-m-d'),
            'qrData' => route('bookings.confirmation', $booking->id),
        ]);

        return $pdf->download('invoice-' . $booking->id . '.pdf');
    }

    /**
     * Preview PDF (for testing).
     */
    public function previewBookingConfirmation(int $bookingId)
    {
        $booking = Booking::with(['service', 'offering', 'customer', 'provider'])
            ->findOrFail($bookingId);

        return view('pdfs.booking-confirmation', [
            'booking' => $booking,
            'qrData' => route('bookings.confirmation', $booking->id),
        ]);
    }
}
