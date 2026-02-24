<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceOffering;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class VendorServicesController extends Controller
{
    /**
     * Display a list of the vendor's services.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Service::with(['category', 'offerings'])
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

        $services = $query->paginate(10)->withQueryString();

        // Calculate stats
        $allServices = Service::where('user_id', $user->id)->with('offerings')->get();
        $totalServices = $allServices->count();
        $totalOfferings = $allServices->sum(fn($s) => $s->offerings->count());
        $availableServices = $allServices->where('is_available', true)->count();

        // Calculate potential revenue (sum of all offering prices)
        $potentialRevenue = $allServices->flatMap(fn($s) => $s->offerings)->sum('price');

        return Inertia::render('Vendor/Services/Index', [
            'services' => $services,
            'filters' => $request->only(['q', 'status', 'sort']),
            'stats' => [
                'total_services' => $totalServices,
                'total_offerings' => $totalOfferings,
                'available_services' => $availableServices,
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

        return Inertia::render('Vendor/Services/Create', [
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
            'badge' => 'nullable|string|max:100',
            'badge_color' => 'nullable|in:blue,gray,green',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'opening_hours' => 'nullable|string|max:500',
            'is_available' => 'boolean',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['slug'] = Str::slug($validated['name']);

        // Ensure unique slug
        $counter = 1;
        $originalSlug = $validated['slug'];
        while (Service::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug.'-'.$counter++;
        }

        $service = Service::create($validated);

        return redirect()->route('vendor.services.edit', $service->id)
            ->with('success', 'Service created successfully. Now add your service offerings.');
    }

    /**
     * Show the form for editing a service.
     */
    public function edit(Request $request, int $id): Response
    {
        $user = $request->user();

        $service = Service::with(['category', 'offerings'])
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

        return Inertia::render('Vendor/Services/Edit', [
            'service' => $service,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    /**
     * Update the specified service.
     */
    public function update(Request $request, int $id)
    {
        $user = $request->user();

        $service = Service::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price_range' => 'nullable|integer|min:1|max:4',
            'badge' => 'nullable|string|max:100',
            'badge_color' => 'nullable|in:blue,gray,green',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'opening_hours' => 'nullable|string|max:500',
            'is_available' => 'boolean',
            'image' => 'nullable|string|max:500',
        ]);

        // Update slug if name changed
        if ($service->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
            $counter = 1;
            $originalSlug = $validated['slug'];
            while (Service::where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
                $validated['slug'] = $originalSlug.'-'.$counter++;
            }
        }

        $service->update($validated);

        return back()->with('success', 'Service updated successfully.');
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();

        $service = Service::where('user_id', $user->id)->findOrFail($id);

        // Delete associated offerings first
        $service->offerings()->delete();
        $service->delete();

        return redirect()->route('vendor.services.index')
            ->with('success', 'Service deleted successfully.');
    }

    /**
     * Store a new offering for a service.
     */
    public function storeOffering(Request $request, int $serviceId)
    {
        $user = $request->user();

        $service = Service::where('user_id', $user->id)->findOrFail($serviceId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'is_popular' => 'boolean',
            'category_tag' => 'nullable|string|max:100',
            'staff_level' => 'nullable|string|max:100',
        ]);

        $validated['service_id'] = $service->id;

        ServiceOffering::create($validated);

        return back()->with('success', 'Service offering added successfully.');
    }

    /**
     * Update an offering.
     */
    public function updateOffering(Request $request, int $serviceId, int $offeringId)
    {
        $user = $request->user();

        $service = Service::where('user_id', $user->id)->findOrFail($serviceId);

        $offering = ServiceOffering::where('service_id', $service->id)->findOrFail($offeringId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'is_popular' => 'boolean',
            'category_tag' => 'nullable|string|max:100',
            'staff_level' => 'nullable|string|max:100',
        ]);

        $offering->update($validated);

        return back()->with('success', 'Service offering updated successfully.');
    }

    /**
     * Delete an offering.
     */
    public function destroyOffering(Request $request, int $serviceId, int $offeringId)
    {
        $user = $request->user();

        $service = Service::where('user_id', $user->id)->findOrFail($serviceId);

        $offering = ServiceOffering::where('service_id', $service->id)->findOrFail($offeringId);

        $offering->delete();

        return back()->with('success', 'Service offering deleted successfully.');
    }

    /**
     * Toggle service availability.
     */
    public function toggleAvailability(Request $request, int $id)
    {
        $user = $request->user();

        $service = Service::where('user_id', $user->id)->findOrFail($id);

        $service->update(['is_available' => !$service->is_available]);

        $status = $service->is_available ? 'available' : 'unavailable';

        return back()->with('success', "Service is now {$status}.");
    }
}

