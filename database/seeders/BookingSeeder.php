<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Shop;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $shops = Shop::with('services')->orderBy('id')->get();
        $customers = User::where('is_vendor', false)->orderBy('id')->get();

        if ($shops->isEmpty() || $customers->isEmpty()) {
            return;
        }

        $today = CarbonImmutable::today('Europe/Prague');
        $scenarios = [
            [-12, '09:00', BookingStatus::Completed, 'Děkuji, všechno proběhlo skvěle.'],
            [-7, '11:00', BookingStatus::Completed, null],
            [-3, '14:00', BookingStatus::Cancelled, 'Termín jsme po domluvě zrušili.'],
            [1, '09:00', BookingStatus::Confirmed, 'Prosím zavolejte pět minut předem.'],
            [2, '11:00', BookingStatus::Pending, null],
            [4, '14:00', BookingStatus::Confirmed, 'Vchod je z ulice, zvonek Novák.'],
            [7, '09:00', BookingStatus::Pending, null],
        ];

        foreach ($shops as $shopIndex => $shop) {
            if ($shop->services->isEmpty()) {
                continue;
            }

            foreach ($scenarios as $scenarioIndex => [$dayOffset, $startTime, $status, $notes]) {
                $service = $shop->services[$scenarioIndex % $shop->services->count()];
                $customer = $customers[($shopIndex + $scenarioIndex) % $customers->count()];
                $bookingDate = $today->addDays($dayOffset);
                $endTime = CarbonImmutable::createFromFormat('H:i', $startTime, 'Europe/Prague')
                    ->addMinutes($service->duration_minutes)
                    ->format('H:i');

                Booking::updateOrCreate(
                    [
                        'shop_id' => $shop->id,
                        'booking_date' => $bookingDate->format('Y-m-d'),
                        'start_time' => $startTime,
                    ],
                    [
                        'user_id' => $customer->id,
                        'service_id' => $service->id,
                        'provider_id' => $shop->user_id,
                        'customer_name' => $customer->name,
                        'customer_email' => $customer->email,
                        'customer_phone' => $customer->phone,
                        'price_amount' => $service->price,
                        'currency' => $shop->currency,
                        'timezone' => $shop->timezone ?: 'Europe/Prague',
                        'status' => $status->value,
                        'end_time' => $endTime,
                        'customer_notes' => $notes,
                        'cancellation_reason' => $status === BookingStatus::Cancelled
                            ? 'Zrušeno po dohodě se zákazníkem.'
                            : null,
                    ]
                );
            }
        }

        $guestShop = $shops->first(fn (Shop $shop) => $shop->services->isNotEmpty());

        if ($guestShop) {
            $guestService = $guestShop->services->first();
            $startTime = '16:00';

            Booking::updateOrCreate(
                [
                    'shop_id' => $guestShop->id,
                    'booking_date' => $today->addDays(3)->format('Y-m-d'),
                    'start_time' => $startTime,
                ],
                [
                    'user_id' => null,
                    'service_id' => $guestService->id,
                    'provider_id' => $guestShop->user_id,
                    'customer_name' => 'Host Domluveno',
                    'customer_email' => 'host@example.cz',
                    'customer_phone' => '+420 777 123 456',
                    'guest_token_hash' => hash('sha256', 'domluveno-demo-guest-token'),
                    'price_amount' => $guestService->price,
                    'currency' => $guestShop->currency,
                    'timezone' => $guestShop->timezone ?: 'Europe/Prague',
                    'status' => BookingStatus::Confirmed->value,
                    'end_time' => CarbonImmutable::createFromFormat('H:i', $startTime, 'Europe/Prague')
                        ->addMinutes($guestService->duration_minutes)
                        ->format('H:i'),
                    'customer_notes' => 'Ukázková rezervace bez účtu.',
                ]
            );
        }
    }
}
