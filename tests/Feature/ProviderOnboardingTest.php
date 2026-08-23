<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_provider_cannot_reenter_onboarding(): void
    {
        $provider = User::factory()->create([
            'is_vendor' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($provider)
            ->get(route('vendor.onboarding.index'))
            ->assertRedirect(route('vendor.dashboard'));
    }

    public function test_onboarding_steps_cannot_be_opened_out_of_order(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'provider_onboarding_pending' => true,
        ]);

        $this->actingAs($user)
            ->get(route('vendor.onboarding.step2'))
            ->assertRedirect(route('vendor.onboarding.step1'));

        $this->actingAs($user)
            ->withSession(['onboarding.step1' => [
                'business_name' => 'Studio Eva',
                'business_phone' => '+420777123456',
                'business_email' => 'studio@example.cz',
            ]])
            ->get(route('vendor.onboarding.step3'))
            ->assertRedirect(route('vendor.onboarding.step2'));
    }

    public function test_provider_onboarding_requires_a_verified_email(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('vendor.onboarding.index'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_onboarding_steps_persist_valid_progress_and_resume(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'provider_onboarding_pending' => true,
        ]);
        $category = Category::create(['name' => 'Péče o tělo', 'slug' => 'pece-o-telo']);

        $this->actingAs($user)->get(route('vendor.onboarding.index'))->assertOk();
        $this->actingAs($user)->get(route('vendor.onboarding.step1'))->assertOk();

        $this->actingAs($user)
            ->post(route('vendor.onboarding.step1.store'), [
                'business_name' => 'Studio Klid',
                'business_phone' => '+420777123456',
                'business_email' => 'studio@example.cz',
            ])
            ->assertRedirect(route('vendor.onboarding.step2'));
        $this->actingAs($user)->get(route('vendor.onboarding.step2'))->assertOk();

        $this->actingAs($user)
            ->post(route('vendor.onboarding.step2.store'), [
                'category_id' => $category->id,
                'shop_name' => 'Studio Klid',
                'description' => 'Klidné studio v centru Prahy s individuálním přístupem ke každému zákazníkovi.',
                'city' => 'Praha',
                'address' => 'Vinohradská 12',
                'currency' => 'CZK',
                'business_hours' => [
                    ['day_of_week' => 1, 'is_closed' => false, 'time_from' => '09:00', 'time_to' => '17:00'],
                    ['day_of_week' => 2, 'is_closed' => true, 'time_from' => null, 'time_to' => null],
                ],
            ])
            ->assertRedirect(route('vendor.onboarding.step3'));
        $this->actingAs($user)->get(route('vendor.onboarding.step3'))->assertOk();

        $this->assertSame('Studio Klid', session('onboarding.step1.business_name'));
        $this->assertSame('Praha', session('onboarding.step2.city'));
    }

    public function test_onboarding_creates_a_czech_shop_services_and_hours_atomically(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'provider_onboarding_pending' => true,
        ]);
        $category = Category::create(['name' => 'Wellness', 'slug' => 'wellness']);
        $session = [
            'onboarding.step1' => [
                'business_name' => 'Studio Eva',
                'business_phone' => '+420777123456',
                'business_email' => 'studio@example.cz',
            ],
            'onboarding.step2' => [
                'category_id' => $category->id,
                'shop_name' => 'Studio Eva',
                'description' => 'Klidné studio v centru Prahy s individuálním přístupem ke každému klientovi.',
                'city' => 'Praha',
                'address' => 'Vinohradská 12',
                'currency' => 'CZK',
                'business_hours' => [
                    ['day_of_week' => 1, 'is_closed' => false, 'time_from' => '09:00', 'time_to' => '17:00'],
                    ['day_of_week' => 2, 'is_closed' => true, 'time_from' => null, 'time_to' => null],
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->withSession($session)
            ->post(route('vendor.onboarding.step3.store'), [
                'services' => [[
                    'name' => 'Relaxační masáž',
                    'description' => 'Šedesát minut klidné relaxační masáže.',
                    'price' => 1250,
                    'duration_minutes' => 60,
                ]],
            ]);

        $response->assertRedirect(route('vendor.dashboard'));
        $this->assertTrue($user->fresh()->is_vendor);
        $this->assertFalse($user->fresh()->provider_onboarding_pending);
        $this->assertDatabaseHas('shops', [
            'user_id' => $user->id,
            'name' => 'Studio Eva',
            'city' => 'Praha',
            'state' => 'Česko',
            'currency' => 'CZK',
            'timezone' => 'Europe/Prague',
            'contact_email' => 'studio@example.cz',
        ]);
        $this->assertDatabaseHas('services', [
            'name' => 'Relaxační masáž',
            'price' => '1250.00',
        ]);
        $this->assertDatabaseHas('business_hours', [
            'day_of_week' => 1,
            'time_from' => '09:00',
            'time_to' => '17:00',
        ]);

        $this->actingAs($user->fresh())
            ->withSession($session)
            ->post(route('vendor.onboarding.step3.store'), [
                'services' => [[
                    'name' => 'Relaxační masáž',
                    'description' => 'Šedesát minut klidné relaxační masáže.',
                    'price' => 1250,
                    'duration_minutes' => 60,
                ]],
            ])
            ->assertRedirect(route('vendor.dashboard'));
        $this->assertDatabaseCount('shops', 1);
    }
}
