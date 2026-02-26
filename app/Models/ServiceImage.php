<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceImage extends Model
{
    protected $fillable = [
        'service_id',
        'path',
        'filename',
        'mime_type',
        'size',
        'order',
        'is_primary',
        'alt_text',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'order' => 'integer',
        'size' => 'integer',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the full URL for the image.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    /**
     * Get thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        return asset('storage/' . dirname($this->path) . '/thumbs/' . $this->filename);
    }
}
