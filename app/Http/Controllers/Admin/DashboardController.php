<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(Request $request): Response
    {
        // Verify admin access
        if (!$request->user()->is_admin) {
            abort(403, 'Unauthorized');
        }

        // Key metrics
        $metrics = [
            'total_users' => User::count(),
            'total_services' => Service::count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => Booking::where('status', '!=', 'cancelled')->sum('total_price'),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'active_vendors' => User::where('is_service_provider', true)->count(),
        ];

        // Recent activity
        $recentBookings = Booking::with(['customer', 'service', 'provider'])
            ->latest()
            ->limit(10)
            ->get();

        // Revenue chart data (last 30 days)
        $revenueData = $this->getRevenueChartData();

        // User growth data
        $userGrowthData = $this->getUserGrowthData();

        // Top services
        $topServices = Service::withCount('bookings')
            ->withSum('bookings as revenue', 'total_price')
            ->orderBy('bookings_count', 'desc')
            ->limit(10)
            ->get();

        // Top categories
        $topCategories = Category::withCount('services')
            ->with(['services' => function ($query) {
                $query->withCount('bookings');
            }])
            ->orderBy('services_count', 'desc')
            ->get();

        // Recent reviews
        $recentReviews = Review::with(['user', 'service'])
            ->latest()
            ->limit(5)
            ->get();

        // Platform health
        $platformHealth = [
            'avg_response_time' => $this->calculateAvgResponseTime(),
            'completion_rate' => $this->calculateCompletionRate(),
            'satisfaction_rate' => $this->calculateSatisfactionRate(),
            'dispute_rate' => $this->calculateDisputeRate(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'metrics' => $metrics,
            'recentBookings' => $recentBookings,
            'revenueData' => $revenueData,
            'userGrowthData' => $userGrowthData,
            'topServices' => $topServices,
            'topCategories' => $topCategories,
            'recentReviews' => $recentReviews,
            'platformHealth' => $platformHealth,
        ]);
    }

    /**
     * Get revenue chart data for the last 30 days.
     */
    private function getRevenueChartData(): array
    {
        $data = [];
        $labels = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Booking::where('status', '!=', 'cancelled')
                ->whereDate('created_at', $date)
                ->sum('total_price');
            
            $labels[] = $date->format('M d');
            $data[] = (float) $revenue;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get user growth data for the last 30 days.
     */
    private function getUserGrowthData(): array
    {
        $data = [];
        $labels = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = User::whereDate('created_at', '<=', $date)->count();
            
            $labels[] = $date->format('M d');
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Calculate average response time (placeholder).
     */
    private function calculateAvgResponseTime(): string
    {
        // This would calculate based on message response times
        return '< 1 hour';
    }

    /**
     * Calculate booking completion rate.
     */
    private function calculateCompletionRate(): float
    {
        $total = Booking::count();
        if ($total === 0) return 0;
        
        $completed = Booking::where('status', 'completed')->count();
        return round(($completed / $total) * 100, 1);
    }

    /**
     * Calculate customer satisfaction rate.
     */
    private function calculateSatisfactionRate(): float
    {
        $avgRating = Review::avg('rating');
        return $avgRating ? round(($avgRating / 5) * 100, 1) : 0;
    }

    /**
     * Calculate dispute rate (placeholder).
     */
    private function calculateDisputeRate(): float
    {
        // This would calculate based on disputed bookings
        return 0.5;
    }
}
