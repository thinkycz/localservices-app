<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Shop;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            ['Skvělá domluva, přesný čas a profesionální výsledek.', ['Spolehlivost', 'Kvalita']],
            ['Příjemný přístup a vše bylo hotové podle dohody.', ['Příjemné jednání', 'Dochvilnost']],
            ['Rychlé objednání a výborná služba. Ráda přijdu znovu.', ['Rychlá domluva', 'Doporučuji']],
        ];

        $bookings = Booking::query()
            ->where('status', BookingStatus::Completed->value)
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->get();

        foreach ($bookings as $index => $booking) {
            [$comment, $tags] = $comments[$index % count($comments)];

            Review::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'user_id' => $booking->user_id,
                    'shop_id' => $booking->shop_id,
                    'rating' => 5 - ($index % 2),
                    'comment' => $comment,
                    'tags' => $tags,
                    'is_approved' => true,
                    'reviewed_at' => $booking->appointmentStartsAt()->addDays(1),
                ]
            );
        }

        Shop::query()->each(fn (Shop $shop) => $shop->updateRatingStats());
    }
}
