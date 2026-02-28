<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessHour extends Model
{
    protected $fillable = [
        'service_id',
        'day_of_week',
        'time_from',
        'time_to',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
