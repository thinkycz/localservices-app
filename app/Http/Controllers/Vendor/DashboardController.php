<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
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
        $services = Service::where('user_id', $user->id)->get();
        $serviceIds = $services->pluck('id');

        // Get all bookings for vendor's services
        $bookings = Booking::whereIn('service_id', $serviceIds)->get();

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
        $customerCounts = $bookings->groupBy('user_id')->map(fn ($b) => $b->count());
        $newCustomers = $customerCounts->filter(fn ($count) => $count === 1)->count();
        $returningCustomers = $customerCounts->filter(fn ($count) => $count > 1)->count();

        // Today's bookings
        $today = Carbon::today();
        $todayBookings = Booking::whereIn('service_id', $serviceIds)
            ->whereDate('booking_date', $today)
            ->with(['customer', 'service', 'offering'])
            ->orderBy('start_time')
            ->get();

        // This week's bookings
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $weekBookings = Booking::whereIn('service_id', $serviceIds)
            ->whereBetween('booking_date', [$weekStart, $weekEnd])
            ->get();

        // Service popularity (based on booking count)
        $servicePopularity = $bookings->groupBy('service_id')
            ->map(fn ($b, $serviceId) => [
                'service' => Service::find($serviceId)?->name ?? 'Unknown',
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
            $monthBookings = Booking::whereIn('service_id', $serviceIds)
                ->whereYear('booking_date', $month->year)
                ->whereMonth('booking_date', $month->month)
                ->where('status', '!=', 'cancelled')
                ->get();

            $monthlyRevenue[] = [
                'month' => $month->format('M'),
                'revenue' => $monthBookings->sum('total_price'),
                'bookings' => $monthBookings->count(),
            ];
        }

        // Recent bookings (last 5)
        $recentBookings = Booking::whereIn('service_id', $serviceIds)
            ->with(['customer', 'service', 'offering'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($booking) => [
                'id' => $booking->id,
                'customer_name' => $booking->customer->name,
                'service_name' => $booking->service->name,
                'offering_name' => $booking->offering->name,
                'date' => $booking->booking_date->format('Y-m-d'),
                'time' => $booking->start_time,
                'status' => $booking->status,
                'price' => $booking->total_price,
            ]);

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
                'change' => '+'.$newCustomers.' this month',
                'positive' => true,
                'icon' => 'user-plus',
                'iconBg' => 'bg-green-100',
                'iconColor' => 'text-green-600',
            ],
            [
                'label' => 'Revenue',
                'value' => '$'.number_format($totalRevenue, 2),
                'change' => $this->calculateRevenueChange($bookings),
                'positive' => true,
                'icon' => 'cash',
                'iconBg' => 'bg-purple-100',
                'iconColor' => 'text-purple-600',
            ],
        ];

        return Inertia::render('Vendor/Dashboard', [
            'stats' => $stats,
            'todayBookings' => $todayBookings->map(fn ($b) => [
                'id' => $b->id,
                'time' => Carbon::parse($b->start_time)->format('g:i A'),
                'end_time' => Carbon::parse($b->end_time)->format('g:i A'),
                'duration' => ($b->offering->duration_minutes ?? 60).' min',
                'title' => $b->offering->name ?? $b->service->name,
                'customer' => $b->customer->name,
                'customer_initials' => $this->getInitials($b->customer->name),
                'status' => strtoupper($b->status),
                'completed' => in_array($b->status, ['completed', 'cancelled']),
            ]),
            'weekStats' => [
                'total_bookings' => $weekBookings->count(),
                'completed' => $weekBookings->where('status', 'completed')->count(),
                'revenue' => $weekBookings->where('status', '!=', 'cancelled')->sum('total_price'),
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

        $current = $bookings->filter(fn ($b) => $b->created_at->gte($currentPeriod))->count();
        $previous = $bookings->filter(fn ($b) => $b->created_at->gte($previousPeriod) && $b->created_at->lt($currentPeriod))->count();

        if ($previous === 0) {
            return $current > 0 ? '+100%' : '0%';
        }

        $change = (($current - $previous) / $previous) * 100;
        $sign = $change >= 0 ? '+' : '';

        return $sign.round($change).'%';
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

        return round($rate).'% rate';
    }

    /**
     * Calculate revenue change.
     */
    private function calculateRevenueChange($bookings)
    {
        $now = Carbon::now();
        $thisMonth = $bookings->filter(fn ($b) => $b->created_at->gte($now->copy()->startOfMonth()) &&
            $b->status !== 'cancelled'
        )->sum('total_price');

        $lastMonth = $bookings->filter(fn ($b) => $b->created_at->gte($now->copy()->subMonth()->startOfMonth()) &&
            $b->created_at->lt($now->copy()->startOfMonth()) &&
            $b->status !== 'cancelled'
        )->sum('total_price');

        if ($lastMonth === 0) {
            return $thisMonth > 0 ? '+100%' : '0%';
        }

        $change = (($thisMonth - $lastMonth) / $lastMonth) * 100;
        $sign = $change >= 0 ? '+' : '';

        return $sign.round($change).'%';
    }

    /**
     * Get initials from name.
     */
    private function getInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper($words[0][0].$words[1][0]);
        }

        return strtoupper(substr($name, 0, 2));
    }
}
