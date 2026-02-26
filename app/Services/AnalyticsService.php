<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Review;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get vendor dashboard analytics.
     */
    public function getVendorAnalytics(int $userId, string $period = '30_days'): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'overview' => $this->getOverviewStats($userId, $dateRange),
            'revenue' => $this->getRevenueData($userId, $dateRange),
            'bookings' => $this->getBookingsData($userId, $dateRange),
            'services' => $this->getTopServices($userId, $dateRange),
            'customers' => $this->getCustomerStats($userId, $dateRange),
            'ratings' => $this->getRatingStats($userId),
        ];
    }

    /**
     * Get overview statistics.
     */
    private function getOverviewStats(int $userId, array $dateRange): array
    {
        $serviceIds = Service::where('user_id', $userId)->pluck('id');

        $currentRevenue = Booking::whereIn('service_id', $serviceIds)
            ->whereBetween('booking_date', $dateRange)
            ->where('status', '!=', 'cancelled')
            ->sum('total_price');

        $previousRevenue = Booking::whereIn('service_id', $serviceIds)
            ->whereBetween('booking_date', $this->getPreviousPeriod($dateRange))
            ->where('status', '!=', 'cancelled')
            ->sum('total_price');

        $currentBookings = Booking::whereIn('service_id', $serviceIds)
            ->whereBetween('booking_date', $dateRange)
            ->count();

        $previousBookings = Booking::whereIn('service_id', $serviceIds)
            ->whereBetween('booking_date', $this->getPreviousPeriod($dateRange))
            ->count();

        return [
            'revenue' => [
                'current' => $currentRevenue,
                'previous' => $previousRevenue,
                'change' => $this->calculatePercentageChange($currentRevenue, $previousRevenue),
            ],
            'bookings' => [
                'current' => $currentBookings,
                'previous' => $previousBookings,
                'change' => $this->calculatePercentageChange($currentBookings, $previousBookings),
            ],
            'services' => Service::where('user_id', $userId)->count(),
            'rating' => Review::whereIn('service_id', $serviceIds)->avg('rating') ?? 0,
        ];
    }

    /**
     * Get revenue chart data.
     */
    private function getRevenueData(int $userId, array $dateRange): array
    {
        $serviceIds = Service::where('user_id', $userId)->pluck('id');

        $bookings = Booking::whereIn('service_id', $serviceIds)
            ->whereBetween('booking_date', $dateRange)
            ->where('status', '!=', 'cancelled')
            ->select(
                DB::raw('DATE(booking_date) as date'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $period = CarbonPeriod::create($dateRange['start'], $dateRange['end']);

        $labels = [];
        $revenue = [];
        $counts = [];

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->format('M d');
            $revenue[] = $bookings[$dateStr]->revenue ?? 0;
            $counts[] = $bookings[$dateStr]->count ?? 0;
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'counts' => $counts,
        ];
    }

    /**
     * Get bookings data by status.
     */
    private function getBookingsData(int $userId, array $dateRange): array
    {
        $serviceIds = Service::where('user_id', $userId)->pluck('id');

        $statusCounts = Booking::whereIn('service_id', $serviceIds)
            ->whereBetween('booking_date', $dateRange)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'labels' => ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
            'data' => [
                $statusCounts['pending'] ?? 0,
                $statusCounts['confirmed'] ?? 0,
                $statusCounts['completed'] ?? 0,
                $statusCounts['cancelled'] ?? 0,
            ],
            'colors' => ['#FCD34D', '#60A5FA', '#34D399', '#F87171'],
        ];
    }

    /**
     * Get top performing services.
     */
    private function getTopServices(int $userId, array $dateRange): array
    {
        return Service::where('user_id', $userId)
            ->withCount(['bookings' => function ($query) use ($dateRange) {
                $query->whereBetween('booking_date', $dateRange);
            }])
            ->withSum(['bookings as revenue' => function ($query) use ($dateRange) {
                $query->whereBetween('booking_date', $dateRange)
                    ->where('status', '!=', 'cancelled');
            }], 'total_price')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn ($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'bookings' => $service->bookings_count,
                'revenue' => $service->revenue ?? 0,
                'rating' => $service->rating,
            ])
            ->toArray();
    }

    /**
     * Get customer statistics.
     */
    private function getCustomerStats(int $userId, array $dateRange): array
    {
        $serviceIds = Service::where('user_id', $userId)->pluck('id');

        $totalCustomers = Booking::whereIn('service_id', $serviceIds)
            ->whereBetween('booking_date', $dateRange)
            ->distinct('user_id')
            ->count('user_id');

        $returningCustomers = Booking::whereIn('service_id', $serviceIds)
            ->whereBetween('booking_date', $dateRange)
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        return [
            'total' => $totalCustomers,
            'new' => $totalCustomers - $returningCustomers,
            'returning' => $returningCustomers,
        ];
    }

    /**
     * Get rating statistics.
     */
    private function getRatingStats(int $userId): array
    {
        $serviceIds = Service::where('user_id', $userId)->pluck('id');

        $ratingDistribution = Review::whereIn('service_id', $serviceIds)
            ->approved()
            ->select('rating', DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $ratingDistribution[$i] ?? 0;
            $distribution[] = [
                'stars' => $i,
                'count' => $count,
            ];
        }

        return [
            'average' => Review::whereIn('service_id', $serviceIds)->approved()->avg('rating') ?? 0,
            'total' => Review::whereIn('service_id', $serviceIds)->approved()->count(),
            'distribution' => $distribution,
        ];
    }

    /**
     * Get date range based on period.
     */
    private function getDateRange(string $period): array
    {
        $end = Carbon::now()->endOfDay();
        
        $start = match($period) {
            '7_days' => Carbon::now()->subDays(7)->startOfDay(),
            '30_days' => Carbon::now()->subDays(30)->startOfDay(),
            '90_days' => Carbon::now()->subDays(90)->startOfDay(),
            'this_month' => Carbon::now()->startOfMonth(),
            'last_month' => Carbon::now()->subMonth()->startOfMonth(),
            'this_year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->subDays(30)->startOfDay(),
        };

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Get previous period for comparison.
     */
    private function getPreviousPeriod(array $currentRange): array
    {
        $days = $currentRange['start']->diffInDays($currentRange['end']);
        
        return [
            'start' => $currentRange['start']->copy()->subDays($days),
            'end' => $currentRange['start']->copy()->subDay(),
        ];
    }

    /**
     * Calculate percentage change.
     */
    private function calculatePercentageChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
