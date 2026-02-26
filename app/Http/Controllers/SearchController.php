<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    /**
     * Advanced search with filters and suggestions.
     */
    public function index(Request $request): Response
    {
        $query = $request->get('q', '');
        $filters = $request->only(['categories', 'price_range', 'min_rating', 'city', 'sort']);

        // Use Scout for full-text search if query exists
        if ($query) {
            $searchResults = Service::search($query, function ($searchEngine, $searchQuery) use ($filters) {
                // Apply filters to Scout search
                if (!empty($filters['categories'])) {
                    $searchQuery->whereIn('category', (array) $filters['categories']);
                }
                if (!empty($filters['price_range'])) {
                    $searchQuery->whereIn('price_range', (array) $filters['price_range']);
                }
                if (!empty($filters['min_rating'])) {
                    $searchQuery->where('rating', '>=', (float) $filters['min_rating']);
                }
                if (!empty($filters['city'])) {
                    $searchQuery->where('city', $filters['city']);
                }
                return $searchQuery;
            })->paginate(20);
        } else {
            // Fallback to regular query
            $searchResults = $this->applyFilters(Service::query(), $filters)
                ->with('category')
                ->paginate(20);
        }

        // Add is_bookmarked flag for authenticated users
        if ($request->user()) {
            $userId = $request->user()->id;
            $serviceIds = $searchResults->pluck('id')->toArray();
            $bookmarkedIds = Bookmark::where('user_id', $userId)
                ->whereIn('service_id', $serviceIds)
                ->pluck('service_id')
                ->toArray();

            $searchResults->getCollection()->transform(function ($service) use ($bookmarkedIds) {
                $service->is_bookmarked = in_array($service->id, $bookmarkedIds);
                return $service;
            });
        }

        $categories = Category::withCount('services')->get();
        
        // Get unique cities for filter
        $cities = Service::distinct()->whereNotNull('city')->pluck('city');

        return Inertia::render('Search/Index', [
            'services' => $searchResults,
            'categories' => $categories,
            'cities' => $cities,
            'filters' => array_merge(['q' => $query], $filters),
            'suggestions' => $query ? $this->getSuggestions($query) : [],
        ]);
    }

    /**
     * Get search suggestions for autocomplete.
     */
    public function suggestions(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Search in services
        $services = Service::search($query)
            ->take(5)
            ->get()
            ->map(fn ($service) => [
                'type' => 'service',
                'id' => $service->id,
                'title' => $service->name,
                'subtitle' => $service->category?->name,
                'url' => route('services.show', $service->slug),
            ]);

        // Search in categories
        $categories = Category::where('name', 'like', "%{$query}%")
            ->take(3)
            ->get()
            ->map(fn ($category) => [
                'type' => 'category',
                'id' => $category->id,
                'title' => $category->name,
                'subtitle' => $category->services_count . ' services',
                'url' => route('services.index', ['categories' => $category->slug]),
            ]);

        // Popular searches (mock data - could be stored in cache/db)
        $popular = $this->getPopularSearches($query);

        return response()->json([
            'services' => $services,
            'categories' => $categories,
            'popular' => $popular,
        ]);
    }

    /**
     * Get search suggestions based on query.
     */
    private function getSuggestions(string $query): array
    {
        return Service::search($query)
            ->take(5)
            ->get()
            ->map(fn ($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'category' => $service->category?->name,
                'slug' => $service->slug,
            ])
            ->toArray();
    }

    /**
     * Get popular searches matching query.
     */
    private function getPopularSearches(string $query): array
    {
        $popularTerms = [
            'plumber',
            'electrician',
            'cleaning',
            'painting',
            'gardening',
            'massage',
            'tutoring',
            'photography',
        ];

        return collect($popularTerms)
            ->filter(fn ($term) => str_contains(strtolower($term), strtolower($query)))
            ->take(3)
            ->map(fn ($term) => [
                'type' => 'popular',
                'title' => $term,
                'url' => route('search.index', ['q' => $term]),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Apply filters to query.
     */
    private function applyFilters($query, array $filters)
    {
        // Filter by category slugs
        if (!empty($filters['categories'])) {
            $categorySlugs = is_array($filters['categories'])
                ? $filters['categories']
                : explode(',', $filters['categories']);

            $query->whereHas('category', function ($q) use ($categorySlugs) {
                $q->whereIn('slug', $categorySlugs);
            });
        }

        // Filter by price range
        if (!empty($filters['price_range'])) {
            $priceRanges = is_array($filters['price_range'])
                ? $filters['price_range']
                : explode(',', $filters['price_range']);

            $query->whereIn('price_range', array_map('intval', $priceRanges));
        }

        // Filter by minimum rating
        if (!empty($filters['min_rating'])) {
            $query->where('rating', '>=', (float) $filters['min_rating']);
        }

        // Filter by location
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        // Sort
        $sort = $filters['sort'] ?? 'recommended';
        match ($sort) {
            'nearest' => $query->orderBy('id', 'asc'), // placeholder for geo sort
            'cheapest' => $query->orderBy('price_range', 'asc'),
            'highest_rated' => $query->orderBy('rating', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('rating', 'desc'),
        };

        return $query;
    }
}
