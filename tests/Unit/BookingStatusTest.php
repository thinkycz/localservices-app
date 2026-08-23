<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BookingStatusTest extends TestCase
{
    #[DataProvider('transitionProvider')]
    public function test_status_transition_rules(BookingStatus $from, BookingStatus $to, bool $allowed): void
    {
        $this->assertSame($allowed, $from->canTransitionTo($to));
    }

    public static function transitionProvider(): array
    {
        return [
            'pending to confirmed' => [BookingStatus::Pending, BookingStatus::Confirmed, true],
            'pending to cancelled' => [BookingStatus::Pending, BookingStatus::Cancelled, true],
            'pending to completed' => [BookingStatus::Pending, BookingStatus::Completed, false],
            'confirmed to completed' => [BookingStatus::Confirmed, BookingStatus::Completed, true],
            'confirmed to cancelled' => [BookingStatus::Confirmed, BookingStatus::Cancelled, true],
            'confirmed to pending' => [BookingStatus::Confirmed, BookingStatus::Pending, false],
            'completed is terminal' => [BookingStatus::Completed, BookingStatus::Cancelled, false],
            'cancelled is terminal' => [BookingStatus::Cancelled, BookingStatus::Confirmed, false],
            'same status is not a transition' => [BookingStatus::Pending, BookingStatus::Pending, false],
        ];
    }
}
