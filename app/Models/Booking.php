<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $hidden = [
        'guest_token_hash',
    ];

    protected $appends = [
        'total_price',
        'customer_display_name',
        'customer_contact_email',
    ];

    protected $fillable = [
        'user_id',
        'shop_id',
        'service_id',
        'provider_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'guest_token_hash',
        'price_amount',
        'currency',
        'timezone',
        'status',
        'booking_date',
        'start_time',
        'end_time',
        'notes',
        'customer_notes',
        'cancellation_reason',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'price_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function getTotalPriceAttribute()
    {
        return $this->price_amount ?? $this->service?->price ?? 0;
    }

    public function statusEnum(): BookingStatus
    {
        return BookingStatus::from($this->status);
    }

    public function appointmentStartsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse(
            $this->booking_date->format('Y-m-d').' '.$this->start_time,
            $this->timezone ?: config('app.timezone')
        );
    }

    public function canBeCancelledByCustomer(?CarbonImmutable $now = null): bool
    {
        $now ??= CarbonImmutable::now($this->timezone ?: config('app.timezone'));

        return $this->statusEnum()->isActive()
            && $now->lte($this->appointmentStartsAt()->subHours(24));
    }

    public function hasGuestToken(string $token): bool
    {
        return $this->guest_token_hash !== null
            && hash_equals($this->guest_token_hash, hash('sha256', $token));
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        return $this->customer_name ?: $this->customer?->name ?: '';
    }

    public function getCustomerContactEmailAttribute(): string
    {
        return $this->customer_email ?: $this->customer?->email ?: '';
    }
}
