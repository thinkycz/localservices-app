<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    /**
     * Get recent notifications for the dropdown (API).
     */
    public function recent(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->recent(10)
            ->get();

        $unreadCount = $request->user()->unreadNotificationsCount();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Ensure user owns this notification
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => auth()->user()->unreadNotificationsCount(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        NotificationService::markAllAsRead($request->user());

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Ensure user owns this notification
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'unread_count' => auth()->user()->unreadNotificationsCount(),
        ]);
    }
}
