<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home page
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Services (public)
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/services/{slug}/book', [BookingController::class, 'show'])->name('services.book');

// Categories (public)
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

// Vendor Routes - All under /vendor prefix, requires auth + service provider
Route::prefix('vendor')->middleware(['auth', 'verified', 'service.provider'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Vendor/Dashboard');
    })->name('vendor.dashboard');

    // Calendar
    Route::get('/calendar', function () {
        return Inertia::render('Vendor/Calendar');
    })->name('vendor.calendar');

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

    // Categories (vendor-specific)
    Route::get('/categories', [\App\Http\Controllers\Vendor\CategoryController::class, 'index'])->name('vendor.categories.index');
    Route::post('/categories', [\App\Http\Controllers\Vendor\CategoryController::class, 'store'])->name('vendor.categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\Vendor\CategoryController::class, 'update'])->name('vendor.categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\Vendor\CategoryController::class, 'destroy'])->name('vendor.categories.destroy');
});

// Profile (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

