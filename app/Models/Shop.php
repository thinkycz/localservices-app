<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    protected $fillable = [
        'category_id',
        'user_id',
        'name',
        'slug',
        'currency',
        'description',
        'price_range',
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

    protected $appends = ['computed_badge'];

    /**
     * Auto-compute a badge based on business rules.
     * Priority: New > Popular > Top Rated
     */
    public function getComputedBadgeAttribute(): ?array
    {
        // New: created within the last 14 days
        if ($this->created_at && $this->created_at->greaterThanOrEqualTo(Carbon::now()->subDays(14))) {
            return ['text' => 'NEW', 'color' => 'blue'];
        }

        // Popular: 10+ bookings in the last 30 days
        $recentBookings = $this->bookings()
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        if ($recentBookings >= 10) {
            return ['text' => 'POPULAR', 'color' => 'green'];
        }

        // Top Rated: rating >= 4.8 with 50+ reviews
        if ($this->rating >= 4.8 && $this->reviews_count >= 50) {
            return ['text' => 'TOP RATED', 'color' => 'blue'];
        }

        return null;
    }

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

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
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
        return $this->hasMany(ShopImage::class)->orderBy('order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ShopImage::class)->where('is_primary', true);
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
     * Get price range as currency symbols string.
     */
    public function getPriceSymbolAttribute(): string
    {
        $symbol = $this->currency === 'EUR' ? '€' : 'Kč';
        return trim(str_repeat($symbol . ' ', $this->price_range));
    }
}
