<?php

namespace Tests\Feature;

use App\Models\Trainer;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\ReorderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReorderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_does_not_create_a_plan_without_an_active_trainer_or_user(): void
    {
        User::factory()->create();
        $service = new ReorderService();

        $this->assertSame(
            'No active trainer is available; no workout plan was created.',
            $service->checkGoals()
        );
        $this->assertDatabaseCount('workout_plans', 0);

        Trainer::create([
            'name' => 'Coach One',
            'email' => 'coach@example.com',
            'specialty' => 'Strength',
        ]);
        User::query()->delete();

        $this->assertSame(
            'No user is available to own the workout plan.',
            $service->checkGoals()
        );
        $this->assertDatabaseCount('workout_plans', 0);
    }

    public function test_it_creates_only_one_pending_automated_plan(): void
    {
        User::factory()->create();
        Trainer::create([
            'name' => 'Coach One',
            'email' => 'coach@example.com',
            'specialty' => 'Strength',
        ]);
        $service = new ReorderService();

        $this->assertStringContainsString('Auto-generated', $service->checkGoals());
        $this->assertSame('A pending automated workout plan already exists.', $service->checkGoals());
        $this->assertDatabaseCount('workout_plans', 1);
    }
}
