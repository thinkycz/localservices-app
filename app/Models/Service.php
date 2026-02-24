<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'category_id',
        'user_id',
        'name',
        'slug',
        'description',
        'price_range',
        'badge',
        'badge_color',
        'image',
        'is_available',
        'available_at',
        'rating',
        'reviews_count',
        'city',
        'state',
        'address',
        'opening_hours',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'rating' => 'float',
        'price_range' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function offerings(): HasMany
    {
        return $this->hasMany(ServiceOffering::class);
    }

    /**
     * Get price range as dollar signs string.
     */
    public function getPriceSymbolAttribute(): string
    {
        return str_repeat('$', $this->price_range);
    }
}
