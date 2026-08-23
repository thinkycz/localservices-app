<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_read_or_delete_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $notification = Notification::create([
            'user_id' => $owner->id,
            'type' => 'booking',
            'title' => 'Nová rezervace',
            'message' => 'Test',
        ]);

        $this->actingAs($attacker)
            ->postJson(route('notifications.read', $notification))
            ->assertForbidden();
        $this->actingAs($attacker)
            ->deleteJson(route('notifications.destroy', $notification))
            ->assertForbidden();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }
}
