<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\GoalAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationReadRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_mark_their_notification_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new GoalAlertNotification('Workout goal reached', null));
        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->get(route('notifications.read', $notification->id))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $owner->notify(new GoalAlertNotification('Workout goal reached', null));
        $notification = $owner->notifications()->firstOrFail();

        $this->actingAs($otherUser)
            ->get(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }
}
