<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BusinessHour;
use App\Models\Category;
use App\Models\Service;
use App\Models\Shop;
use App\Services\ShopCoverImageService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

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
                $q->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('description', 'like', '%'.$request->q.'%');
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
        $totalServicesCount = $allServices->sum(fn ($s) => $s->services->count());
        $availableServices = $allServices->where('is_available', true)->count();

        // Calculate potential revenue (sum of all service prices) grouped by currency
        $potentialRevenueByCurrency = $allServices->flatMap(function ($shop) {
            return $shop->services->map(function ($service) use ($shop) {
                return ['currency' => $shop->currency ?? 'CZK', 'price' => $service->price];
            });
        })->groupBy('currency')->map(function ($items) {
            return $items->sum('price');
        });

        $potentialRevenueDisplay = [];
        foreach ($potentialRevenueByCurrency as $currency => $amount) {
            $potentialRevenueDisplay[] = Money::format($amount, $currency);
        }
        $potentialRevenueString = empty($potentialRevenueDisplay) ? '0.00 CZK' : implode(' + ', $potentialRevenueDisplay);

        return Inertia::render('Vendor/Shops/Index', [
            'shops' => $shops,
            'filters' => $request->only(['q', 'status', 'sort']),
            'stats' => [
                'total_shops' => $totalServices,
                'total_services' => $totalServicesCount,
                'available_shops' => $availableServices,
                'inactive_shops' => $totalServices - $availableServices,
                'potential_revenue' => $potentialRevenueString,
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
    public function store(Request $request, ShopCoverImageService $images)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'currency' => 'required|string|in:CZK,EUR',
            'description' => 'nullable|string',
            'price_range' => 'nullable|integer|min:1|max:4',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'is_online_only' => 'boolean',
            'is_available' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'business_hours' => 'nullable|array',
            'business_hours.*.day_of_week' => 'required|integer|between:0,6|distinct',
            'business_hours.*.is_closed' => 'sometimes|boolean',
            'business_hours.*.time_from' => 'nullable|date_format:H:i',
            'business_hours.*.time_to' => 'nullable|date_format:H:i',
        ]);

        $businessHoursData = $this->bookableBusinessHours($validated['business_hours'] ?? []);
        $image = $request->file('image');
        unset($validated['business_hours'], $validated['image']);

        $validated['user_id'] = $request->user()->id;
        $validated['slug'] = Str::slug($validated['name']);

        // Ensure unique slug
        $counter = 1;
        $originalSlug = $validated['slug'];
        while (Shop::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug.'-'.$counter++;
        }

        $storedPath = null;
        try {
            $shop = DB::transaction(function () use ($businessHoursData, $image, $images, &$storedPath, $validated): Shop {
                $shop = Shop::create($validated);
                if ($image) {
                    $storedPath = $images->store($shop, $image);
                    $shop->update(['image' => $storedPath]);
                }

                foreach ($businessHoursData as $hour) {
                    BusinessHour::create([
                        'shop_id' => $shop->id,
                        'day_of_week' => $hour['day_of_week'],
                        'time_from' => $hour['time_from'],
                        'time_to' => $hour['time_to'],
                    ]);
                }

                return $shop;
            });
        } catch (\Throwable $exception) {
            $images->delete($storedPath);
            throw $exception;
        }

        return redirect()->route('vendor.shops.show', $shop->id)
            ->with('success', __('Shop created successfully. Now add your services.'));
    }

    /**
     * Show the detailed dashboard for a shop.
     */
    public function show(Request $request, int $id): Response
    {
        $user = $request->user();

        $shop = Shop::with(['category', 'services', 'businessHours'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $categories = Category::all();

        // Get booking stats for this shop
        $bookings = Booking::where('shop_id', $id)->get();
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
    public function update(Request $request, int $id, ShopCoverImageService $images)
    {
        $user = $request->user();

        $shop = Shop::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'currency' => 'required|string|in:CZK,EUR',
            'description' => 'nullable|string',
            'price_range' => 'nullable|integer|min:1|max:4',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'is_online_only' => 'boolean',
            'is_available' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_image' => 'sometimes|boolean',
            'business_hours' => 'nullable|array',
            'business_hours.*.day_of_week' => 'required|integer|between:0,6|distinct',
            'business_hours.*.is_closed' => 'sometimes|boolean',
            'business_hours.*.time_from' => 'nullable|date_format:H:i',
            'business_hours.*.time_to' => 'nullable|date_format:H:i',
        ]);

        $businessHoursData = $this->bookableBusinessHours($validated['business_hours'] ?? []);
        $image = $request->file('image');
        $removeImage = (bool) ($validated['remove_image'] ?? false);
        unset($validated['business_hours'], $validated['image'], $validated['remove_image']);

        // Update slug if name changed
        if ($shop->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
            $counter = 1;
            $originalSlug = $validated['slug'];
            while (Shop::where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
                $validated['slug'] = $originalSlug.'-'.$counter++;
            }
        }

        $oldPath = $shop->image;
        $storedPath = $image ? $images->store($shop, $image) : null;
        if ($storedPath || $removeImage) {
            $validated['image'] = $storedPath;
        }

        try {
            DB::transaction(function () use ($businessHoursData, $shop, $validated): void {
                $shop->update($validated);
                $shop->businessHours()->delete();
                foreach ($businessHoursData as $hour) {
                    BusinessHour::create([
                        'shop_id' => $shop->id,
                        'day_of_week' => $hour['day_of_week'],
                        'time_from' => $hour['time_from'],
                        'time_to' => $hour['time_to'],
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            $images->delete($storedPath);
            throw $exception;
        }

        if (($storedPath || $removeImage) && $oldPath !== $storedPath) {
            $images->delete($oldPath);
        }

        return back()->with('success', __('Shop updated successfully.'));
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Request $request, int $id, ShopCoverImageService $images)
    {
        $user = $request->user();

        $shop = Shop::where('user_id', $user->id)->findOrFail($id);

        $imagePath = $shop->image;
        $shop->services()->delete();
        $shop->delete();
        $images->delete($imagePath);

        return redirect()->route('vendor.shops.index')
            ->with('success', __('Shop deleted successfully.'));
    }

    /**
     * Store a new service for a shop.
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
            'is_available' => 'sometimes|boolean',
        ]);

        $validated['shop_id'] = $shop->id;

        Service::create($validated);

        return back()->with('success', __('Service added successfully.'));
    }

    /**
     * Update a service.
     */
    public function updateService(Request $request, int $shopId, int $serviceId)
    {
        $user = $request->user();

        $shop = Shop::where('user_id', $user->id)->findOrFail($shopId);

        $service = Service::where('shop_id', $shop->id)->findOrFail($serviceId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'is_popular' => 'boolean',
            'category_tag' => 'nullable|string|max:100',
            'staff_level' => 'nullable|string|max:100',
            'is_available' => 'sometimes|boolean',
        ]);

        $service->update($validated);

        return back()->with('success', __('Service updated successfully.'));
    }

    /**
     * Delete a service.
     */
    public function destroyService(Request $request, int $shopId, int $serviceId)
    {
        $user = $request->user();

        $shop = Shop::where('user_id', $user->id)->findOrFail($shopId);

        $service = Service::where('shop_id', $shop->id)->findOrFail($serviceId);

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

        $shop->update(['is_available' => ! $shop->is_available]);

        $status = $shop->is_available ? 'available' : 'unavailable';

        return back()->with('success', $status === 'available' ? __('Shop is now active.') : __('Shop is now inactive.'));
    }

    /**
     * Store / sync business hours for a shop.
     */
    public function storeBusinessHours(Request $request, int $shopId)
    {
        $user = $request->user();
        $shop = Shop::where('user_id', $user->id)->findOrFail($shopId);

        $validated = $request->validate([
            'hours' => 'present|array',
            'hours.*.day_of_week' => 'required|integer|between:0,6|distinct',
            'hours.*.is_closed' => 'sometimes|boolean',
            'hours.*.time_from' => 'nullable|date_format:H:i',
            'hours.*.time_to' => 'nullable|date_format:H:i',
        ]);

        $hours = $this->bookableBusinessHours($validated['hours'], 'hours');

        DB::transaction(function () use ($hours, $shop): void {
            $shop->businessHours()->delete();

            foreach ($hours as $hour) {
                BusinessHour::create([
                    'shop_id' => $shop->id,
                    'day_of_week' => $hour['day_of_week'],
                    'time_from' => $hour['time_from'],
                    'time_to' => $hour['time_to'],
                ]);
            }
        });

        return back()->with('success', __('Business hours updated successfully.'));
    }

    /**
     * Closed days are represented by the absence of a business-hour row.
     *
     * @param  array<int, array<string, mixed>>  $hours
     * @return array<int, array{day_of_week: int, time_from: string, time_to: string}>
     */
    private function bookableBusinessHours(array $hours, string $errorKey = 'business_hours'): array
    {
        $normalized = [];

        foreach ($hours as $index => $hour) {
            if ((bool) ($hour['is_closed'] ?? false)) {
                continue;
            }

            $from = $hour['time_from'] ?? null;
            $to = $hour['time_to'] ?? null;
            if (! $from || ! $to || $to <= $from) {
                throw ValidationException::withMessages([
                    "{$errorKey}.{$index}.time_to" => __('Closing time must be after opening time.'),
                ]);
            }

            $normalized[] = [
                'day_of_week' => (int) $hour['day_of_week'],
                'time_from' => $from,
                'time_to' => $to,
            ];
        }

        return $normalized;
    }
}
