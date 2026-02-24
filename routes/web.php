<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VendorServicesController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home page
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Services
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/services/{slug}/book', [BookingController::class, 'show'])->name('services.book');

// Categories
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

// Dashboard (auth required)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Calendar (auth required)
Route::get('/calendar', function () {
    return Inertia::render('Calendar');
})->middleware(['auth', 'verified'])->name('calendar');

// Customers (auth required - for service providers)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customerId}', [CustomerController::class, 'show'])->name('customers.show');
});

// Vendor Services (auth required - for service providers)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/vendor/services', [VendorServicesController::class, 'index'])->name('vendor.services.index');
    Route::get('/vendor/services/create', [VendorServicesController::class, 'create'])->name('vendor.services.create');
    Route::post('/vendor/services', [VendorServicesController::class, 'store'])->name('vendor.services.store');
    Route::get('/vendor/services/{id}/edit', [VendorServicesController::class, 'edit'])->name('vendor.services.edit');
    Route::put('/vendor/services/{id}', [VendorServicesController::class, 'update'])->name('vendor.services.update');
    Route::delete('/vendor/services/{id}', [VendorServicesController::class, 'destroy'])->name('vendor.services.destroy');
    Route::post('/vendor/services/{id}/toggle-availability', [VendorServicesController::class, 'toggleAvailability'])->name('vendor.services.toggle');
    
    // Service Offerings
    Route::post('/vendor/services/{serviceId}/offerings', [VendorServicesController::class, 'storeOffering'])->name('vendor.services.offerings.store');
    Route::put('/vendor/services/{serviceId}/offerings/{offeringId}', [VendorServicesController::class, 'updateOffering'])->name('vendor.services.offerings.update');
    Route::delete('/vendor/services/{serviceId}/offerings/{offeringId}', [VendorServicesController::class, 'destroyOffering'])->name('vendor.services.offerings.destroy');

    // Vendor Categories
    Route::get('/vendor/categories', [\App\Http\Controllers\Vendor\CategoryController::class, 'index'])->name('vendor.categories.index');
    Route::post('/vendor/categories', [\App\Http\Controllers\Vendor\CategoryController::class, 'store'])->name('vendor.categories.store');
    Route::put('/vendor/categories/{category}', [\App\Http\Controllers\Vendor\CategoryController::class, 'update'])->name('vendor.categories.update');
    Route::delete('/vendor/categories/{category}', [\App\Http\Controllers\Vendor\CategoryController::class, 'destroy'])->name('vendor.categories.destroy');
});

// Profile (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
