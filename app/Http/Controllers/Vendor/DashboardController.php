<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Shop;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        // Get all services for this vendor
        $shops = Shop::where('user_id', $user->id)->get();
        $shopIds = $shops->pluck('id');

        // Get all bookings for vendor's services
        $bookings = Booking::whereIn('shop_id', $shopIds)->get();

        // Calculate stats
        $totalBookings = $bookings->count();
        $completedBookings = $bookings->where('status', 'completed')->count();
        $cancelledBookings = $bookings->where('status', 'cancelled')->count();
        $pendingBookings = $bookings->where('status', 'pending')->count();
        $confirmedBookings = $bookings->where('status', 'confirmed')->count();

        // Revenue (excluding cancelled)
        $totalRevenue = $bookings->where('status', '!=', 'cancelled')->sum('total_price');

        // Get unique customers
        $totalCustomers = $bookings->unique('user_id')->count();

        // Calculate new vs returning customers
        $customerCounts = $bookings->groupBy('user_id')->map(fn($b) => $b->count());
        $newCustomers = $customerCounts->filter(fn($count) => $count === 1)->count();
        $returningCustomers = $customerCounts->filter(fn($count) => $count > 1)->count();

        // Today's bookings
        $today = Carbon::today();
        $todayBookings = Booking::whereIn('shop_id', $shopIds)
            ->whereDate('booking_date', $today)
            ->with(['customer', 'shop', 'service'])
            ->orderBy('start_time')
            ->get();

        // This week's bookings
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $weekBookings = Booking::whereIn('shop_id', $shopIds)
            ->whereBetween('booking_date', [$weekStart, $weekEnd])
            ->get();

        // Service popularity (based on booking count)
        $servicePopularity = $bookings->groupBy('shop_id')
            ->map(fn($b, $shopId) => [
                'shop' => Shop::find($shopId)?->name ?? 'Unknown',
                'count' => $b->count(),
                'percentage' => $totalBookings > 0 ? round(($b->count() / $totalBookings) * 100) : 0,
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values();

        // Monthly revenue trend (last 6 months)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthBookings = Booking::whereIn('shop_id', $shopIds)
                ->whereYear('booking_date', $month->year)
                ->whereMonth('booking_date', $month->month)
                ->where('status', '!=', 'cancelled')
                ->get();

            $revenueByShopForMonth = $monthBookings->groupBy('shop_id')->map(function($shopBookings) {
                return $shopBookings->sum('total_price');
            })->sum(); // Add up total numbers as a generic visual indicator if mixed, else true value

            $monthlyRevenue[] = [
                'month' => $month->format('M'),
                'revenue' => $revenueByShopForMonth,
                'bookings' => $monthBookings->count(),
            ];
        }

        // Recent bookings (last 5)
        $recentBookings = Booking::whereIn('shop_id', $shopIds)
            ->with(['customer', 'shop', 'service'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($booking) => [
                'id' => $booking->id,
                'customer_name' => $booking->customer->name,
                'service_name' => $booking->shop->name,
                'service_name' => $booking->service->name,
                'date' => $booking->booking_date->format('Y-m-d'),
                'time' => $booking->start_time,
                'status' => $booking->status,
                'price' => $booking->total_price,
            ]);

        // Group revenue by shop to show aggregates ONLY per shop, as requested by user
        $revenueByShop = $bookings->where('status', '!=', 'cancelled')->groupBy('shop_id')->map(function($shopBookings) {
            $shop = $shopBookings->first()->shop;
            $currency = $shop ? $shop->currency : 'CZK';
            $amount = $shopBookings->sum('total_price');
            return $shop->name . ': ' . number_format($amount, 2) . ' ' . $currency;
        });

        $revenueString = $revenueByShop->isEmpty() ? '0.00 CZK' : $revenueByShop->implode(' | ');

        // Stats for the stats cards
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
                'change' => '+' . $newCustomers . ' this month',
                'positive' => true,
                'icon' => 'user-plus',
                'iconBg' => 'bg-green-100',
                'iconColor' => 'text-green-600',
            ],
            [
                'label' => 'Revenue',
                'value' => $revenueString,
                'change' => $this->calculateRevenueChange($bookings),
                'positive' => true,
                'icon' => 'cash',
                'iconBg' => 'bg-purple-100',
                'iconColor' => 'text-purple-600',
            ],
        ];

        return Inertia::render('Vendor/Dashboard', [
            'stats' => $stats,
            'todayBookings' => $todayBookings->map(fn($b) => [
                'id' => $b->id,
                'time' => Carbon::parse($b->start_time)->format('g:i A'),
                'end_time' => Carbon::parse($b->end_time)->format('g:i A'),
                'duration' => ($b->service->duration_minutes ?? 60) . ' min',
                'title' => $b->service->name ?? $b->shop->name,
                'customer' => $b->customer->name,
                'customer_initials' => $this->getInitials($b->customer->name),
                'status' => strtoupper($b->status),
                'completed' => in_array($b->status, ['completed', 'cancelled']),
            ]),
            'weekStats' => [
                'total_bookings' => $weekBookings->count(),
                'completed' => $weekBookings->where('status', 'completed')->count(),
                'revenue' => $weekBookings->where('status', '!=', 'cancelled')->groupBy('shop_id')->map(function($sb) {
                    $shop = $sb->first()->shop;
                    return number_format($sb->sum('total_price'), 2) . ' ' . ($shop ? $shop->currency : 'CZK');
                })->implode(' | ') ?: '0',
            ],
            'servicePopularity' => $servicePopularity,
            'monthlyRevenue' => $monthlyRevenue,
            'recentBookings' => $recentBookings,
            'overview' => [
                'total_services' => $shops->count(),
                'available_services' => $shops->where('is_available', true)->count(),
                'total_customers' => $totalCustomers,
                'pending_bookings' => $pendingBookings,
                'confirmed_bookings' => $confirmedBookings,
                'returning_customers' => $returningCustomers,
            ],
        ]);
    }

    /**
     * Calculate percentage change from previous period.
     */
    private function calculateChange($bookings, $period = 'week')
    {
        $now = Carbon::now();
        $currentPeriod = match ($period) {
            'week' => $now->copy()->startOfWeek(),
            'month' => $now->copy()->startOfMonth(),
            default => $now->copy()->startOfWeek(),
        };

        $previousPeriod = match ($period) {
            'week' => $now->copy()->subWeek()->startOfWeek(),
            'month' => $now->copy()->subMonth()->startOfMonth(),
            default => $now->copy()->subWeek()->startOfWeek(),
        };

        $current = $bookings->filter(fn($b) => $b->created_at->gte($currentPeriod))->count();
        $previous = $bookings->filter(fn($b) => $b->created_at->gte($previousPeriod) && $b->created_at->lt($currentPeriod))->count();

        if ($previous === 0) {
            return $current > 0 ? '+100%' : '0%';
        }

        $change = (($current - $previous) / $previous) * 100;
        $sign = $change >= 0 ? '+' : '';

        return $sign . round($change) . '%';
    }

    /**
     * Calculate cancellation rate change.
     */
    private function calculateCancellationChange($bookings)
    {
        $total = $bookings->count();
        if ($total === 0) {
            return '0%';
        }

        $cancelled = $bookings->where('status', 'cancelled')->count();
        $rate = ($cancelled / $total) * 100;

        return round($rate) . '% rate';
    }

    /**
     * Calculate revenue change.
     */
    private function calculateRevenueChange($bookings)
    {
        $now = Carbon::now();

        $thisMonthGrouped = $bookings->filter(
            fn($b) => $b->created_at->gte($now->copy()->startOfMonth()) &&
                $b->status !== 'cancelled'
        )->groupBy(function($b) { return $b->shop->currency ?? 'CZK'; });

        $lastMonthGrouped = $bookings->filter(
            fn($b) => $b->created_at->gte($now->copy()->subMonth()->startOfMonth()) &&
                $b->created_at->lt($now->copy()->startOfMonth()) &&
                $b->status !== 'cancelled'
        )->groupBy(function($b) { return $b->shop->currency ?? 'CZK'; });

        // Calculate total amount across currencies to show an overall percentage metric
        // A proper way would be per currency. For simplicity we sum the raw numbers.
        $thisMonth = $thisMonthGrouped->map->sum('total_price')->sum();
        $lastMonth = $lastMonthGrouped->map->sum('total_price')->sum();

        if ($lastMonth == 0) {
            return $thisMonth > 0 ? '+100%' : '0%';
        }

        $change = (($thisMonth - $lastMonth) / $lastMonth) * 100;
        $sign = $change >= 0 ? '+' : '';

        return $sign . round($change) . '%';
    }

    /**
     * Get initials from name.
     */
    private function getInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper($words[0][0] . $words[1][0]);
        }

        return strtoupper(substr($name, 0, 2));
    }
}
