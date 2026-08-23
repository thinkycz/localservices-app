<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Models\Shop;
use App\Services\BookingAvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function availability(
        Request $request,
        Shop $shop,
        BookingAvailabilityService $availability,
    ): JsonResponse {
        $validated = $request->validate([
            'service_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
        $service = Service::where('shop_id', $shop->id)->findOrFail($validated['service_id']);
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $validated['date'], $shop->timezone);

        return response()->json([
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'date' => $validated['date'],
            ...$availability->forDate($shop, $service, $date),
        ]);
    }

    public function index(Request $request): Response
    {
        $query = Shop::with('category')->where('is_available', true);

        // Search by keyword
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('description', 'like', '%'.$request->q.'%')
                    ->orWhere('city', 'like', '%'.$request->q.'%')
                    ->orWhere('address', 'like', '%'.$request->q.'%')
                    ->orWhereHas('category', fn ($category) => $category
                        ->where('name', 'like', '%'.$request->q.'%'))
                    ->orWhereHas('services', fn ($service) => $service
                        ->where('is_available', true)
                        ->where(function ($serviceQuery) use ($request) {
                            $serviceQuery->where('name', 'like', '%'.$request->q.'%')
                                ->orWhere('description', 'like', '%'.$request->q.'%');
                        }));
            });
        }

        // Filter by category slugs (array)
        if ($request->filled('categories')) {
            $categorySlugs = is_array($request->categories)
                ? $request->categories
                : explode(',', $request->categories);

            $query->whereHas('category', function ($q) use ($categorySlugs) {
                $q->whereIn('slug', $categorySlugs);
            });
        }

        // Filter by price range (array of integers 1-4)
        if ($request->filled('price_range')) {
            $priceRanges = is_array($request->price_range)
                ? $request->price_range
                : explode(',', $request->price_range);

            $query->whereIn('price_range', array_map('intval', $priceRanges));
        }

        // Filter by minimum rating
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', (float) $request->min_rating);
        }

        // Filter by location (city/state)
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Sort
        $sort = $request->get('sort', 'recommended');
        match ($sort) {
            'cheapest' => $query->orderBy('price_range', 'asc'),
            default => $query->orderBy('rating', 'desc'),
        };

        $shops = $query->paginate(20)->withQueryString();

        $categories = Category::withCount([
            'shops' => fn ($query) => $query->where('is_available', true),
        ])->get();

        return Inertia::render('Shops/Index', [
            'shops' => $shops,
            'categories' => $categories,
            'filters' => $request->only(['q', 'categories', 'price_range', 'min_rating', 'sort', 'city']),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $shop = Shop::with([
            'category',
            'services' => fn ($query) => $query->where('is_available', true),
            'businessHours',
            'approvedReviews.user',
        ])
            ->where('slug', $slug)
            ->where('is_available', true)
            ->firstOrFail();

        $related = Shop::with('category')
            ->where('category_id', $shop->category_id)
            ->where('id', '!=', $shop->id)
            ->where('is_available', true)
            ->orderBy('rating', 'desc')
            ->limit(3)
            ->get();

        return Inertia::render('Shops/Show', [
            'shop' => $shop,
            'related' => $related,
        ]);
    }
}
