<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('payments')->group(function () {
        Route::post('/{booking}/confirm', [PaymentController::class, 'confirmPayment'])->name('payments.confirm');
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    Route::prefix('messages')->group(function () {
        Route::get('/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unreadCount');
        Route::post('/{conversation}', [MessageController::class, 'store'])->name('messages.store')->whereNumber('conversation');
        Route::get('/{conversation}/poll', [MessageController::class, 'poll'])->name('messages.poll')->whereNumber('conversation');
    });
});
