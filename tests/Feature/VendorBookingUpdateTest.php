<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
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

    public function test_vendor_cannot_update_booking_for_another_vendors_shop(): void
    {
        $vendor = User::factory()->create([
            'is_vendor' => true,
        ]);

        $otherVendor = User::factory()->create([
            'is_vendor' => true,
        ]);

        $customer = User::factory()->create();

        $category = Category::create([
            'name' => 'Home Services',
            'slug' => 'home-services',
        ]);

        $otherShop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $otherVendor->id,
            'name' => 'Other Vendor Shop',
            'slug' => 'other-vendor-shop',
            'currency' => 'CZK',
        ]);

        $service = Service::create([
            'shop_id' => $otherShop->id,
            'name' => 'Consultation',
            'duration_minutes' => 60,
            'price' => 50.00,
        ]);

        $booking = Booking::create([
            'user_id' => $customer->id,
            'shop_id' => $otherShop->id,
            'service_id' => $service->id,
            'provider_id' => $otherVendor->id,
            'status' => 'pending',
            'booking_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $this
            ->actingAs($vendor)
            ->post(route('vendor.bookings.update', $booking->id), [
                'status' => 'confirmed',
            ])
            ->assertNotFound();

        $this->assertSame('pending', $booking->fresh()->status);
    }
}
