<?php

namespace Tests\Feature;

use App\Mail\ContactSubmissionReceived;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CoreRouteFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_content_authentication_and_locale_surfaces_render(): void
    {
        foreach (['home', 'shops.index', 'pages.contact', 'pages.faq', 'pages.privacy', 'pages.terms', 'login', 'register', 'password.request'] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }

        $this->from(route('home'))
            ->get(route('language.switch', 'en'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('locale', 'en');

        $this->from(route('home'))
            ->get(route('language.switch', 'de'))
            ->assertRedirect(route('home'));
        $this->assertSame('en', session('locale'));
    }

    public function test_contact_submission_validates_persists_and_queues_after_commit(): void
    {
        Mail::fake();

        $this->from(route('pages.contact'))
            ->post(route('pages.contact.submit'), [
                'name' => 'Alena Testovací',
                'email' => 'alena@example.cz',
                'type' => 'support',
                'subject' => 'Dotaz k rezervaci',
                'message' => 'Potřebuji ověřit podrobnosti své rezervace.',
            ])
            ->assertRedirect(route('pages.contact'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contact_submissions', [
            'email' => 'alena@example.cz',
            'type' => 'support',
        ]);
        Mail::assertQueued(ContactSubmissionReceived::class);

        $this->from(route('pages.contact'))
            ->post(route('pages.contact.submit'), [
                'name' => '',
                'email' => 'not-an-email',
                'type' => 'unknown',
                'subject' => '',
                'message' => 'short',
            ])
            ->assertRedirect(route('pages.contact'))
            ->assertSessionHasErrors(['name', 'email', 'type', 'subject', 'message']);
    }

    public function test_notification_endpoints_are_scoped_to_the_authenticated_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ownedUnread = Notification::create([
            'user_id' => $owner->id,
            'type' => 'booking',
            'title' => 'Vlastní oznámení',
            'message' => 'Jen pro vlastníka.',
        ]);
        $ownedSecond = Notification::create([
            'user_id' => $owner->id,
            'type' => 'booking',
            'title' => 'Druhé oznámení',
            'message' => 'Jen pro vlastníka.',
        ]);
        $foreign = Notification::create([
            'user_id' => $other->id,
            'type' => 'booking',
            'title' => 'Cizí oznámení',
            'message' => 'Nesmí se zobrazit.',
        ]);

        $this->actingAs($owner)
            ->getJson(route('notifications.recent'))
            ->assertOk()
            ->assertJsonCount(2, 'notifications')
            ->assertJsonMissing(['id' => $foreign->id]);

        $this->actingAs($owner)
            ->postJson(route('notifications.read', $ownedUnread))
            ->assertOk()
            ->assertJsonPath('unread_count', 1);

        $this->actingAs($owner)
            ->postJson(route('notifications.markAllRead'))
            ->assertOk()
            ->assertJsonPath('unread_count', 0);
        $this->assertNotNull($ownedSecond->fresh()->read_at);
        $this->assertNull($foreign->fresh()->read_at);

        $this->actingAs($owner)
            ->deleteJson(route('notifications.destroy', $ownedUnread))
            ->assertOk();
        $this->assertDatabaseMissing('notifications', ['id' => $ownedUnread->id]);
        $this->assertDatabaseHas('notifications', ['id' => $foreign->id]);
    }

    public function test_customer_confirmation_and_review_pages_are_owner_scoped(): void
    {
        [$customer, $otherCustomer, $booking] = $this->completedBooking();

        $this->actingAs($customer)
            ->get(route('bookings.confirmation', $booking))
            ->assertOk();
        $this->actingAs($customer)
            ->get(route('reviews.create', $booking))
            ->assertOk();
        $this->actingAs($customer)
            ->get(route('reviews.user'))
            ->assertOk();

        $this->actingAs($otherCustomer)
            ->get(route('bookings.confirmation', $booking))
            ->assertNotFound();
        $this->actingAs($otherCustomer)
            ->get(route('reviews.create', $booking))
            ->assertNotFound();
    }

    public function test_provider_can_manage_shop_hours_and_services_while_other_providers_cannot(): void
    {
        $provider = User::factory()->create(['is_vendor' => true, 'email_verified_at' => now()]);
        $otherProvider = User::factory()->create(['is_vendor' => true, 'email_verified_at' => now()]);
        $category = Category::create(['name' => 'Wellness', 'slug' => 'wellness']);
        $shop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $provider->id,
            'name' => 'Studio Klid',
            'slug' => 'studio-klid',
            'currency' => 'CZK',
            'is_available' => true,
        ]);

        $this->actingAs($provider)->get(route('vendor.shops.index'))->assertOk();
        $this->actingAs($provider)->get(route('vendor.shops.create'))->assertOk();
        $this->actingAs($provider)->get(route('vendor.shops.show', $shop))->assertOk();
        $this->actingAs($provider)->get(route('vendor.shops.edit', $shop))->assertOk();
        $this->actingAs($otherProvider)->get(route('vendor.shops.show', $shop))->assertNotFound();
        $this->actingAs($otherProvider)->get(route('vendor.shops.edit', $shop))->assertNotFound();

        $this->actingAs($provider)
            ->post(route('vendor.shops.business-hours.store', $shop), [
                'hours' => [
                    ['day_of_week' => 1, 'is_closed' => false, 'time_from' => '09:00', 'time_to' => '17:00'],
                    ['day_of_week' => 2, 'is_closed' => true, 'time_from' => null, 'time_to' => null],
                ],
            ])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('business_hours', ['shop_id' => $shop->id, 'day_of_week' => 1]);
        $this->assertDatabaseMissing('business_hours', ['shop_id' => $shop->id, 'day_of_week' => 2]);

        $this->actingAs($provider)
            ->post(route('vendor.shops.services.store', $shop), [
                'name' => 'Relaxační masáž',
                'description' => 'Klidná masáž na šedesát minut.',
                'price' => 1250,
                'duration_minutes' => 60,
                'is_popular' => false,
            ])
            ->assertSessionHasNoErrors();
        $service = $shop->services()->firstOrFail();

        $this->actingAs($provider)
            ->put(route('vendor.shops.services.update', [$shop, $service]), [
                'name' => 'Relaxační masáž 60',
                'description' => 'Upravený popis služby.',
                'price' => 1350,
                'duration_minutes' => 60,
                'is_popular' => true,
            ])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('services', ['id' => $service->id, 'name' => 'Relaxační masáž 60']);

        $this->actingAs($otherProvider)
            ->delete(route('vendor.shops.services.destroy', [$shop, $service]))
            ->assertNotFound();
        $this->assertDatabaseHas('services', ['id' => $service->id]);

        $this->actingAs($provider)
            ->post(route('vendor.shops.toggle', $shop))
            ->assertSessionHasNoErrors();
        $this->assertFalse($shop->fresh()->is_available);

        $this->actingAs($provider)
            ->delete(route('vendor.shops.services.destroy', [$shop, $service]))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('services', ['id' => $service->id]);

        $this->actingAs($otherProvider)
            ->delete(route('vendor.shops.destroy', $shop))
            ->assertNotFound();
        $this->actingAs($provider)
            ->delete(route('vendor.shops.destroy', $shop))
            ->assertRedirect(route('vendor.shops.index'));
        $this->assertDatabaseMissing('shops', ['id' => $shop->id]);
    }

    public function test_role_guards_redirect_guests_customers_and_existing_providers_safely(): void
    {
        $this->get(route('vendor.dashboard'))->assertRedirect(route('login'));
        $this->get(route('bookings.index'))->assertRedirect(route('login'));

        $customer = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($customer)
            ->get(route('vendor.dashboard'))
            ->assertRedirect(route('home'));

        $provider = User::factory()->create(['is_vendor' => true, 'email_verified_at' => now()]);
        $this->actingAs($provider)
            ->get(route('vendor.onboarding.step1'))
            ->assertRedirect(route('vendor.dashboard'));
    }

    /**
     * @return array{User, User, Booking}
     */
    private function completedBooking(): array
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $provider = User::factory()->create(['is_vendor' => true, 'email_verified_at' => now()]);
        $category = Category::create(['name' => 'Péče', 'slug' => 'pece']);
        $shop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $provider->id,
            'name' => 'Studio Péče',
            'slug' => 'studio-pece',
            'currency' => 'CZK',
            'is_available' => true,
        ]);
        $service = Service::create([
            'shop_id' => $shop->id,
            'name' => 'Péče na hodinu',
            'price' => 900,
            'duration_minutes' => 60,
        ]);
        $booking = Booking::create([
            'user_id' => $customer->id,
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'provider_id' => $provider->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'price_amount' => 900,
            'currency' => 'CZK',
            'timezone' => 'Europe/Prague',
            'status' => 'completed',
            'booking_date' => now()->subDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        return [$customer, $otherCustomer, $booking];
    }
}
