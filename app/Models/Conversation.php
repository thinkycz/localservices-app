<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'customer_id',
        'provider_id',
        'service_id',
        'booking_id',
        'last_message_at',
        'customer_unread_count',
        'provider_unread_count',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('customer_id', $userId)
              ->orWhere('provider_id', $userId);
        });
    }

    public function scopeWithUnreadCount($query, $userId)
    {
        return $query->selectRaw('
            conversations.*,
            CASE 
                WHEN customer_id = ? THEN customer_unread_count 
                ELSE provider_unread_count 
            END as unread_count
        ', [$userId]);
    }

    public function markAsRead(int $userId): void
    {
        if ($this->customer_id === $userId) {
            $this->update(['customer_unread_count' => 0]);
        } else {
            $this->update(['provider_unread_count' => 0]);
        }
    }

    public function incrementUnreadCount(int $senderId): void
    {
        if ($this->customer_id === $senderId) {
            $this->increment('provider_unread_count');
        } else {
            $this->increment('customer_unread_count');
        }
    }
}
