<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\GuestBookingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\Vendor\CalendarController;
use App\Http\Controllers\Vendor\CustomerController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\OnboardingController;
use App\Http\Controllers\Vendor\ShopsController;
use App\Http\Middleware\EnsureNotVendor;
use App\Models\Category;
use App\Models\Shop;
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
    $featuredShops = Shop::with('category')
        ->where('is_available', true)
        ->orderBy('rating', 'desc')
        ->orderBy('reviews_count', 'desc')
        ->limit(8)
        ->get();

    $categories = Category::withCount([
        'shops' => fn ($query) => $query->where('is_available', true),
    ])
        ->orderBy('shops_count', 'desc')
        ->limit(8)
        ->get();

    return Inertia::render('Home', [
        'featuredShops' => $featuredShops,
        'categories' => $categories,
    ]);
})->name('home');

Route::middleware('auth')->get('/dashboard', function () {
    $user = Auth::user();

    if ($user?->is_vendor) {
        return redirect()->route('vendor.dashboard');
    }

    if ($user?->provider_onboarding_pending) {
        return $user->hasVerifiedEmail()
            ? redirect()->route('vendor.onboarding.index')
            : redirect()->route('verification.notice');
    }

    return redirect()->route('home');
})->name('dashboard');

// Shops (public)
Route::get('/shops', [ShopController::class, 'index'])->name('shops.index');
Route::get('/shops/{slug}', [ShopController::class, 'show'])->name('shops.show');
Route::get('/shops/{slug}/book', [BookingController::class, 'show'])->name('shops.book');
Route::get('/shops/{shop:slug}/availability', [ShopController::class, 'availability'])
    ->middleware('throttle:60,1')
    ->name('shops.availability');

// Public and guest booking routes
Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('bookings.store');
Route::prefix('guest/bookings/{booking}/{token}')
    ->middleware('throttle:30,1')
    ->name('guest.bookings.')
    ->group(function () {
        Route::get('/', [GuestBookingController::class, 'show'])->name('show');
        Route::post('/cancel', [GuestBookingController::class, 'cancel'])->name('cancel');
        Route::post('/claim', [GuestBookingController::class, 'claim'])
            ->middleware(['auth', 'verified'])
            ->name('claim');
    });

// Customer account booking routes
Route::middleware('auth')->group(function () {
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
Route::post('/contact', [PageController::class, 'submitContact'])
    ->middleware('throttle:10,1')
    ->name('pages.contact.submit');

// Vendor Onboarding (auth required, but NOT vendor)
Route::middleware(['auth', 'verified', EnsureNotVendor::class])->prefix('become-vendor')->name('vendor.onboarding.')->group(function () {
    Route::get('/', [OnboardingController::class, 'index'])->name('index');
    Route::get('/step1', [OnboardingController::class, 'step1'])->name('step1');
    Route::post('/step1', [OnboardingController::class, 'storeStep1'])->name('step1.store');
    Route::get('/step2', [OnboardingController::class, 'step2'])->name('step2');
    Route::post('/step2', [OnboardingController::class, 'storeStep2'])->name('step2.store');
    Route::get('/step3', [OnboardingController::class, 'step3'])->name('step3');
    Route::post('/step3', [OnboardingController::class, 'storeStep3'])->name('step3.store');
});

// Vendor Routes - All under /vendor prefix, requires auth + vendor
Route::prefix('vendor')->middleware(['auth', 'verified', 'vendor.check'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('vendor.dashboard');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('vendor.calendar');

    // Bookings
    Route::get('/bookings', [App\Http\Controllers\Vendor\BookingController::class, 'index'])->name('vendor.bookings.index');
    Route::get('/bookings/{id}', [App\Http\Controllers\Vendor\BookingController::class, 'show'])->name('vendor.bookings.show');
    Route::post('/bookings/{id}/confirm', [App\Http\Controllers\Vendor\BookingController::class, 'confirm'])->name('vendor.bookings.confirm');
    Route::post('/bookings/{id}/complete', [App\Http\Controllers\Vendor\BookingController::class, 'complete'])->name('vendor.bookings.complete');
    Route::post('/bookings/{id}/cancel', [App\Http\Controllers\Vendor\BookingController::class, 'cancel'])->name('vendor.bookings.cancel');
    Route::post('/bookings/{id}/update', [App\Http\Controllers\Vendor\BookingController::class, 'update'])->name('vendor.bookings.update');
    Route::post('/bookings/{id}/notes', [App\Http\Controllers\Vendor\BookingController::class, 'addNotes'])->name('vendor.bookings.notes');
    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('vendor.customers.index');
    Route::get('/customers/{customerId}', [CustomerController::class, 'show'])->name('vendor.customers.show');

    // Shops
    Route::get('/shops', [ShopsController::class, 'index'])->name('vendor.shops.index');
    Route::get('/shops/create', [ShopsController::class, 'create'])->name('vendor.shops.create');
    Route::post('/shops', [ShopsController::class, 'store'])->name('vendor.shops.store');
    Route::get('/shops/{id}', [ShopsController::class, 'show'])->name('vendor.shops.show');
    Route::get('/shops/{id}/edit', [ShopsController::class, 'edit'])->name('vendor.shops.edit');
    Route::put('/shops/{id}', [ShopsController::class, 'update'])->name('vendor.shops.update');
    Route::delete('/shops/{id}', [ShopsController::class, 'destroy'])->name('vendor.shops.destroy');
    Route::post('/shops/{id}/toggle-availability', [ShopsController::class, 'toggleAvailability'])->name('vendor.shops.toggle');

    // Services (formerly Service Offerings)
    Route::post('/shops/{shopId}/services', [ShopsController::class, 'storeService'])->name('vendor.shops.services.store');
    Route::put('/shops/{shopId}/services/{serviceId}', [ShopsController::class, 'updateService'])->name('vendor.shops.services.update');
    Route::delete('/shops/{shopId}/services/{serviceId}', [ShopsController::class, 'destroyService'])->name('vendor.shops.services.destroy');

    // Business Hours
    Route::post('/shops/{shopId}/business-hours', [ShopsController::class, 'storeBusinessHours'])->name('vendor.shops.business-hours.store');
});

// Profile (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
