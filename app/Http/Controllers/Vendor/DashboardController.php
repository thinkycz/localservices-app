<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Shop;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the vendor dashboard with real data.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $now = Carbon::now(config('app.timezone'));

        $shops = Shop::where('user_id', $user->id)->get();
        $shopIds = $shops->pluck('id');
        $bookings = Booking::whereIn('shop_id', $shopIds)
            ->with(['customer', 'shop', 'service'])
            ->get();

        $totalBookings = $bookings->count();
        $cancelledBookings = $bookings->where('status', 'cancelled')->count();
        $pendingBookings = $bookings->where('status', 'pending')->count();
        $confirmedBookings = $bookings->where('status', 'confirmed')->count();

        $customerGroups = $bookings->groupBy(fn (Booking $booking) => $this->customerIdentity($booking));
        $totalCustomers = $customerGroups->count();
        $newCustomers = $customerGroups->filter(function (Collection $customerBookings) use ($now): bool {
            $firstBooking = $customerBookings->sortBy('created_at')->first();

            return $firstBooking?->created_at?->gte($now->copy()->startOfMonth()) ?? false;
        })->count();
        $returningCustomers = $customerGroups
            ->filter(fn (Collection $customerBookings) => $customerBookings->count() > 1)
            ->count();

        $todayBookings = $bookings
            ->filter(fn (Booking $booking) => $booking->booking_date->isSameDay($now))
            ->sortBy('start_time')
            ->values();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $now->copy()->endOfWeek(Carbon::SUNDAY);
        $weekBookings = $bookings
            ->filter(fn (Booking $booking) => $booking->booking_date->betweenIncluded($weekStart, $weekEnd))
            ->values();

        $servicePopularity = $bookings->groupBy('service_id')
            ->map(function (Collection $serviceBookings) use ($totalBookings): array {
                $service = $serviceBookings->first()->service;
                $count = $serviceBookings->count();

                return [
                    // Keep the legacy `shop` key until the dashboard component is migrated.
                    'shop' => $service?->name ?? 'Unknown service',
                    'count' => $count,
                    'percentage' => $totalBookings > 0 ? round(($count / $totalBookings) * 100) : 0,
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthBookings = $bookings->filter(fn (Booking $booking) => $booking->booking_date->year === $month->year
                && $booking->booking_date->month === $month->month
                && $booking->status !== 'cancelled'
            );
            $revenueByCurrency = $this->revenueByCurrency($monthBookings);

            $monthlyRevenue[] = [
                'month' => $month->format('M'),
                // The numeric legacy value is retained only when there is one currency.
                'revenue' => $revenueByCurrency->count() === 1 ? $revenueByCurrency->first() : null,
                'currency' => $revenueByCurrency->count() === 1 ? $revenueByCurrency->keys()->first() : null,
                'formatted_revenue' => $this->formatCurrencyTotals($revenueByCurrency),
                'revenue_by_currency' => $revenueByCurrency->all(),
                'bookings' => $monthBookings->count(),
            ];
        }

        $recentBookings = $bookings
            ->sortByDesc('created_at')
            ->take(5)
            ->values()
            ->map(function (Booking $booking): array {
                $currency = $this->bookingCurrency($booking);
                $customerName = $booking->customer_display_name ?: 'Zákazník bez jména';

                return [
                    'id' => $booking->id,
                    'customer_name' => $customerName,
                    'service_name' => $booking->service?->name ?? $booking->shop?->name ?? 'Služba',
                    'date' => $booking->booking_date->format('Y-m-d'),
                    'time' => $booking->start_time,
                    'status' => $booking->status,
                    'price' => (float) $booking->total_price,
                    'currency' => $currency,
                    'formatted_price' => $this->formatMoney($booking->total_price, $currency),
                ];
            });

        $revenueByCurrency = $this->revenueByCurrency($bookings);
        $revenueString = $this->formatCurrencyTotals($revenueByCurrency, $shops->pluck('currency'));

        $stats = [
            [
                'label' => 'Total Bookings',
                'value' => $totalBookings,
                'change' => $this->calculateChange($bookings, 'week'),
                'positive' => true,
                'icon' => 'calendar-check',
                'iconBg' => 'bg-blue-100',
                'iconColor' => 'text-blue-600',
            ],
            [
                'label' => 'Cancellations',
                'value' => $cancelledBookings,
                'change' => $this->calculateCancellationChange($bookings),
                'positive' => $cancelledBookings < ($bookings->count() * 0.1),
                'icon' => 'calendar-x',
                'iconBg' => 'bg-red-100',
                'iconColor' => 'text-red-600',
            ],
            [
                'label' => 'New Customers',
                'value' => $newCustomers,
                'change' => '+'.$newCustomers.' this month',
                'positive' => true,
                'icon' => 'user-plus',
                'iconBg' => 'bg-green-100',
                'iconColor' => 'text-green-600',
            ],
            [
                'label' => 'Revenue',
                'value' => $revenueString,
                'details' => $revenueString,
                'revenue_by_currency' => $revenueByCurrency->all(),
                'change' => $this->calculateRevenueChange($bookings),
                'positive' => true,
                'icon' => 'cash',
                'iconBg' => 'bg-purple-100',
                'iconColor' => 'text-purple-600',
            ],
        ];

        $weekRevenueByCurrency = $this->revenueByCurrency($weekBookings);
        $services = Service::whereIn('shop_id', $shopIds)->get();

        return Inertia::render('Vendor/Dashboard', [
            'stats' => $stats,
            'todayBookings' => $todayBookings->map(function (Booking $booking): array {
                $customerName = $booking->customer_display_name ?: 'Zákazník bez jména';

                return [
                    'id' => $booking->id,
                    'time' => Carbon::parse($booking->start_time)->format('g:i A'),
                    'end_time' => Carbon::parse($booking->end_time)->format('g:i A'),
                    'duration' => ($booking->service?->duration_minutes ?? 60).' min',
                    'title' => $booking->service?->name ?? $booking->shop?->name ?? 'Služba',
                    'customer' => $customerName,
                    'customer_initials' => $this->getInitials($customerName),
                    'status' => strtoupper($booking->status),
                    'completed' => in_array($booking->status, ['completed', 'cancelled'], true),
                ];
            }),
            'weekStats' => [
                'total_bookings' => $weekBookings->count(),
                'completed' => $weekBookings->where('status', 'completed')->count(),
                'revenue' => $this->formatCurrencyTotals($weekRevenueByCurrency, $shops->pluck('currency')),
                'revenue_by_currency' => $weekRevenueByCurrency->all(),
            ],
            'servicePopularity' => $servicePopularity,
            'monthlyRevenue' => $monthlyRevenue,
            'recentBookings' => $recentBookings,
            'overview' => [
                'total_services' => $services->count(),
                'available_services' => $services->where('is_available', true)->count(),
                'total_customers' => $totalCustomers,
                'pending_bookings' => $pendingBookings,
                'confirmed_bookings' => $confirmedBookings,
                'returning_customers' => $returningCustomers,
            ],
        ]);
    }

    private function calculateChange(Collection $bookings, string $period = 'week'): string
    {
        $now = Carbon::now(config('app.timezone'));
        $currentPeriod = $period === 'month'
            ? $now->copy()->startOfMonth()
            : $now->copy()->startOfWeek();
        $previousPeriod = $period === 'month'
            ? $now->copy()->subMonth()->startOfMonth()
            : $now->copy()->subWeek()->startOfWeek();

        $current = $bookings->filter(fn (Booking $booking) => $booking->created_at->gte($currentPeriod))->count();
        $previous = $bookings->filter(fn (Booking $booking) => $booking->created_at->gte($previousPeriod) && $booking->created_at->lt($currentPeriod)
        )->count();

        if ($previous === 0) {
            return $current > 0 ? '+100%' : '0%';
        }

        $change = (($current - $previous) / $previous) * 100;

        return ($change >= 0 ? '+' : '').round($change).'%';
    }

    private function calculateCancellationChange(Collection $bookings): string
    {
        if ($bookings->isEmpty()) {
            return '0%';
        }

        return round(($bookings->where('status', 'cancelled')->count() / $bookings->count()) * 100).'% rate';
    }

    private function calculateRevenueChange(Collection $bookings): string
    {
        $now = Carbon::now(config('app.timezone'));
        $thisMonth = $this->revenueByCurrency($bookings->filter(fn (Booking $booking) => $booking->created_at->gte($now->copy()->startOfMonth())
        ));
        $lastMonth = $this->revenueByCurrency($bookings->filter(fn (Booking $booking) => $booking->created_at->gte($now->copy()->subMonth()->startOfMonth())
            && $booking->created_at->lt($now->copy()->startOfMonth())
        ));

        $currencies = $thisMonth->keys()->merge($lastMonth->keys())->unique();
        if ($currencies->isEmpty()) {
            return '0%';
        }

        return $currencies->map(function (string $currency) use ($thisMonth, $lastMonth, $currencies): string {
            $current = (float) $thisMonth->get($currency, 0);
            $previous = (float) $lastMonth->get($currency, 0);
            $change = $previous === 0.0
                ? ($current > 0 ? 100 : 0)
                : (($current - $previous) / $previous) * 100;
            $formatted = ($change >= 0 ? '+' : '').round($change).'%';

            return $currencies->count() > 1 ? $currency.' '.$formatted : $formatted;
        })->implode(' | ');
    }

    private function customerIdentity(Booking $booking): string
    {
        if ($booking->user_id !== null) {
            return 'user:'.$booking->user_id;
        }

        $email = mb_strtolower(trim($booking->customer_contact_email));

        return $email !== '' ? 'guest:'.$email : 'guest-booking:'.$booking->id;
    }

    private function bookingCurrency(Booking $booking): string
    {
        return strtoupper($booking->currency ?: $booking->shop?->currency ?: 'CZK');
    }

    private function revenueByCurrency(Collection $bookings): Collection
    {
        return $bookings
            ->where('status', '!=', 'cancelled')
            ->groupBy(fn (Booking $booking) => $this->bookingCurrency($booking))
            ->map(fn (Collection $currencyBookings) => $currencyBookings->sum(
                fn (Booking $booking) => (float) $booking->total_price
            ));
    }

    private function formatCurrencyTotals(Collection $totals, ?Collection $fallbackCurrencies = null): string
    {
        if ($totals->isNotEmpty()) {
            return $totals->map(
                fn ($amount, string $currency) => $this->formatMoney($amount, $currency)
            )->implode(' | ');
        }

        $currencies = ($fallbackCurrencies ?? collect(['CZK']))
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

    private function getInitials(string $name): string
    {
        $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
        }

        return $words === [] ? '?' : mb_strtoupper(mb_substr($words[0], 0, 2));
    }
}
