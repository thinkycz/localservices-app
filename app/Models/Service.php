<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'duration_minutes',
        'is_popular',
        'category_tag',
        'staff_level',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'is_popular' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
