<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Shop;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Display the vendor calendar with real booking data.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $view = $request->get('view', 'week');

        // Get all services for this vendor
        $shops = Shop::where('user_id', $user->id)->get();
        $shopIds = $shops->pluck('id');

        $referenceDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))
            : Carbon::now();

        if ($view === 'today') {
            $startDate = Carbon::now()->startOfDay();
            $endDate = $startDate->copy();
        } elseif ($view === 'day') {
            $startDate = $referenceDate->copy()->startOfDay();
            $endDate = $startDate->copy();
        } elseif ($view === 'month') {
            $startDate = $referenceDate->copy()->startOfMonth();
            $endDate = $referenceDate->copy()->endOfMonth();
        } else {
            $startDate = $referenceDate->copy()->startOfWeek(Carbon::MONDAY);
            $endDate = $referenceDate->copy()->endOfWeek(Carbon::SUNDAY);
            $view = 'week';
        }

        // Get all bookings for the date range
        $bookings = Booking::whereIn('shop_id', $shopIds)
            ->whereDate('booking_date', '>=', $startDate->toDateString())
            ->whereDate('booking_date', '<=', $endDate->toDateString())
            ->with(['customer', 'shop', 'service'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        // Guest bookings cannot be grouped by their null user_id. Use the stored
        // contact email as their identity so unrelated guests stay separate.
        $customerBookingCounts = Booking::whereIn('shop_id', $shopIds)
            ->with('customer')
            ->get()
            ->groupBy(fn (Booking $booking) => $this->customerIdentity($booking))
            ->map->count();

        // Format bookings for the calendar
        $formattedBookings = $bookings->map(function (Booking $booking) use ($customerBookingCounts, $startDate) {
            $startTime = Carbon::parse($booking->start_time);
            $endTime = Carbon::parse($booking->end_time);

            // Determine color type based on status
            $colorType = match ($booking->status) {
                'pending' => 'yellow',
                'confirmed' => 'blue',
                'completed' => 'green',
                'cancelled' => 'red',
                default => 'blue',
            };

            $customerName = $booking->customer_display_name ?: 'Zákazník bez jména';
            $customerBookingsCount = $customerBookingCounts->get($this->customerIdentity($booking), 1);
            $customerType = $customerBookingsCount > 1 ? 'Regular Customer' : 'New Customer';
            $duration = $booking->service?->duration_minutes
                ?? $startTime->diffInMinutes($endTime);
            $currency = strtoupper($booking->currency ?: $booking->shop?->currency ?: 'CZK');

            return [
                'id' => $booking->id,
                'customer' => $customerName,
                'shop' => $booking->service?->name ?? $booking->shop?->name ?? 'Služba',
                'serviceDetail' => $booking->service?->name ?? 'Služba',
                'dayIndex' => Carbon::parse($booking->booking_date)->startOfDay()->diffInDays($startDate->copy()->startOfDay()),
                'fullDate' => Carbon::parse($booking->booking_date)->format('Y-m-d'),
                'startHour' => (int) $startTime->format('H'),
                'startMin' => (int) $startTime->format('i'),
                'duration' => $duration,
                'colorType' => $colorType,
                'status' => $booking->status,
                'initials' => $this->getInitials($customerName),
                'avatarBg' => $this->getAvatarBg($customerName),
                'avatarText' => $this->getAvatarText($customerName),
                'dateStr' => Carbon::parse($booking->booking_date)->locale('cs')->translatedFormat('j. F Y'),
                'timeStr' => $startTime->format('H:i').'–'.$endTime->format('H:i'),
                'price' => $this->formatMoney($booking->total_price, $currency),
                'currency' => $currency,
                'customerType' => $customerType,
                'notes' => $booking->customer_notes ? '"'.$booking->customer_notes.'"' : '',
                'customerEmail' => $booking->customer_contact_email,
                'customerPhone' => $booking->customer_phone ?: $booking->customer?->phone,
            ];
        });

        $daysCount = $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1;

        $weekDays = [];
        for ($i = 0; $i < $daysCount; $i++) {
            $date = $startDate->copy()->addDays($i);
            $weekDays[] = [
                'day' => $date->locale('cs')->translatedFormat('D'),
                'date' => (int) $date->format('d'),
                'dayIndex' => $i,
                'isToday' => $date->isToday(),
                'fullDate' => $date->format('Y-m-d'),
            ];
        }

        if ($view === 'month') {
            $rangeLabel = $startDate->locale('cs')->translatedFormat('F Y');
        } elseif ($view === 'day' || $view === 'today') {
            $rangeLabel = $startDate->locale('cs')->translatedFormat('j. F Y');
        } else {
            $rangeLabel = $startDate->locale('cs')->translatedFormat('j. M').' – '.$endDate->locale('cs')->translatedFormat('j. M Y');
        }

        // Calculate stats for the week
        $revenueByCurrency = $this->revenueByCurrency($bookings);
        $weekStats = [
            'total_bookings' => $bookings->count(),
            'completed' => $bookings->where('status', 'completed')->count(),
            'pending' => $bookings->where('status', 'pending')->count(),
            'confirmed' => $bookings->where('status', 'confirmed')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
            // Preserve the numeric prop when there is one currency; a mixed
            // total is intentionally null because adding raw currencies is false.
            'revenue' => $revenueByCurrency->count() <= 1
                ? (float) ($revenueByCurrency->first() ?? 0)
                : null,
            'formatted_revenue' => $this->formatCurrencyTotals($revenueByCurrency, $shops->pluck('currency')),
            'revenue_by_currency' => $revenueByCurrency->all(),
        ];

        return Inertia::render('Vendor/Calendar', [
            'bookings' => $formattedBookings,
            'weekDays' => $weekDays,
            'weekRange' => $rangeLabel,
            'weekStats' => $weekStats,
            'currentView' => $view,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get initials from name.
     */
    private function getInitials(string $name): string
    {
        $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
        }

        return $words === [] ? '?' : mb_strtoupper(mb_substr($words[0], 0, 2));
    }

    private function customerIdentity(Booking $booking): string
    {
        if ($booking->user_id !== null) {
            return 'user:'.$booking->user_id;
        }

        $email = mb_strtolower(trim($booking->customer_contact_email));

        return $email !== '' ? 'guest:'.$email : 'guest-booking:'.$booking->id;
    }

    private function revenueByCurrency(Collection $bookings): Collection
    {
        return $bookings
            ->where('status', '!=', 'cancelled')
            ->groupBy(fn (Booking $booking) => strtoupper(
                $booking->currency ?: $booking->shop?->currency ?: 'CZK'
            ))
            ->map(fn (Collection $currencyBookings) => $currencyBookings->sum(
                fn (Booking $booking) => (float) $booking->total_price
            ));
    }

    private function formatCurrencyTotals(Collection $totals, Collection $fallbackCurrencies): string
    {
        if ($totals->isNotEmpty()) {
            return $totals->map(
                fn ($amount, string $currency) => $this->formatMoney($amount, $currency)
            )->implode(' | ');
        }

        $currencies = $fallbackCurrencies
            ->filter()
            ->map(fn ($currency) => strtoupper((string) $currency))
            ->unique()
            ->values();

        if ($currencies->isEmpty()) {
            $currencies = collect(['CZK']);
        }

        return $currencies->map(fn (string $currency) => $this->formatMoney(0, $currency))->implode(' | ');
    }

    private function formatMoney($amount, string $currency): string
    {
        return Money::format($amount, $currency);
    }

    /**
     * Get avatar background color based on name.
     */
    private function getAvatarBg(string $name): string
    {
        $colors = ['bg-blue-200', 'bg-green-200', 'bg-purple-200', 'bg-pink-200', 'bg-orange-200', 'bg-teal-200'];
        $index = crc32($name) % count($colors);

        return $colors[$index];
    }

    /**
     * Get avatar text color based on name.
     */
    private function getAvatarText(string $name): string
    {
        $colors = ['text-blue-700', 'text-green-700', 'text-purple-700', 'text-pink-700', 'text-orange-700', 'text-teal-700'];
        $index = crc32($name) % count($colors);

        return $colors[$index];
    }
}
