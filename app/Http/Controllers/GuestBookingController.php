<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GuestBookingController extends Controller
{
    public function show(Booking $booking, string $token): Response
    {
        $this->authorizeToken($booking, $token);
        $booking->load(['shop', 'service', 'provider']);

        return Inertia::render('Booking/GuestManage', [
            'booking' => $booking,
            'token' => $token,
            'canCancel' => $booking->canBeCancelledByCustomer(),
        ]);
    }

    public function cancel(Booking $booking, string $token, BookingService $bookings): RedirectResponse
    {
        $this->authorizeToken($booking, $token);
        $bookings->cancelByCustomer($booking);

        return back()->with('success', __('Booking cancelled successfully.'));
    }

    public function claim(Request $request, Booking $booking, string $token): RedirectResponse
    {
        $this->authorizeToken($booking, $token);
        abort_unless(
            hash_equals(mb_strtolower($booking->customer_email), mb_strtolower($request->user()->email)),
            403
        );

        DB::transaction(function () use ($booking, $request): void {
            Booking::query()
                ->whereNull('user_id')
                ->whereRaw('LOWER(customer_email) = ?', [mb_strtolower($booking->customer_email)])
                ->lockForUpdate()
                ->update([
                    'user_id' => $request->user()->id,
                    'guest_token_hash' => null,
                ]);
        });

        return redirect()->route('bookings.index')
            ->with('success', __('Booking added to your account.'));
    }

    private function authorizeToken(Booking $booking, string $token): void
    {
        abort_unless($booking->hasGuestToken($token), 404);
    }
}
