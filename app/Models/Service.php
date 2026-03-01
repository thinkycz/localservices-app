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
        'is_online_only',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_online_only' => 'boolean',
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

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class)->orderBy('day_of_week');
    }

    /**
     * Get approved reviews only.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ServiceImage::class)->orderBy('order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ServiceImage::class)->where('is_primary', true);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->approved();
    }

    /**
     * Update rating and reviews count.
     */
    public function updateRatingStats(): void
    {
        $stats = $this->reviews()
            ->approved()
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();

        $this->update([
            'rating' => round($stats->avg_rating ?? 0, 1),
            'reviews_count' => $stats->count ?? 0,
        ]);
    }

    /**
     * Get price range as dollar signs string.
     */
    public function getPriceSymbolAttribute(): string
    {
        return str_repeat('$', $this->price_range);
    }
}
