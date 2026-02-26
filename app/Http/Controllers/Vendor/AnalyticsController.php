<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    /**
     * Display vendor analytics dashboard.
     */
    public function index(Request $request): Response
    {
        $period = $request->get('period', '30_days');
        
        $analytics = $this->analyticsService->getVendorAnalytics(
            $request->user()->id,
            $period
        );

        return Inertia::render('Vendor/Analytics', [
            'analytics' => $analytics,
            'currentPeriod' => $period,
        ]);
    }
}
