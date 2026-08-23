<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorBookingUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_update_booking_status_and_redirect_back(): void
    {
        $vendor = User::factory()->create([
            'is_vendor' => true,
        ]);

        $customer = User::factory()->create();

        $category = Category::create([
            'name' => 'Home Services',
            'slug' => 'home-services',
        ]);

        $shop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendor->id,
            'name' => 'Vendor Shop',
            'slug' => 'vendor-shop',
            'currency' => 'CZK',
        ]);

        $service = Service::create([
            'shop_id' => $shop->id,
            'name' => 'Consultation',
            'duration_minutes' => 60,
            'price' => 50.00,
        ]);

        $booking = Booking::create([
            'user_id' => $customer->id,
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'provider_id' => $vendor->id,
            'status' => 'pending',
            'booking_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $response = $this
            ->from(route('vendor.bookings.show', $booking->id))
            ->actingAs($vendor)
            ->post(route('vendor.bookings.update', $booking->id), [
                'status' => 'confirmed',
            ]);

        $response
            ->assertRedirect(route('vendor.bookings.show', $booking->id))
            ->assertSessionHas('success', 'Booking status updated successfully.');

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_vendor_cannot_update_another_vendors_booking(): void
    {
        $owner = User::factory()->create([
            'is_vendor' => true,
        ]);
        $attacker = User::factory()->create([
            'is_vendor' => true,
        ]);
        $customer = User::factory()->create();
        $category = Category::create([
            'name' => 'Péče o tělo',
            'slug' => 'pece-o-telo',
        ]);
        $shop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $owner->id,
            'name' => 'Studio majitele',
            'slug' => 'studio-majitele',
            'currency' => 'CZK',
        ]);
        $service = Service::create([
            'shop_id' => $shop->id,
            'name' => 'Konzultace',
            'duration_minutes' => 60,
            'price' => 1200,
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'provider_id' => $owner->id,
            'status' => 'pending',
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $response = $this
            ->actingAs($attacker)
            ->post(route('vendor.bookings.update', $booking->id), [
                'status' => 'confirmed',
            ]);

        $response->assertNotFound();
        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_vendor_cannot_skip_from_pending_to_completed(): void
    {
        [$vendor, $booking] = $this->createBookingForVendor('pending', now()->subHour());

        $response = $this
            ->from(route('vendor.bookings.show', $booking->id))
            ->actingAs($vendor)
            ->post(route('vendor.bookings.update', $booking->id), [
                'status' => 'completed',
            ]);

        $response
            ->assertRedirect(route('vendor.bookings.show', $booking->id))
            ->assertSessionHasErrors('status');
        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_vendor_cannot_complete_a_confirmed_booking_before_it_starts(): void
    {
        [$vendor, $booking] = $this->createBookingForVendor('confirmed', now()->addDay());

        $response = $this
            ->from(route('vendor.bookings.show', $booking->id))
            ->actingAs($vendor)
            ->post(route('vendor.bookings.complete', $booking->id));

        $response
            ->assertRedirect(route('vendor.bookings.show', $booking->id))
            ->assertSessionHasErrors('status');
        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_vendor_cancellation_requires_and_records_a_reason(): void
    {
        [$vendor, $booking] = $this->createBookingForVendor('pending', now()->addDay());

        $this->from(route('vendor.bookings.show', $booking->id))
            ->actingAs($vendor)
            ->post(route('vendor.bookings.cancel', $booking->id), [
                'cancellation_reason' => '',
            ])
            ->assertRedirect(route('vendor.bookings.show', $booking->id))
            ->assertSessionHasErrors('cancellation_reason');

        $this->assertSame('pending', $booking->fresh()->status);

        $this->from(route('vendor.bookings.show', $booking->id))
            ->actingAs($vendor)
            ->post(route('vendor.bookings.cancel', $booking->id), [
                'cancellation_reason' => 'Provozovna musí z provozních důvodů zavřít.',
            ])
            ->assertRedirect(route('vendor.bookings.show', $booking->id))
            ->assertSessionHasNoErrors();

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('Provozovna musí z provozních důvodů zavřít.', $booking->cancellation_reason);
    }

    public function test_vendor_can_confirm_complete_and_add_internal_notes_through_dedicated_actions(): void
    {
        [$vendor, $booking] = $this->createBookingForVendor('pending', now()->subHour());

        $this->from(route('vendor.bookings.show', $booking))
            ->actingAs($vendor)
            ->post(route('vendor.bookings.confirm', $booking))
            ->assertRedirect(route('vendor.bookings.show', $booking))
            ->assertSessionHasNoErrors();
        $this->assertSame('confirmed', $booking->fresh()->status);

        $this->from(route('vendor.bookings.show', $booking))
            ->actingAs($vendor)
            ->post(route('vendor.bookings.notes', $booking), [
                'notes' => 'Zákazník preferuje klidné prostředí.',
            ])
            ->assertRedirect(route('vendor.bookings.show', $booking))
            ->assertSessionHasNoErrors();
        $this->assertStringContainsString('Zákazník preferuje klidné prostředí.', $booking->fresh()->notes);

        $this->from(route('vendor.bookings.show', $booking))
            ->actingAs($vendor)
            ->post(route('vendor.bookings.complete', $booking))
            ->assertRedirect(route('vendor.bookings.show', $booking))
            ->assertSessionHasNoErrors();
        $this->assertSame('completed', $booking->fresh()->status);
    }

    /**
     * @return array{User, Booking}
     */
    private function createBookingForVendor(string $status, \DateTimeInterface $startsAt): array
    {
        $vendor = User::factory()->create(['is_vendor' => true]);
        $customer = User::factory()->create();
        $category = Category::create([
            'name' => 'Kategorie '.uniqid(),
            'slug' => 'kategorie-'.uniqid(),
        ]);
        $shop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendor->id,
            'name' => 'Studio '.uniqid(),
            'slug' => 'studio-'.uniqid(),
            'currency' => 'CZK',
        ]);
        $service = Service::create([
            'shop_id' => $shop->id,
            'name' => 'Konzultace',
            'duration_minutes' => 60,
            'price' => 1200,
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'provider_id' => $vendor->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'status' => $status,
            'booking_date' => $startsAt->format('Y-m-d'),
            'start_time' => $startsAt->format('H:i:s'),
            'end_time' => Carbon::instance(\DateTime::createFromInterface($startsAt))->addHour()->format('H:i:s'),
            'timezone' => config('app.timezone'),
        ]);

        return [$vendor, $booking];
    }
}
