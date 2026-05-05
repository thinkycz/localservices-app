<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Review;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingReviewIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_store_rejects_service_that_does_not_belong_to_selected_shop(): void
    {
        $customer = User::factory()->create();
        $vendorA = User::factory()->create(['is_vendor' => true]);
        $vendorB = User::factory()->create(['is_vendor' => true]);

        $category = Category::create([
            'name' => 'Home Services',
            'slug' => 'home-services',
        ]);

        $shopA = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendorA->id,
            'name' => 'Shop A',
            'slug' => 'shop-a',
            'currency' => 'CZK',
        ]);

        $shopB = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendorB->id,
            'name' => 'Shop B',
            'slug' => 'shop-b',
            'currency' => 'CZK',
        ]);

        $serviceB = Service::create([
            'shop_id' => $shopB->id,
            'name' => 'Service B',
            'duration_minutes' => 60,
            'price' => 80.00,
        ]);

        $this->actingAs($customer)
            ->post(route('bookings.store'), [
                'shop_id' => $shopA->id,
                'service_id' => $serviceB->id,
                'provider_id' => $vendorA->id,
                'booking_date' => now()->addDay()->toDateString(),
                'start_time' => '10:00',
                'full_name' => 'John Customer',
                'email' => $customer->email,
                'phone' => '123456789',
            ])
            ->assertSessionHasErrors('service_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_review_store_rejects_tampered_shop_id_for_booking(): void
    {
        $customer = User::factory()->create();
        $vendor = User::factory()->create(['is_vendor' => true]);

        $category = Category::create([
            'name' => 'Home Services',
            'slug' => 'home-services',
        ]);

        $shopA = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendor->id,
            'name' => 'Shop A',
            'slug' => 'shop-a',
            'currency' => 'CZK',
        ]);

        $shopB = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendor->id,
            'name' => 'Shop B',
            'slug' => 'shop-b',
            'currency' => 'CZK',
        ]);

        $service = Service::create([
            'shop_id' => $shopA->id,
            'name' => 'Service A',
            'duration_minutes' => 60,
            'price' => 50.00,
        ]);

        $booking = Booking::create([
            'user_id' => $customer->id,
            'shop_id' => $shopA->id,
            'service_id' => $service->id,
            'provider_id' => $vendor->id,
            'status' => 'completed',
            'booking_date' => now()->subDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $this->actingAs($customer)
            ->post(route('reviews.store'), [
                'booking_id' => $booking->id,
                'shop_id' => $shopB->id,
                'rating' => 5,
                'comment' => 'Great service, very professional and on time.',
                'tags' => ['Professional'],
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_review_store_allows_payload_without_shop_id_and_uses_booking_shop(): void
    {
        $customer = User::factory()->create();
        $vendor = User::factory()->create(['is_vendor' => true]);

        $category = Category::create([
            'name' => 'Home Services',
            'slug' => 'home-services',
        ]);

        $shop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendor->id,
            'name' => 'Shop A',
            'slug' => 'shop-a',
            'currency' => 'CZK',
        ]);

        $service = Service::create([
            'shop_id' => $shop->id,
            'name' => 'Service A',
            'duration_minutes' => 60,
            'price' => 50.00,
        ]);

        $booking = Booking::create([
            'user_id' => $customer->id,
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'provider_id' => $vendor->id,
            'status' => 'completed',
            'booking_date' => now()->subDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $this->actingAs($customer)
            ->post(route('reviews.store'), [
                'booking_id' => $booking->id,
                'rating' => 5,
                'comment' => 'Great service, very professional and on time.',
                'tags' => ['Professional'],
            ])
            ->assertRedirect(route('bookings.index'));

        $review = Review::first();
        $this->assertNotNull($review);
        $this->assertSame($shop->id, $review->shop_id);
    }
}
