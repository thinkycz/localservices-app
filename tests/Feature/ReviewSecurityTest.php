<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Review;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_shop_is_derived_from_the_completed_booking(): void
    {
        $customer = User::factory()->create();
        $vendor = User::factory()->create(['is_vendor' => true]);
        $category = Category::create(['name' => 'Wellness', 'slug' => 'wellness']);
        $bookedShop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendor->id,
            'name' => 'Booked studio',
            'slug' => 'booked-studio',
            'currency' => 'CZK',
        ]);
        $otherShop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendor->id,
            'name' => 'Other studio',
            'slug' => 'other-studio',
            'currency' => 'CZK',
        ]);
        $service = Service::create([
            'shop_id' => $bookedShop->id,
            'name' => 'Massage',
            'duration_minutes' => 60,
            'price' => 1000,
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'shop_id' => $bookedShop->id,
            'service_id' => $service->id,
            'provider_id' => $vendor->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'status' => 'completed',
            'booking_date' => now()->subDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $response = $this->actingAs($customer)->post(route('reviews.store'), [
            'booking_id' => $booking->id,
            'shop_id' => $otherShop->id,
            'rating' => 5,
            'comment' => 'Velmi příjemná a profesionální návštěva.',
        ]);

        $response->assertRedirect(route('bookings.index'));
        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'shop_id' => $bookedShop->id,
            'user_id' => $customer->id,
        ]);
        $this->assertDatabaseMissing('reviews', [
            'booking_id' => $booking->id,
            'shop_id' => $otherShop->id,
        ]);

        $this->actingAs($customer)->post(route('reviews.store'), [
            'booking_id' => $booking->id,
            'rating' => 4,
            'comment' => 'Druhé hodnocení nesmí vzniknout.',
        ])->assertSessionHas('error');
        $this->assertDatabaseCount('reviews', 1);

        $otherCustomer = User::factory()->create();
        $this->actingAs($otherCustomer)
            ->get(route('reviews.create', $booking->id))
            ->assertNotFound();
        $this->actingAs($otherCustomer)
            ->post(route('reviews.store'), [
                'booking_id' => $booking->id,
                'rating' => 5,
                'comment' => 'Cizí zákazník nesmí tuto návštěvu hodnotit.',
            ])
            ->assertNotFound();

        try {
            Review::create([
                'booking_id' => $booking->id,
                'shop_id' => $bookedShop->id,
                'user_id' => $customer->id,
                'rating' => 3,
                'comment' => 'Databáze musí zamítnout druhou recenzi stejné rezervace.',
                'is_approved' => true,
                'reviewed_at' => now(),
            ]);
            $this->fail('The database accepted a second review for one booking.');
        } catch (QueryException) {
            $this->assertDatabaseCount('reviews', 1);
        }
    }
}
