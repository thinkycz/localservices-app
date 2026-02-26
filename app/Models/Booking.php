<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'service_offering_id',
        'provider_id',
        'status',
        'payment_status',
        'payment_method',
        'paid_at',
        'payment_notes',
        'booking_date',
        'start_time',
        'end_time',
        'total_price',
        'notes',
        'customer_notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'total_price' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function offering(): BelongsTo
    {
        return $this->belongsTo(ServiceOffering::class, 'service_offering_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
