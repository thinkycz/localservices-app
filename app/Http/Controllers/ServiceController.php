<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Service::with('category');

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
            'nearest'   => $query->orderBy('id', 'asc'), // placeholder for geo sort
            'cheapest'  => $query->orderBy('price_range', 'asc'),
            default     => $query->orderBy('rating', 'desc'),
        };

        $services = $query->paginate(20)->withQueryString();

        $categories = Category::withCount('services')->get();

        return Inertia::render('Services/Index', [
            'services'   => $services,
            'categories' => $categories,
            'filters'    => $request->only(['q', 'categories', 'price_range', 'min_rating', 'sort', 'city']),
        ]);
    }

    public function show(string $slug): Response
    {
        $service = Service::with(['category', 'offerings'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Service::with('category')
            ->where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->orderBy('rating', 'desc')
            ->limit(3)
            ->get();

        return Inertia::render('Services/Show', [
            'service' => $service,
            'related' => $related,
        ]);
    }
}
