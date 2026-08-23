<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_vendor' => false,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_provider_registration_requires_verification_before_onboarding(): void
    {
        $response = $this->post('/register', [
            'name' => 'Provider User',
            'email' => 'provider@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_vendor' => true,
        ]);

        $user = User::where('email', 'provider@example.com')->firstOrFail();

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertFalse($user->is_vendor);
        $this->assertTrue($user->provider_onboarding_pending);
        $this->get(route('dashboard'))->assertRedirect(route('verification.notice'));

        $user->markEmailAsVerified();

        $this->actingAs($user->fresh())
            ->get(route('dashboard'))
            ->assertRedirect(route('vendor.onboarding.index'));
    }
}
