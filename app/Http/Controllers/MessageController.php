<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Booking;
use App\Models\Service;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    /**
     * Display all conversations for the user.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $conversations = Conversation::forUser($user->id)
            ->with(['customer', 'provider', 'service', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        // Add unread count for current user
        $conversations->getCollection()->transform(function ($conversation) use ($user) {
            $conversation->unread_count = $user->id === $conversation->customer_id 
                ? $conversation->customer_unread_count 
                : $conversation->provider_unread_count;
            return $conversation;
        });

        return Inertia::render('Messages/Index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Show a specific conversation.
     */
    public function show(Request $request, Conversation $conversation): Response
    {
        $user = $request->user();
        
        // Verify user is part of this conversation
        if ($conversation->customer_id !== $user->id && $conversation->provider_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Mark messages as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Reset unread count
        $conversation->markAsRead($user->id);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return Inertia::render('Messages/Show', [
            'conversation' => $conversation->load(['customer', 'provider', 'service', 'booking']),
            'messages' => $messages,
        ]);
    }

    /**
     * Start a new conversation.
     */
    public function start(Request $request, Service $service): RedirectResponse
    {
        $user = $request->user();
        
        // Check if conversation already exists
        $existing = Conversation::where('customer_id', $user->id)
            ->where('provider_id', $service->user_id)
            ->where('service_id', $service->id)
            ->first();

        if ($existing) {
            return redirect()->route('messages.show', $existing->id);
        }

        $conversation = Conversation::create([
            'customer_id' => $user->id,
            'provider_id' => $service->user_id,
            'service_id' => $service->id,
        ]);

        return redirect()->route('messages.show', $conversation->id);
    }

    /**
     * Start a conversation from a booking.
     */
    public function startFromBooking(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();
        
        // Verify user is part of this booking
        if ($booking->user_id !== $user->id && $booking->provider_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Check if conversation already exists
        $existing = Conversation::where('booking_id', $booking->id)->first();

        if ($existing) {
            return redirect()->route('messages.show', $existing->id);
        }

        $conversation = Conversation::create([
            'customer_id' => $booking->user_id,
            'provider_id' => $booking->provider_id,
            'service_id' => $booking->service_id,
            'booking_id' => $booking->id,
        ]);

        // Add initial system message
        $conversation->messages()->create([
            'sender_id' => $booking->provider_id,
            'content' => "Hi! I'm looking forward to your booking on {$booking->booking_date->format('M d, Y')}. Let me know if you have any questions!",
            'type' => 'system',
        ]);

        return redirect()->route('messages.show', $conversation->id);
    }

    /**
     * Send a message.
     */
    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        
        // Verify user is part of this conversation
        if ($conversation->customer_id !== $user->id && $conversation->provider_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'string|max:255',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'content' => $validated['content'],
            'type' => 'text',
            'attachments' => $validated['attachments'] ?? null,
        ]);

        // Update conversation
        $conversation->update([
            'last_message_at' => now(),
        ]);
        $conversation->incrementUnreadCount($user->id);

        // Create notification for recipient
        $recipientId = $user->id === $conversation->customer_id 
            ? $conversation->provider_id 
            : $conversation->customer_id;
        
        \App\Models\Notification::create([
            'user_id' => $recipientId,
            'type' => 'message',
            'title' => 'New Message',
            'message' => "You have a new message from {$user->name}",
            'data' => [
                'conversation_id' => $conversation->id,
                'sender_name' => $user->name,
            ],
            'action_url' => route('messages.show', $conversation->id),
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->load('sender'),
        ]);
    }

    /**
     * Get new messages (for polling).
     */
    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        
        if ($conversation->customer_id !== $user->id && $conversation->provider_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $afterId = $request->get('after_id', 0);

        $messages = $conversation->messages()
            ->with('sender')
            ->where('id', '>', $afterId)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        $conversation->markAsRead($user->id);

        return response()->json([
            'messages' => $messages,
            'unread_count' => $user->id === $conversation->customer_id 
                ? $conversation->customer_unread_count 
                : $conversation->provider_unread_count,
        ]);
    }

    /**
     * Get unread conversation count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $count = Conversation::forUser($user->id)
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('customer_id', $user->id)
                      ->where('customer_unread_count', '>', 0);
                })->orWhere(function ($q) use ($user) {
                    $q->where('provider_id', $user->id)
                      ->where('provider_unread_count', '>', 0);
                });
            })
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }
}
