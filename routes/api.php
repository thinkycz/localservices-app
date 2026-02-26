<?php

use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Payment API routes
Route::middleware('auth')->group(function () {
    Route::post('/payments/{booking}/intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/{booking}/confirm', [PaymentController::class, 'confirmPayment']);
});

// Payment page routes
Route::middleware('auth')->group(function () {
    Route::get('/payment/{booking}', [PaymentController::class, 'show'])->name('payment.show');
    Route::get('/payment-history', [PaymentController::class, 'history'])->name('payment.history');
    Route::post('/payment/{booking}/refund', [PaymentController::class, 'refund'])->name('payment.refund');
});

// Notification API routes - using web middleware for session auth
Route::middleware(['web', 'auth'])->prefix('notifications')->group(function () {
    Route::get('/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Notification page route
Route::middleware('auth')->get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

// Message routes
Route::middleware('auth')->group(function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{conversation}/poll', [MessageController::class, 'poll'])->name('messages.poll');
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unreadCount');
    Route::post('/services/{service}/message', [MessageController::class, 'start'])->name('messages.start');
    Route::post('/bookings/{booking}/message', [MessageController::class, 'startFromBooking'])->name('messages.startFromBooking');
});

// Image upload routes
Route::middleware('auth')->prefix('services/{service}/images')->group(function () {
    Route::get('/', [ImageUploadController::class, 'index'])->name('images.index');
    Route::post('/', [ImageUploadController::class, 'upload'])->name('images.upload');
    Route::delete('/{image}', [ImageUploadController::class, 'destroy'])->name('images.destroy');
    Route::post('/{image}/primary', [ImageUploadController::class, 'setPrimary'])->name('images.primary');
    Route::post('/reorder', [ImageUploadController::class, 'updateOrder'])->name('images.reorder');
});
