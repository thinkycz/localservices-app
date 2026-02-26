<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): Response
    {
        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            match ($request->role) {
                'admin' => $query->where('is_admin', true),
                'vendor' => $query->where('is_service_provider', true),
                'customer' => $query->where('is_service_provider', false),
                default => null,
            };
        }

        // Filter by status
        if ($request->filled('status')) {
            match ($request->status) {
                'verified' => $query->whereNotNull('email_verified_at'),
                'unverified' => $query->whereNull('email_verified_at'),
                default => null,
            };
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status']),
            'stats' => [
                'total' => User::count(),
                'admins' => User::where('is_admin', true)->count(),
                'vendors' => User::where('is_service_provider', true)->count(),
                'customers' => User::where('is_service_provider', false)->count(),
                'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
            ],
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        $user->load(['services', 'bookings' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'activity' => $this->getUserActivity($user),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'is_admin' => 'boolean',
            'is_service_provider' => 'boolean',
        ]);

        $user->update($validated);

        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Toggle user admin status.
     */
    public function toggleAdmin(User $user)
    {
        $user->update(['is_admin' => ! $user->is_admin]);

        return back()->with('success', 'Admin status updated.');
    }

    /**
     * Toggle user service provider status.
     */
    public function toggleVendor(User $user)
    {
        $user->update(['is_service_provider' => ! $user->is_service_provider]);

        return back()->with('success', 'Vendor status updated.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Bulk actions on users.
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'action' => 'required|in:delete,verify,make_admin,remove_admin,make_vendor,remove_vendor',
        ]);

        $users = User::whereIn('id', $validated['ids']);

        match ($validated['action']) {
            'delete' => $users->delete(),
            'verify' => $users->update(['email_verified_at' => now()]),
            'make_admin' => $users->update(['is_admin' => true]),
            'remove_admin' => $users->update(['is_admin' => false]),
            'make_vendor' => $users->update(['is_service_provider' => true]),
            'remove_vendor' => $users->update(['is_service_provider' => false]),
            default => null,
        };

        return back()->with('success', 'Bulk action completed successfully.');
    }

    /**
     * Get user activity summary.
     */
    private function getUserActivity(User $user): array
    {
        return [
            'total_bookings' => $user->bookings()->count(),
            'total_services' => $user->services()->count(),
            'total_reviews' => $user->reviews()->count(),
            'member_since' => $user->created_at->format('F Y'),
            'last_login' => $user->last_login_at?->diffForHumans() ?? 'Never',
        ];
    }
}
