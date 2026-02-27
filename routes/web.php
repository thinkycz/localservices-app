<?php

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home page
Route::get('/', function () {
    $featuredServices = \App\Models\Service::with('category')
        ->where('is_available', true)
        ->orderBy('rating', 'desc')
        ->orderBy('reviews_count', 'desc')
        ->limit(8)
        ->get();

    // Add is_bookmarked flag for authenticated users
    if (Auth::check()) {
        $userId = Auth::id();
        $serviceIds = $featuredServices->pluck('id')->toArray();
        $bookmarkedIds = \App\Models\Bookmark::where('user_id', $userId)
            ->whereIn('service_id', $serviceIds)
            ->pluck('service_id')
            ->toArray();

        $featuredServices->transform(function ($service) use ($bookmarkedIds) {
            $service->is_bookmarked = in_array($service->id, $bookmarkedIds);
            return $service;
        });
    }

    $categories = \App\Models\Category::withCount('services')
        ->orderBy('services_count', 'desc')
        ->limit(8)
        ->get();

    return Inertia::render('Home', [
        'featuredServices' => $featuredServices,
        'categories' => $categories,
    ]);
})->name('home');

Route::middleware('auth')->get('/dashboard', function () {
    $user = Auth::user();

    if ($user?->is_admin) {
        return redirect()->route('admin.dashboard');
    }

    if ($user?->is_service_provider) {
        return redirect()->route('vendor.dashboard');
    }

    return redirect()->route('home');
})->name('dashboard');

// Services (public)
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/services/{slug}/book', [BookingController::class, 'show'])->name('services.book');

// Booking routes (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/confirmation/{id}', [BookingController::class, 'confirmation'])->name('bookings.confirmation');
    Route::get('/bookings', [BookingController::class, 'userBookings'])->name('bookings.index');
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    
    // PDF Downloads
    Route::get('/bookings/{id}/pdf', [PdfController::class, 'bookingConfirmation'])->name('bookings.pdf');
    Route::get('/bookings/{id}/invoice', [PdfController::class, 'invoice'])->name('bookings.invoice');
});

// Payments (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/payment/{booking}', [\App\Http\Controllers\PaymentController::class, 'show'])->name('payment.show');
    Route::get('/payment-history', [\App\Http\Controllers\PaymentController::class, 'history'])->name('payment.history');
    Route::post('/payment/{booking}/refund', [\App\Http\Controllers\PaymentController::class, 'refund'])->name('payment.refund');
});

// Reviews (public viewing, auth required for creating)
Route::get('/services/{slug}/reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::middleware('auth')->group(function () {
    Route::get('/reviews/create/{bookingId}', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/my-reviews', [ReviewController::class, 'userReviews'])->name('reviews.user');
});

// Notifications + Messages pages (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');

    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [\App\Http\Controllers\MessageController::class, 'show'])->name('messages.show')->whereNumber('conversation');
    Route::post('/services/{service}/message', [\App\Http\Controllers\MessageController::class, 'start'])->name('messages.start');
    Route::post('/bookings/{booking}/message', [\App\Http\Controllers\MessageController::class, 'startFromBooking'])->name('messages.startFromBooking');
});

// Bookmarks (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/bookmarks/{serviceId}', [BookmarkController::class, 'store'])->name('bookmarks.store');
    Route::delete('/bookmarks/{serviceId}', [BookmarkController::class, 'destroy'])->name('bookmarks.destroy');
    Route::post('/bookmarks/{serviceId}/toggle', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
    Route::get('/bookmarks/{serviceId}/check', [BookmarkController::class, 'check'])->name('bookmarks.check');
});

// Categories (public)
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

// Static Pages
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('pages.contact.submit');

// Vendor Onboarding (auth required, but NOT service provider)
Route::middleware(['auth', 'verified'])->prefix('become-vendor')->name('vendor.onboarding.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Vendor\OnboardingController::class, 'index'])->name('index');
    Route::get('/step1', [\App\Http\Controllers\Vendor\OnboardingController::class, 'step1'])->name('step1');
    Route::post('/step1', [\App\Http\Controllers\Vendor\OnboardingController::class, 'storeStep1'])->name('step1.store');
    Route::get('/step2', [\App\Http\Controllers\Vendor\OnboardingController::class, 'step2'])->name('step2');
    Route::post('/step2', [\App\Http\Controllers\Vendor\OnboardingController::class, 'storeStep2'])->name('step2.store');
    Route::get('/step3', [\App\Http\Controllers\Vendor\OnboardingController::class, 'step3'])->name('step3');
    Route::post('/step3', [\App\Http\Controllers\Vendor\OnboardingController::class, 'storeStep3'])->name('step3.store');
});

