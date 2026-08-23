<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pending => in_array($next, [self::Confirmed, self::Cancelled], true),
            self::Confirmed => in_array($next, [self::Completed, self::Cancelled], true),
            self::Completed, self::Cancelled => false,
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed], true);
    }

    /**
     * @return list<string>
     */
    public static function activeValues(): array
    {
        return [self::Pending->value, self::Confirmed->value];
    }
}
