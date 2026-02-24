<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOffering extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'description',
        'price',
        'duration_minutes',
        'is_popular',
        'category_tag',
        'staff_level',
    ];

    protected $casts = [
        'price'          => 'float',
        'duration_minutes' => 'integer',
        'is_popular'     => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
