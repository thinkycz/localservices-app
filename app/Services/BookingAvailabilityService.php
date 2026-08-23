<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Shop;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class BookingAvailabilityService
{
    /**
     * @return array{slots: list<string>, closed: bool, reason: string|null, timezone: string}
     */
    public function forDate(Shop $shop, Service $service, CarbonImmutable $date): array
    {
        $timezone = $shop->timezone ?: config('app.timezone');
        $day = $date->setTimezone($timezone)->startOfDay();

        if ($service->shop_id !== $shop->id || ! $shop->is_available || ! $service->is_available) {
            return $this->closed($timezone, 'unavailable');
        }

        if ($day->lt(CarbonImmutable::now($timezone)->startOfDay())) {
            return $this->closed($timezone, 'past_date');
        }

        $shop->loadMissing('businessHours');
        $businessHour = $shop->businessHours->firstWhere('day_of_week', $day->dayOfWeek);

        if ($shop->businessHours->isNotEmpty() && ! $businessHour) {
            return $this->closed($timezone, 'closed');
        }

        $opensAt = $businessHour?->time_from ?? '09:00';
        $closesAt = $businessHour?->time_to ?? '18:00';
        $cursor = CarbonImmutable::parse($day->format('Y-m-d').' '.$opensAt, $timezone);
        $closing = CarbonImmutable::parse($day->format('Y-m-d').' '.$closesAt, $timezone);
        $now = CarbonImmutable::now($timezone);

        $bookings = Booking::query()
            ->where('shop_id', $shop->id)
            ->whereDate('booking_date', $day->toDateString())
            ->whereIn('status', BookingStatus::activeValues())
            ->get(['start_time', 'end_time']);

        $slots = [];
        while ($cursor->addMinutes($service->duration_minutes)->lte($closing)) {
            $slotEnd = $cursor->addMinutes($service->duration_minutes);
            $overlaps = $bookings->contains(function (Booking $booking) use ($cursor, $slotEnd): bool {
                $existingStart = substr($booking->start_time, 0, 5);
                $existingEnd = substr($booking->end_time, 0, 5);

                return $cursor->format('H:i') < $existingEnd
                    && $slotEnd->format('H:i') > $existingStart;
            });

            if ($cursor->gt($now) && ! $overlaps) {
                $slots[] = $cursor->format('H:i');
            }

            $cursor = $cursor->addMinutes(30);
        }

        return [
            'slots' => $slots,
            'closed' => false,
            'reason' => $slots === [] ? 'no_slots' : null,
            'timezone' => $timezone,
        ];
    }

    /**
     * @return array{CarbonImmutable, CarbonImmutable}
     */
    public function assertBookable(Shop $shop, Service $service, string $date, string $time): array
    {
        $timezone = $shop->timezone ?: config('app.timezone');
        $day = CarbonImmutable::createFromFormat('!Y-m-d', $date, $timezone);
        $availability = $this->forDate($shop, $service, $day);

        if (! in_array($time, $availability['slots'], true)) {
            throw ValidationException::withMessages([
                'start_time' => __('The selected time is no longer available.'),
            ]);
        }

        $start = CarbonImmutable::parse($date.' '.$time, $timezone);

        return [$start, $start->addMinutes($service->duration_minutes)];
    }

    /**
     * @return array{slots: list<string>, closed: true, reason: string, timezone: string}
     */
    private function closed(string $timezone, string $reason): array
    {
        return [
            'slots' => [],
            'closed' => true,
            'reason' => $reason,
            'timezone' => $timezone,
        ];
    }
}
