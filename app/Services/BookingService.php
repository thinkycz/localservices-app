<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Mail\BookingConfirmation;
use App\Mail\BookingStatusUpdated;
use App\Mail\NewBookingNotification;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly BookingAvailabilityService $availability,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{booking: Booking, guest_token: string|null}
     */
    public function create(array $data, ?User $customer): array
    {
        $service = Service::with('shop.businessHours')->findOrFail($data['service_id']);
        $shop = $service->shop;
        $lockName = "booking:{$shop->id}:{$data['booking_date']}";

        return Cache::lock($lockName, 10)->block(5, function () use ($data, $customer, $service, $shop): array {
            return DB::transaction(function () use ($data, $customer, $service, $shop): array {
                Booking::query()
                    ->where('shop_id', $shop->id)
                    ->whereDate('booking_date', $data['booking_date'])
                    ->whereIn('status', BookingStatus::activeValues())
                    ->lockForUpdate()
                    ->get();

                [$start, $end] = $this->availability->assertBookable(
                    $shop,
                    $service,
                    $data['booking_date'],
                    $data['start_time'],
                );

                $guestToken = $customer ? null : Str::random(64);
                $booking = Booking::create([
                    'user_id' => $customer?->id,
                    'shop_id' => $shop->id,
                    'service_id' => $service->id,
                    'provider_id' => $shop->user_id,
                    'customer_name' => $data['full_name'],
                    'customer_email' => mb_strtolower($data['email']),
                    'customer_phone' => $data['phone'],
                    'guest_token_hash' => $guestToken ? hash('sha256', $guestToken) : null,
                    'price_amount' => $service->price,
                    'currency' => $shop->currency,
                    'timezone' => $shop->timezone,
                    'status' => BookingStatus::Pending->value,
                    'booking_date' => $data['booking_date'],
                    'start_time' => $start->format('H:i:s'),
                    'end_time' => $end->format('H:i:s'),
                    'customer_notes' => $data['customer_notes'] ?? null,
                ]);

                if ($customer && ! $customer->phone) {
                    $customer->update(['phone' => $data['phone']]);
                }

                $this->notifyBookingCreatedAfterCommit($booking, $guestToken);

                return ['booking' => $booking, 'guest_token' => $guestToken];
            }, 3);
        });
    }

    public function transition(Booking $booking, BookingStatus $next, ?string $reason = null): void
    {
        $current = $booking->statusEnum();
        if (! $current->canTransitionTo($next)) {
            throw ValidationException::withMessages([
                'status' => __('This booking status change is not allowed.'),
            ]);
        }

        if ($next === BookingStatus::Completed
            && CarbonImmutable::now($booking->timezone)->lt($booking->appointmentStartsAt())) {
            throw ValidationException::withMessages([
                'status' => __('A booking can only be completed after it starts.'),
            ]);
        }

        DB::transaction(function () use ($booking, $current, $next, $reason): void {
            $booking->update([
                'status' => $next->value,
                'cancellation_reason' => $next === BookingStatus::Cancelled ? $reason : null,
            ]);

            DB::afterCommit(function () use ($booking, $current, $next): void {
                $booking->loadMissing(['customer', 'shop', 'service', 'provider']);
                $this->runNotificationSafely(
                    $booking,
                    'customer_status_email',
                    fn () => Mail::to($booking->customer_contact_email)
                        ->queue(new BookingStatusUpdated($booking, $current->value, $next->value)),
                );
                $this->runNotificationSafely(
                    $booking,
                    'status_notification',
                    fn () => NotificationService::bookingStatusUpdated($booking, $current->value),
                );
            });
        });
    }

    public function cancelByCustomer(Booking $booking, ?CarbonImmutable $now = null): void
    {
        if (! $booking->canBeCancelledByCustomer($now)) {
            throw ValidationException::withMessages([
                'booking' => __('Bookings can only be cancelled at least 24 hours before they start.'),
            ]);
        }

        $this->transition($booking, BookingStatus::Cancelled);
    }

    private function notifyBookingCreatedAfterCommit(Booking $booking, ?string $guestToken): void
    {
        DB::afterCommit(function () use ($booking, $guestToken): void {
            $booking->loadMissing(['customer', 'shop', 'service', 'provider']);
            $manageUrl = $guestToken
                ? route('guest.bookings.show', ['booking' => $booking->id, 'token' => $guestToken])
                : null;

            $this->runNotificationSafely(
                $booking,
                'customer_confirmation_email',
                fn () => Mail::to($booking->customer_contact_email)
                    ->queue(new BookingConfirmation($booking, $manageUrl)),
            );
            $this->runNotificationSafely(
                $booking,
                'provider_booking_email',
                fn () => Mail::to($booking->provider->email)
                    ->queue(new NewBookingNotification($booking)),
            );
            $this->runNotificationSafely(
                $booking,
                'booking_notification',
                fn () => NotificationService::bookingCreated($booking),
            );
        });
    }

    private function runNotificationSafely(Booking $booking, string $channel, callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $exception) {
            Log::warning('Post-commit booking notification failed.', [
                'booking_id' => $booking->id,
                'channel' => $channel,
                'exception' => $exception::class,
            ]);
        }
    }
}