// Vendor Routes - All under /vendor prefix, requires auth + service provider
Route::prefix('vendor')->middleware(['auth', 'verified', 'service.provider'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Vendor\DashboardController::class, 'index'])->name('vendor.dashboard');

    // Analytics
    Route::get('/analytics', [\App\Http\Controllers\Vendor\AnalyticsController::class, 'index'])->name('vendor.analytics');

    // Calendar
    Route::get('/calendar', [\App\Http\Controllers\Vendor\CalendarController::class, 'index'])->name('vendor.calendar');

    // Bookings
    Route::get('/bookings', [\App\Http\Controllers\Vendor\BookingController::class, 'index'])->name('vendor.bookings.index');
    Route::get('/bookings/{id}', [\App\Http\Controllers\Vendor\BookingController::class, 'show'])->name('vendor.bookings.show');
    Route::post('/bookings/{id}/confirm', [\App\Http\Controllers\Vendor\BookingController::class, 'confirm'])->name('vendor.bookings.confirm');
    Route::post('/bookings/{id}/complete', [\App\Http\Controllers\Vendor\BookingController::class, 'complete'])->name('vendor.bookings.complete');
    Route::post('/bookings/{id}/cancel', [\App\Http\Controllers\Vendor\BookingController::class, 'cancel'])->name('vendor.bookings.cancel');
    Route::post('/bookings/{id}/notes', [\App\Http\Controllers\Vendor\BookingController::class, 'addNotes'])->name('vendor.bookings.notes');
    Route::get('/services/{serviceId}/available-slots', [\App\Http\Controllers\Vendor\BookingController::class, 'getAvailableSlots'])->name('vendor.bookings.slots');

    // Customers
    Route::get('/customers', [\App\Http\Controllers\Vendor\CustomerController::class, 'index'])->name('vendor.customers.index');
    Route::get('/customers/{customerId}', [\App\Http\Controllers\Vendor\CustomerController::class, 'show'])->name('vendor.customers.show');

    // Services
    Route::get('/services', [\App\Http\Controllers\Vendor\ServicesController::class, 'index'])->name('vendor.services.index');
    Route::get('/services/create', [\App\Http\Controllers\Vendor\ServicesController::class, 'create'])->name('vendor.services.create');
    Route::post('/services', [\App\Http\Controllers\Vendor\ServicesController::class, 'store'])->name('vendor.services.store');
    Route::get('/services/{id}/edit', [\App\Http\Controllers\Vendor\ServicesController::class, 'edit'])->name('vendor.services.edit');
    Route::put('/services/{id}', [\App\Http\Controllers\Vendor\ServicesController::class, 'update'])->name('vendor.services.update');
    Route::delete('/services/{id}', [\App\Http\Controllers\Vendor\ServicesController::class, 'destroy'])->name('vendor.services.destroy');
    Route::post('/services/{id}/toggle-availability', [\App\Http\Controllers\Vendor\ServicesController::class, 'toggleAvailability'])->name('vendor.services.toggle');

    // Service Offerings
    Route::post('/services/{serviceId}/offerings', [\App\Http\Controllers\Vendor\ServicesController::class, 'storeOffering'])->name('vendor.services.offerings.store');
    Route::put('/services/{serviceId}/offerings/{offeringId}', [\App\Http\Controllers\Vendor\ServicesController::class, 'updateOffering'])->name('vendor.services.offerings.update');
    Route::delete('/services/{serviceId}/offerings/{offeringId}', [\App\Http\Controllers\Vendor\ServicesController::class, 'destroyOffering'])->name('vendor.services.offerings.destroy');
});

// Profile (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    
    // User Management
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');
    Route::patch('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::post('/users/{user}/toggle-admin', [\App\Http\Controllers\Admin\UserController::class, 'toggleAdmin'])->name('admin.users.toggle-admin');
    Route::post('/users/{user}/toggle-vendor', [\App\Http\Controllers\Admin\UserController::class, 'toggleVendor'])->name('admin.users.toggle-vendor');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/users/bulk', [\App\Http\Controllers\Admin\UserController::class, 'bulkAction'])->name('admin.users.bulk');
});

// Social Authentication
Route::get('/auth/{provider}', [\App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])
    ->name('social.redirect')
    ->where('provider', 'google|facebook');

Route::get('/auth/{provider}/callback', [\App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])
    ->name('social.callback')
    ->where('provider', 'google|facebook');

Route::middleware('auth')->group(function () {
    Route::get('/auth/{provider}/link', [\App\Http\Controllers\Auth\SocialAuthController::class, 'link'])
        ->name('social.link')
        ->where('provider', 'google|facebook');
    
    Route::post('/auth/{provider}/unlink', [\App\Http\Controllers\Auth\SocialAuthController::class, 'unlink'])
        ->name('social.unlink')
        ->where('provider', 'google|facebook');
});

require __DIR__.'/auth.php';
