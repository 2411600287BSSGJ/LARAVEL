<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_staff_and_admin_can_resend_email_verification_from_the_profile(): void
    {
        Notification::fake();

        foreach (['staff', 'admin'] as $role) {
            $user = User::factory()->unverified()->create(['role' => $role]);

            $this->actingAs($user)
                ->get('/profile')
                ->assertOk()
                ->assertSee('Send Verification Email');

            $this->actingAs($user)
                ->from('/profile')
                ->post(route('verification.send'))
                ->assertRedirect('/profile')
                ->assertSessionHas('status', 'verification-link-sent');

            Notification::assertSentTo($user, VerifyEmail::class);
        }
    }

    public function test_staff_can_verify_email_after_requesting_a_link_from_the_profile(): void
    {
        Notification::fake();
        $staff = User::factory()->unverified()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Not Verified')
            ->assertSee('Send Verification Email');

        $this->from('/profile')
            ->post(route('verification.send'))
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'verification-link-sent');

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $staff->id, 'hash' => sha1($staff->email)]
        );

        $this->get($verificationUrl)
            ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

        $this->assertTrue($staff->fresh()->hasVerifiedEmail());
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
