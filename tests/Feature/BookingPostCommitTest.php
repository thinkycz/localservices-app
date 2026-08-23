<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmation;
use App\Mail\NewBookingNotification;
use App\Models\BusinessHour;
use App\Models\Category;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class BookingPostCommitTest extends TestCase
{
    use DatabaseMigrations;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_confirmation_messages_are_queued_only_after_booking_commit(): void
    {
        Mail::fake();
        $service = $this->bookableService();

        $result = app(BookingService::class)->create($this->payload($service), null);

        $this->assertDatabaseHas('bookings', ['id' => $result['booking']->id]);
        Mail::assertQueued(BookingConfirmation::class, 1);
        Mail::assertQueued(NewBookingNotification::class, 1);
    }

    public function test_mail_transport_failure_does_not_make_a_committed_booking_look_failed(): void
    {
        Mail::shouldReceive('to')->andThrow(new RuntimeException('mail unavailable'));
        $service = $this->bookableService();

        $result = app(BookingService::class)->create($this->payload($service), null);

        $this->assertDatabaseHas('bookings', [
            'id' => $result['booking']->id,
            'status' => 'pending',
        ]);
        $this->assertNotNull($result['guest_token']);
    }

    private function bookableService(): Service
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-23 10:00', 'Europe/Prague'));
        $provider = User::factory()->create(['is_vendor' => true]);
        $category = Category::create(['name' => 'Test', 'slug' => 'test']);
        $shop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $provider->id,
            'name' => 'Studio Test',
            'slug' => 'studio-test',
            'currency' => 'CZK',
            'timezone' => 'Europe/Prague',
            'is_available' => true,
        ]);
        BusinessHour::create([
            'shop_id' => $shop->id,
            'day_of_week' => 1,
            'time_from' => '09:00',
            'time_to' => '17:00',
        ]);

        return Service::create([
            'shop_id' => $shop->id,
            'name' => 'Testovací služba',
            'price' => 800,
            'duration_minutes' => 60,
            'is_available' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Service $service): array
    {
        return [
            'service_id' => $service->id,
            'booking_date' => '2026-08-24',
            'start_time' => '10:00',
            'full_name' => 'Host Test',
            'email' => 'host@example.cz',
            'phone' => '+420 777 123 456',
            'customer_notes' => null,
        ];
    }
}
