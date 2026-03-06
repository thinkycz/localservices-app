<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Shop::with('category');

        // Search by keyword
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('description', 'like', '%' . $request->q . '%');
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
            'nearest' => $query->orderBy('id', 'asc'), // placeholder for geo sort
            'cheapest' => $query->orderBy('price_range', 'asc'),
            default => $query->orderBy('rating', 'desc'),
        };

        $shops = $query->paginate(20)->withQueryString();

        // Add is_bookmarked flag for authenticated users
        if ($request->user()) {
            $userId = $request->user()->id;
            $shopIds = $shops->pluck('id')->toArray();
            $bookmarkedIds = Bookmark::where('user_id', $userId)
                ->whereIn('shop_id', $shopIds)
                ->pluck('shop_id')
                ->toArray();

            $shops->getCollection()->transform(function ($shop) use ($bookmarkedIds) {
                $shop->is_bookmarked = in_array($shop->id, $bookmarkedIds);
                return $shop;
            });
        }

        $categories = Category::withCount('shops')->get();

        return Inertia::render('Shops/Index', [
            'shops' => $shops,
            'categories' => $categories,
            'filters' => $request->only(['q', 'categories', 'price_range', 'min_rating', 'sort', 'city']),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $shop = Shop::with(['category', 'services', 'businessHours'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Add is_bookmarked flag for authenticated users
        if ($request->user()) {
            $shop->is_bookmarked = Bookmark::where('user_id', $request->user()->id)
                ->where('shop_id', $shop->id)
                ->exists();
        }

        $related = Shop::with('category')
            ->where('category_id', $shop->category_id)
            ->where('id', '!=', $shop->id)
            ->orderBy('rating', 'desc')
            ->limit(3)
            ->get();

        // Add is_bookmarked flag to related services for authenticated users
        if ($request->user()) {
            $userId = $request->user()->id;
            $relatedIds = $related->pluck('id')->toArray();
            $bookmarkedIds = Bookmark::where('user_id', $userId)
                ->whereIn('shop_id', $relatedIds)
                ->pluck('shop_id')
                ->toArray();

            $related->transform(function ($relatedShop) use ($bookmarkedIds) {
                $relatedShop->is_bookmarked = in_array($relatedShop->id, $bookmarkedIds);
                return $relatedShop;
            });
        }

        $bookings = \App\Models\Booking::where('provider_id', $shop->user_id)
            ->where('booking_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['booking_date', 'start_time', 'end_time']);

        return Inertia::render('Shops/Show', [
            'shop' => $shop,
            'related' => $related,
            'bookings' => $bookings,
        ]);
    }
}
