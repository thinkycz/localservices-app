<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use App\Models\Category;
use App\Models\Shop;
// use App\Models\Service;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class ShopsController extends Controller
{
    /**
     * Display a list of the vendor's services.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Shop::with(['category', 'services'])
            ->where('user_id', $user->id);

        // Search functionality
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }

        // Filter by availability
        if ($request->filled('status')) {
            if ($request->status === 'available') {
                $query->where('is_available', true);
            } elseif ($request->status === 'unavailable') {
                $query->where('is_available', false);
            }
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->latest(),
        };

        $shops = $query->paginate(10)->withQueryString();

        // Calculate stats
        $allServices = Shop::where('user_id', $user->id)->with('services')->get();
        $totalServices = $allServices->count();
        $totalServicesCount = $allServices->sum(fn($s) => $s->services->count());
        $availableServices = $allServices->where('is_available', true)->count();

        // Calculate potential revenue (sum of all service prices)
        $potentialRevenue = $allServices->flatMap(fn($s) => $s->services)->sum('price');

        return Inertia::render('Vendor/Shops/Index', [
            'shops' => $shops,
            'filters' => $request->only(['q', 'status', 'sort']),
            'stats' => [
                'total_shops' => $totalServices,
                'total_services' => $totalServicesCount,
                'available_shops' => $availableServices,
                'inactive_shops' => $totalServices - $availableServices,
                'potential_revenue' => $potentialRevenue,
            ],
        ]);
    }

    /**
     * Show the form for creating a new service.
     */
    public function create(): Response
    {
        $categories = Category::all();

        return Inertia::render('Vendor/Shops/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created service.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price_range' => 'nullable|integer|min:1|max:4',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'is_online_only' => 'boolean',
            'is_available' => 'boolean',
            'business_hours' => 'nullable|array',
            'business_hours.*.day_of_week' => 'required|integer|between:0,6',
            'business_hours.*.time_from' => 'required|string',
            'business_hours.*.time_to' => 'required|string',
        ]);

        $businessHoursData = $validated['business_hours'] ?? [];
        unset($validated['business_hours']);

        $validated['user_id'] = $request->user()->id;
        $validated['slug'] = Str::slug($validated['name']);

        // Ensure unique slug
        $counter = 1;
        $originalSlug = $validated['slug'];
        while (Shop::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter++;
        }

        $shop = Shop::create($validated);

        // Save business hours
        foreach ($businessHoursData as $hour) {
            BusinessHour::create([
                'service_id' => $shop->id,
                'day_of_week' => $hour['day_of_week'],
                'time_from' => $hour['time_from'],
                'time_to' => $hour['time_to'],
            ]);
        }

        return redirect()->route('vendor.shops.show', $shop->id)
            ->with('success', __('Shop created successfully. Now add your services.'));
    }

    /**
     * Show the detailed dashboard for a service.
     */
    public function show(Request $request, int $id): Response
    {
        $user = $request->user();

        $shop = Shop::with(['category', 'services', 'businessHours'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $categories = Category::all();

        // Get booking stats for this service
        $bookings = \App\Models\Booking::where('service_id', $id)->get();
        $stats = [
            'total_bookings' => $bookings->count(),
            'completed_bookings' => $bookings->where('status', 'completed')->count(),
            'cancelled_bookings' => $bookings->where('status', 'cancelled')->count(),
            'total_revenue' => $bookings->where('status', '!=', 'cancelled')->sum('total_price'),
        ];

        return Inertia::render('Vendor/Shops/Show', [
            'shop' => $shop,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing a service.
     */
    public function edit(Request $request, int $id): Response
    {
        $user = $request->user();

        $shop = Shop::with(['category', 'businessHours'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $categories = Category::all();

        return Inertia::render('Vendor/Shops/Edit', [
            'shop' => $shop,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified service.
     */
    public function update(Request $request, int $id)
    {
        $user = $request->user();

        $shop = Shop::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price_range' => 'nullable|integer|min:1|max:4',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'is_online_only' => 'boolean',
            'is_available' => 'boolean',
            'image' => 'nullable|string|max:500',
            'business_hours' => 'nullable|array',
            'business_hours.*.day_of_week' => 'required|integer|between:0,6',
            'business_hours.*.time_from' => 'required|string',
            'business_hours.*.time_to' => 'required|string',
        ]);

        $businessHoursData = $validated['business_hours'] ?? [];
        unset($validated['business_hours']);

        // Update slug if name changed
        if ($shop->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
            $counter = 1;
            $originalSlug = $validated['slug'];
            while (Shop::where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }

        $shop->update($validated);

        // Sync business hours
        $shop->businessHours()->delete();
        foreach ($businessHoursData as $hour) {
            BusinessHour::create([
                'service_id' => $shop->id,
                'day_of_week' => $hour['day_of_week'],
                'time_from' => $hour['time_from'],
                'time_to' => $hour['time_to'],
            ]);
        }

        return back()->with('success', __('Shop updated successfully.'));
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();

        $shop = Shop::where('user_id', $user->id)->findOrFail($id);

        // Delete associated offerings first
        $shop->services()->delete();
        $shop->delete();

        return redirect()->route('vendor.shops.index')
            ->with('success', __('Shop deleted successfully.'));
    }

    /**
     * Store a new offering for a service.
     */
    public function storeService(Request $request, int $shopId)
    {
        $user = $request->user();

        $shop = Shop::where('user_id', $user->id)->findOrFail($shopId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'is_popular' => 'boolean',
            'category_tag' => 'nullable|string|max:100',
            'staff_level' => 'nullable|string|max:100',
        ]);

        $validated['service_id'] = $shop->id;

        Service::create($validated);

        return back()->with('success', __('Service added successfully.'));
    }

    /**
     * Update an offering.
     */
    public function updateService(Request $request, int $shopId, int $serviceId)
    {
        $user = $request->user();

        $shop = Shop::where('user_id', $user->id)->findOrFail($shopId);

        $service = Service::where('service_id', $shop->id)->findOrFail($serviceId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'is_popular' => 'boolean',
            'category_tag' => 'nullable|string|max:100',
            'staff_level' => 'nullable|string|max:100',
        ]);

        $service->update($validated);

        return back()->with('success', __('Service updated successfully.'));
    }

    /**
     * Delete an offering.
     */
    public function destroyService(Request $request, int $shopId, int $serviceId)
    {
        $user = $request->user();

        $shop = Shop::where('user_id', $user->id)->findOrFail($shopId);

        $service = Service::where('service_id', $shop->id)->findOrFail($serviceId);

        $service->delete();

        return back()->with('success', __('Service deleted successfully.'));
    }

    /**
     * Toggle service availability.
     */
    public function toggleAvailability(Request $request, int $id)
    {
        $user = $request->user();

        $shop = Shop::where('user_id', $user->id)->findOrFail($id);

        $shop->update(['is_available' => !$shop->is_available]);

        $status = $shop->is_available ? 'available' : 'unavailable';

        return back()->with('success', $status === 'active' ? __('Service is now active.') : __('Service is now inactive.'));
    }

    /**
     * Store / sync business hours for a service.
     */
    public function storeBusinessHours(Request $request, int $shopId)
    {
        $user = $request->user();
        $shop = Shop::where('user_id', $user->id)->findOrFail($shopId);

        $validated = $request->validate([
            'hours' => 'present|array',
            'hours.*.day_of_week' => 'required|integer|between:0,6',
            'hours.*.time_from' => 'required|string|date_format:H:i',
            'hours.*.time_to' => 'required|string|date_format:H:i|after:hours.*.time_from',
        ]);

        // Delete existing and re-create
        $shop->businessHours()->delete();

        foreach ($validated['hours'] as $hour) {
            BusinessHour::create([
                'service_id' => $shop->id,
                'day_of_week' => $hour['day_of_week'],
                'time_from' => $hour['time_from'],
                'time_to' => $hour['time_to'],
            ]);
        }

        return back()->with('success', __('Business hours updated successfully.'));
    }
}
