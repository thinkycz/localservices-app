<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

// Language Switcher
Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'cs'])) {
        Session::put('locale', $locale);
    }
    return redirect()->back();
})->name('language.switch');


// Home page
Route::get('/', function () {
    $featuredServices = \App\Models\Shop::with('category')
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
            ->whereIn('shop_id', $serviceIds)
            ->pluck('shop_id')
            ->toArray();

        $featuredServices->transform(function ($service) use ($bookmarkedIds) {
            $service->is_bookmarked = in_array($service->id, $bookmarkedIds);
            return $service;
        });
    }

    $categories = \App\Models\Category::withCount('shops')
        ->orderBy('shops_count', 'desc')
        ->limit(8)
        ->get();

    return Inertia::render('Home', [
        'featuredShops' => $featuredServices,
        'categories' => $categories,
    ]);
})->name('home');

Route::middleware('auth')->get('/dashboard', function () {
    $user = Auth::user();

    if ($user?->is_vendor) {
        return redirect()->route('vendor.dashboard');
    }

    return redirect()->route('home');
})->name('dashboard');

// Shops (public)
Route::get('/shops', [ShopController::class, 'index'])->name('shops.index');
Route::get('/shops/{slug}', [ShopController::class, 'show'])->name('shops.show');
Route::get('/shops/{slug}/book', [BookingController::class, 'show'])->name('shops.book');

// Booking routes (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/confirmation/{id}', [BookingController::class, 'confirmation'])->name('bookings.confirmation');
    Route::get('/bookings', [BookingController::class, 'userBookings'])->name('bookings.index');
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});

// Reviews (public viewing, auth required for creating)
Route::middleware('auth')->group(function () {
    Route::get('/reviews/create/{bookingId}', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/my-reviews', [ReviewController::class, 'userReviews'])->name('reviews.user');
});

// Static Pages
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('pages.contact.submit');

// Vendor Onboarding (auth required, but NOT vendor)
Route::middleware(['auth', 'verified'])->prefix('become-vendor')->name('vendor.onboarding.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Vendor\OnboardingController::class, 'index'])->name('index');
    Route::get('/step1', [\App\Http\Controllers\Vendor\OnboardingController::class, 'step1'])->name('step1');
    Route::post('/step1', [\App\Http\Controllers\Vendor\OnboardingController::class, 'storeStep1'])->name('step1.store');
    Route::get('/step2', [\App\Http\Controllers\Vendor\OnboardingController::class, 'step2'])->name('step2');
    Route::post('/step2', [\App\Http\Controllers\Vendor\OnboardingController::class, 'storeStep2'])->name('step2.store');
    Route::get('/step3', [\App\Http\Controllers\Vendor\OnboardingController::class, 'step3'])->name('step3');
    Route::post('/step3', [\App\Http\Controllers\Vendor\OnboardingController::class, 'storeStep3'])->name('step3.store');
});

// Vendor Routes - All under /vendor prefix, requires auth + vendor
Route::prefix('vendor')->middleware(['auth', 'verified', 'vendor.check'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Vendor\DashboardController::class, 'index'])->name('vendor.dashboard');

    // Calendar
    Route::get('/calendar', [\App\Http\Controllers\Vendor\CalendarController::class, 'index'])->name('vendor.calendar');

    // Bookings
    Route::get('/bookings', [\App\Http\Controllers\Vendor\BookingController::class, 'index'])->name('vendor.bookings.index');
    Route::get('/bookings/{id}', [\App\Http\Controllers\Vendor\BookingController::class, 'show'])->name('vendor.bookings.show');
    Route::post('/bookings/{id}/confirm', [\App\Http\Controllers\Vendor\BookingController::class, 'confirm'])->name('vendor.bookings.confirm');
    Route::post('/bookings/{id}/complete', [\App\Http\Controllers\Vendor\BookingController::class, 'complete'])->name('vendor.bookings.complete');
    Route::post('/bookings/{id}/cancel', [\App\Http\Controllers\Vendor\BookingController::class, 'cancel'])->name('vendor.bookings.cancel');
    Route::post('/bookings/{id}/notes', [\App\Http\Controllers\Vendor\BookingController::class, 'addNotes'])->name('vendor.bookings.notes');
    Route::get('/shops/{shopId}/available-slots', [\App\Http\Controllers\Vendor\BookingController::class, 'getAvailableSlots'])->name('vendor.bookings.slots');

    // Customers
    Route::get('/customers', [\App\Http\Controllers\Vendor\CustomerController::class, 'index'])->name('vendor.customers.index');
    Route::get('/customers/{customerId}', [\App\Http\Controllers\Vendor\CustomerController::class, 'show'])->name('vendor.customers.show');

    // Shops
    Route::get('/shops', [\App\Http\Controllers\Vendor\ShopsController::class, 'index'])->name('vendor.shops.index');
    Route::get('/shops/create', [\App\Http\Controllers\Vendor\ShopsController::class, 'create'])->name('vendor.shops.create');
    Route::post('/shops', [\App\Http\Controllers\Vendor\ShopsController::class, 'store'])->name('vendor.shops.store');
    Route::get('/shops/{id}', [\App\Http\Controllers\Vendor\ShopsController::class, 'show'])->name('vendor.shops.show');
    Route::get('/shops/{id}/edit', [\App\Http\Controllers\Vendor\ShopsController::class, 'edit'])->name('vendor.shops.edit');
    Route::put('/shops/{id}', [\App\Http\Controllers\Vendor\ShopsController::class, 'update'])->name('vendor.shops.update');
    Route::delete('/shops/{id}', [\App\Http\Controllers\Vendor\ShopsController::class, 'destroy'])->name('vendor.shops.destroy');
    Route::post('/shops/{id}/toggle-availability', [\App\Http\Controllers\Vendor\ShopsController::class, 'toggleAvailability'])->name('vendor.shops.toggle');

    // Services (formerly Service Offerings)
    Route::post('/shops/{shopId}/services', [\App\Http\Controllers\Vendor\ShopsController::class, 'storeService'])->name('vendor.shops.services.store');
    Route::put('/shops/{shopId}/services/{serviceId}', [\App\Http\Controllers\Vendor\ShopsController::class, 'updateService'])->name('vendor.shops.services.update');
    Route::delete('/shops/{shopId}/services/{serviceId}', [\App\Http\Controllers\Vendor\ShopsController::class, 'destroyService'])->name('vendor.shops.services.destroy');

    // Business Hours
    Route::post('/shops/{shopId}/business-hours', [\App\Http\Controllers\Vendor\ShopsController::class, 'storeBusinessHours'])->name('vendor.shops.business-hours.store');
});

// Profile (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



require __DIR__ . '/auth.php';
