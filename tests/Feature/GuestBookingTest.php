<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BusinessHour;
use App\Models\Category;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GuestBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_guest_booking_derives_authoritative_fields_from_the_service(): void
    {
        Carbon::setTestNow('2026-08-23 10:00:00');
        Mail::fake();

        [$vendor, $shop, $service] = $this->createBookableService();
        $otherVendor = User::factory()->create(['is_vendor' => true]);
        $otherShop = Shop::create([
            'category_id' => $shop->category_id,
            'user_id' => $otherVendor->id,
            'name' => 'Cizí studio',
            'slug' => 'cizi-studio',
            'currency' => 'EUR',
        ]);

        $response = $this->post(route('bookings.store'), [
            'service_id' => $service->id,
            'shop_id' => $otherShop->id,
            'provider_id' => $otherVendor->id,
            'booking_date' => '2026-08-24',
            'start_time' => '10:00',
            'full_name' => 'Eva Nováková',
            'email' => 'eva@example.cz',
            'phone' => '+420 777 123 456',
            'customer_notes' => 'Prosím bez parfemace.',
        ]);

        $response->assertRedirect();
        $this->assertStringStartsWith(url('/guest/bookings/'), $response->headers->get('Location'));
        $this->assertDatabaseHas('bookings', [
            'user_id' => null,
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'provider_id' => $vendor->id,
            'status' => 'pending',
            'customer_name' => 'Eva Nováková',
            'customer_email' => 'eva@example.cz',
            'customer_phone' => '+420 777 123 456',
            'price_amount' => '1250.00',
            'currency' => 'CZK',
            'timezone' => 'Europe/Prague',
        ]);
        $this->assertNotNull(Booking::firstOrFail()->guest_token_hash);
    }

    public function test_public_availability_respects_service_duration_and_existing_bookings(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-23 10:00', 'Europe/Prague'));
        [$vendor, $shop, $service] = $this->createBookableService();
        Booking::create([
            'user_id' => User::factory()->create()->id,
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'provider_id' => $vendor->id,
            'status' => 'confirmed',
            'booking_date' => '2026-08-24',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $response = $this->getJson(route('shops.availability', [
            'shop' => $shop,
            'service_id' => $service->id,
            'date' => '2026-08-24',
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('closed', false)
            ->assertJsonPath('timezone', 'Europe/Prague');
        $this->assertContains('09:00', $response->json('slots'));
        $this->assertNotContains('10:00', $response->json('slots'));
        $this->assertNotContains('10:30', $response->json('slots'));
        $this->assertContains('11:00', $response->json('slots'));
    }

    public function test_public_availability_rejects_inactive_records_closed_days_and_foreign_services(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-23 10:00', 'Europe/Prague'));
        [, $shop, $service] = $this->createBookableService();

        $shop->update(['is_available' => false]);
        $this->getJson(route('shops.availability', [
            'shop' => $shop,
            'service_id' => $service->id,
            'date' => '2026-08-24',
        ]))
            ->assertOk()
            ->assertJsonPath('closed', true)
            ->assertJsonPath('reason', 'unavailable');

        $shop->update(['is_available' => true]);
        $service->update(['is_available' => false]);
        $this->getJson(route('shops.availability', [
            'shop' => $shop,
            'service_id' => $service->id,
            'date' => '2026-08-24',
        ]))
            ->assertOk()
            ->assertJsonPath('reason', 'unavailable');

        $service->update(['is_available' => true]);
        $this->getJson(route('shops.availability', [
            'shop' => $shop,
            'service_id' => $service->id,
            'date' => '2026-08-25',
        ]))
            ->assertOk()
            ->assertJsonPath('closed', true)
            ->assertJsonPath('reason', 'closed');

        $otherShop = Shop::create([
            'category_id' => $shop->category_id,
            'user_id' => $shop->user_id,
            'name' => 'Jiné studio',
            'slug' => 'jine-studio',
            'currency' => 'CZK',
            'is_available' => true,
        ]);
        $otherService = Service::create([
            'shop_id' => $otherShop->id,
            'name' => 'Jiná služba',
            'duration_minutes' => 60,
            'price' => 900,
        ]);
        $this->getJson(route('shops.availability', [
            'shop' => $otherShop,
            'service_id' => $service->id,
            'date' => '2026-08-24',
        ]))->assertNotFound();
        $this->assertNotSame($otherService->shop_id, $service->shop_id);
    }

    public function test_final_availability_check_rejects_a_second_submission_for_the_same_slot(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-23 10:00', 'Europe/Prague'));
        Mail::fake();
        [, , $service] = $this->createBookableService();
        $payload = [
            'service_id' => $service->id,
            'booking_date' => '2026-08-24',
            'start_time' => '10:00',
            'full_name' => 'Eva První',
            'email' => 'prvni@example.cz',
            'phone' => '+420 777 111 111',
        ];

        $this->post(route('bookings.store'), $payload)->assertRedirect();
        $this->from(route('shops.book', $service->shop->slug))
            ->post(route('bookings.store'), [
                ...$payload,
                'full_name' => 'Eva Druhá',
                'email' => 'druha@example.cz',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_guest_cancellation_requires_a_valid_token_and_twenty_four_hours_notice(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-23 10:00', 'Europe/Prague'));
        [$vendor, $shop, $service] = $this->createBookableService();
        $token = 'valid-guest-token';
        $booking = $this->createGuestBooking($vendor, $shop, $service, $token, '2026-08-24 10:00');

        $this->post(route('guest.bookings.cancel', [
            'booking' => $booking,
            'token' => 'wrong-token',
        ]))->assertNotFound();

        $this->post(route('guest.bookings.cancel', [
            'booking' => $booking,
            'token' => $token,
        ]))->assertSessionHasNoErrors();
        $this->assertSame('cancelled', $booking->fresh()->status);

        $lateBooking = $this->createGuestBooking($vendor, $shop, $service, 'late-token', '2026-08-24 09:59');
        $response = $this->post(route('guest.bookings.cancel', [
            'booking' => $lateBooking,
            'token' => 'late-token',
        ]));

        $response->assertSessionHasErrors('booking');
        $this->assertSame('pending', $lateBooking->fresh()->status);
    }

    public function test_verified_customer_can_claim_a_matching_guest_booking(): void
    {
        [$vendor, $shop, $service] = $this->createBookableService();
        $customer = User::factory()->create([
            'email' => 'eva@example.cz',
            'email_verified_at' => now(),
        ]);
        $booking = $this->createGuestBooking($vendor, $shop, $service, 'claim-token', '2026-08-24 10:00');
        $otherMatchingBooking = $this->createGuestBooking($vendor, $shop, $service, 'second-token', '2026-08-25 11:00');

        $response = $this->actingAs($customer)->post(route('guest.bookings.claim', [
            'booking' => $booking,
            'token' => 'claim-token',
        ]));

        $response->assertRedirect(route('bookings.index'));
        $this->assertSame($customer->id, $booking->fresh()->user_id);
        $this->assertNull($booking->fresh()->guest_token_hash);
        $this->assertSame($customer->id, $otherMatchingBooking->fresh()->user_id);
        $this->assertNull($otherMatchingBooking->fresh()->guest_token_hash);
        $this->get(route('guest.bookings.show', [
            'booking' => $booking,
            'token' => 'claim-token',
        ]))->assertNotFound();
    }

    public function test_authenticated_customer_cancellation_obeys_the_twenty_four_hour_boundary(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-23 10:00', 'Europe/Prague'));
        Mail::fake();
        [$vendor, $shop, $service] = $this->createBookableService();
        $customer = User::factory()->create();

        $beforeBoundary = $this->createGuestBooking($vendor, $shop, $service, 'before-token', '2026-08-24 10:01');
        $beforeBoundary->update(['user_id' => $customer->id, 'guest_token_hash' => null]);
        $this->actingAs($customer)
            ->post(route('bookings.cancel', $beforeBoundary))
            ->assertSessionHasNoErrors();
        $this->assertSame('cancelled', $beforeBoundary->fresh()->status);

        $atBoundary = $this->createGuestBooking($vendor, $shop, $service, 'at-token', '2026-08-24 10:00');
        $atBoundary->update(['user_id' => $customer->id, 'guest_token_hash' => null]);
        $this->actingAs($customer)
            ->post(route('bookings.cancel', $atBoundary))
            ->assertSessionHasNoErrors();
        $this->assertSame('cancelled', $atBoundary->fresh()->status);

        $afterBoundary = $this->createGuestBooking($vendor, $shop, $service, 'after-token', '2026-08-24 09:59');
        $afterBoundary->update(['user_id' => $customer->id, 'guest_token_hash' => null]);
        $this->actingAs($customer)
            ->post(route('bookings.cancel', $afterBoundary))
            ->assertSessionHasErrors('booking');
        $this->assertSame('pending', $afterBoundary->fresh()->status);
    }

    /**
     * @return array{User, Shop, Service}
     */
    private function createBookableService(): array
    {
        $vendor = User::factory()->create([
            'is_vendor' => true,
            'email_verified_at' => now(),
        ]);
        $category = Category::create([
            'name' => 'Péče o tělo',
            'slug' => 'pece-o-telo',
        ]);
        $shop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendor->id,
            'name' => 'Klidné studio',
            'slug' => 'klidne-studio',
            'currency' => 'CZK',
            'is_available' => true,
        ]);
        BusinessHour::create([
            'shop_id' => $shop->id,
            'day_of_week' => 1,
            'time_from' => '09:00',
            'time_to' => '17:00',
        ]);
        $service = Service::create([
            'shop_id' => $shop->id,
            'name' => 'Relaxační masáž',
            'duration_minutes' => 60,
            'price' => 1250,
        ]);

        return [$vendor, $shop, $service];
    }

    private function createGuestBooking(
        User $vendor,
        Shop $shop,
        Service $service,
        string $token,
        string $startsAt,
    ): Booking {
        $start = CarbonImmutable::parse($startsAt, 'Europe/Prague');

        return Booking::create([
            'user_id' => null,
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'provider_id' => $vendor->id,
            'customer_name' => 'Eva Nováková',
            'customer_email' => 'eva@example.cz',
            'customer_phone' => '+420 777 123 456',
            'guest_token_hash' => hash('sha256', $token),
            'price_amount' => $service->price,
            'currency' => $shop->currency,
            'timezone' => 'Europe/Prague',
            'status' => 'pending',
            'booking_date' => $start->toDateString(),
            'start_time' => $start->format('H:i:s'),
            'end_time' => $start->addMinutes($service->duration_minutes)->format('H:i:s'),
        ]);
    }
}
