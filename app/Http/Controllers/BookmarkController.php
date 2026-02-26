<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookmarkController extends Controller
{
    /**
     * Display user's bookmarks.
     */
    public function index(Request $request): Response
    {
        $bookmarks = Bookmark::with(['service' => function ($query) {
                $query->with(['category', 'offerings']);
            }])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return Inertia::render('Bookmarks/Index', [
            'bookmarks' => $bookmarks,
        ]);
    }

    /**
     * Add a service to bookmarks.
     */
    public function store(Request $request, int $serviceId): RedirectResponse|JsonResponse
    {
        $service = Service::findOrFail($serviceId);

        // Check if already bookmarked
        $existing = Bookmark::where('user_id', $request->user()->id)
            ->where('service_id', $serviceId)
            ->first();

        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Already bookmarked'], 409);
            }
            return back()->with('info', 'This service is already in your bookmarks.');
        }

        Bookmark::create([
            'user_id' => $request->user()->id,
            'service_id' => $serviceId,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Bookmarked successfully'], 201);
        }

        return back()->with('success', 'Service added to your bookmarks.');
    }

    /**
     * Remove a bookmark.
     */
    public function destroy(Request $request, int $serviceId): RedirectResponse|JsonResponse
    {
        $bookmark = Bookmark::where('user_id', $request->user()->id)
            ->where('service_id', $serviceId)
            ->firstOrFail();

        $bookmark->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Bookmark removed'], 200);
        }

        return back()->with('success', 'Service removed from your bookmarks.');
    }

    /**
     * Toggle bookmark status (add/remove).
     */
    public function toggle(Request $request, int $serviceId): JsonResponse
    {
        $service = Service::findOrFail($serviceId);

        $existing = Bookmark::where('user_id', $request->user()->id)
            ->where('service_id', $serviceId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'bookmarked' => false,
                'message' => 'Bookmark removed',
            ]);
        }

        Bookmark::create([
            'user_id' => $request->user()->id,
            'service_id' => $serviceId,
        ]);

        return response()->json([
            'bookmarked' => true,
            'message' => 'Bookmarked successfully',
        ]);
    }

    /**
     * Check if a service is bookmarked by the user.
     */
    public function check(Request $request, int $serviceId): JsonResponse
    {
        $isBookmarked = Bookmark::where('user_id', $request->user()->id)
            ->where('service_id', $serviceId)
            ->exists();

        return response()->json([
            'bookmarked' => $isBookmarked,
        ]);
    }
}
