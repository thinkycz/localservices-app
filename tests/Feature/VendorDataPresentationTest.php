<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VendorDataPresentationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_provider_dashboard_and_calendar_present_guest_bookings_and_currency_breakdowns(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-24 08:00:00', 'Europe/Prague'));

        [$vendor, $customer, $czkShop, $czkService, $eurShop, $eurService] = $this->createProviderFixture();

        $this->createBooking($vendor, null, $czkShop, $czkService, [
            'customer_name' => 'Eva Hostovská',
            'customer_email' => 'eva@example.cz',
            'customer_phone' => '+420 777 123 456',
            'price_amount' => 1200,
            'currency' => 'CZK',
            'booking_date' => '2026-08-24',
            'start_time' => '13:00:00',
            'end_time' => '14:00:00',
        ]);
        $this->createBooking($vendor, $customer, $czkShop, $czkService, [
            'price_amount' => 800,
            'currency' => 'CZK',
            'booking_date' => '2026-08-24',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);
        $this->createBooking($vendor, $customer, $eurShop, $eurService, [
            'price_amount' => 50,
            'currency' => 'EUR',
            'booking_date' => '2026-08-25',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ]);

        $this->actingAs($vendor)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vendor/Dashboard')
                ->where('todayBookings.1.customer', 'Eva Hostovská')
                ->where('recentBookings', fn ($bookings) => collect($bookings)->contains(
                    fn ($booking) => $booking['customer_name'] === 'Eva Hostovská'
                ))
                ->where('stats.3.value', Money::format(2000, 'CZK').' | '.Money::format(50, 'EUR'))
                ->where('weekStats.revenue', Money::format(2000, 'CZK').' | '.Money::format(50, 'EUR'))
                ->where('overview.total_customers', 2));

        $this->actingAs($vendor)
            ->get(route('vendor.calendar', [
                'view' => 'week',
                'start_date' => '2026-08-24',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vendor/Calendar')
                ->where('bookings.1.customer', 'Eva Hostovská')
                ->where('bookings.1.customerEmail', 'eva@example.cz')
                ->where('bookings.1.customerPhone', '+420 777 123 456')
                ->where('bookings.1.price', Money::format(1200, 'CZK'))
                ->where('weekStats.revenue', null)
                ->where('weekStats.formatted_revenue', Money::format(2000, 'CZK').' | '.Money::format(50, 'EUR')));
    }

    public function test_customer_pages_exclude_guests_and_never_combine_account_spend_across_currencies(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-24 08:00:00', 'Europe/Prague'));

        [$vendor, $customer, $czkShop, $czkService, $eurShop, $eurService] = $this->createProviderFixture();

        $this->createBooking($vendor, null, $czkShop, $czkService, [
            'customer_name' => 'Eva Hostovská',
            'customer_email' => 'eva@example.cz',
            'price_amount' => 1200,
            'currency' => 'CZK',
        ]);
        $this->createBooking($vendor, $customer, $czkShop, $czkService, [
            'price_amount' => 800,
            'currency' => 'CZK',
        ]);
        $this->createBooking($vendor, $customer, $eurShop, $eurService, [
            'price_amount' => 50,
            'currency' => 'EUR',
        ]);

        $this->actingAs($vendor)
            ->get(route('vendor.customers.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vendor/Customers/Index')
                ->has('customers', 1)
                ->where('customers.0.id', $customer->id)
                ->where('customers.0.total_spent', Money::format(800, 'CZK').' | '.Money::format(50, 'EUR'))
                ->where('stats.total_customers', 1)
                ->where('stats.total_revenue', Money::format(800, 'CZK').' | '.Money::format(50, 'EUR')));

        $this->actingAs($vendor)
            ->get(route('vendor.customers.show', $customer->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vendor/Customers/Show')
                ->where('customer.total_spent', Money::format(800, 'CZK').' | '.Money::format(50, 'EUR'))
                ->where('customer.bookings', fn ($bookings) => collect($bookings)->contains(
                    fn ($booking) => $booking['currency'] === 'EUR'
                        && $booking['formatted_price'] === Money::format(50, 'EUR')
                )));
    }

    /**
     * @return array{User, User, Shop, Service, Shop, Service}
     */
    private function createProviderFixture(): array
    {
        $vendor = User::factory()->create([
            'is_vendor' => true,
            'email_verified_at' => now(),
        ]);
        $customer = User::factory()->create([
            'name' => 'Jan Zákazník',
            'email' => 'jan@example.cz',
        ]);
        $category = Category::create([
            'name' => 'Péče o tělo',
            'slug' => 'pece-o-telo-'.uniqid(),
        ]);
        $czkShop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendor->id,
            'name' => 'Pražské studio',
            'slug' => 'prazske-studio-'.uniqid(),
            'currency' => 'CZK',
            'timezone' => 'Europe/Prague',
        ]);
        $eurShop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendor->id,
            'name' => 'Bratislavské studio',
            'slug' => 'bratislavske-studio-'.uniqid(),
            'currency' => 'EUR',
            'timezone' => 'Europe/Prague',
        ]);
        $czkService = Service::create([
            'shop_id' => $czkShop->id,
            'name' => 'Masáž',
            'duration_minutes' => 60,
            'price' => 1200,
        ]);
        $eurService = Service::create([
            'shop_id' => $eurShop->id,
            'name' => 'Konzultace',
            'duration_minutes' => 30,
            'price' => 50,
        ]);

        return [$vendor, $customer, $czkShop, $czkService, $eurShop, $eurService];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createBooking(
        User $vendor,
        ?User $customer,
        Shop $shop,
        Service $service,
        array $overrides = [],
    ): Booking {
        return Booking::create(array_merge([
            'user_id' => $customer?->id,
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'provider_id' => $vendor->id,
            'customer_name' => $customer?->name,
            'customer_email' => $customer?->email,
            'customer_phone' => $customer?->phone,
            'price_amount' => $service->price,
            'currency' => $shop->currency,
            'timezone' => $shop->timezone,
            'status' => 'confirmed',
            'booking_date' => '2026-08-24',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ], $overrides));
    }
}
