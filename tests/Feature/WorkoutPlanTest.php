<?php

namespace Tests\Feature;

use App\Models\Trainer;
use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WorkoutPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_workout_plan_with_items(): void
    {
        $user = User::factory()->create();
        $trainer = Trainer::create([
            'name' => 'Coach One',
            'email' => 'coach@example.com',
            'specialty' => 'Strength',
        ]);

        $response = $this->actingAs($user)->post(route('plans.store'), [
            'trainer_id' => $trainer->id,
            'notes' => 'Build strength',
            'items' => [
                ['exercise_name' => 'Squats', 'quantity' => 3, 'unit' => 'sets'],
            ],
        ]);

        $response->assertRedirect(route('plans.index'));
        $this->assertDatabaseHas('workout_plans', [
            'trainer_id' => $trainer->id,
            'created_by' => $user->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('workout_plan_items', [
            'exercise_name' => 'Squats',
            'quantity' => 3,
            'unit' => 'sets',
        ]);
    }

    public function test_plan_routes_render_their_views(): void
    {
        $user = User::factory()->create();
        $trainer = Trainer::create([
            'name' => 'Coach One',
            'email' => 'coach@example.com',
            'specialty' => 'Strength',
        ]);
        $plan = WorkoutPlan::create([
            'reference_number' => WorkoutPlan::generateReferenceNumber(),
            'trainer_id' => $trainer->id,
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('plans.index'))
            ->assertOk();
        $this->get(route('plans.create'))->assertOk();
        $this->get(route('plans.show', $plan))->assertOk();
        $this->get(route('plans.edit', $plan))->assertOk();
    }

    public function test_plan_can_be_updated_and_deleted(): void
    {
        $user = User::factory()->create();
        $trainer = Trainer::create([
            'name' => 'Coach One',
            'email' => 'coach@example.com',
            'specialty' => 'Strength',
        ]);
        $plan = WorkoutPlan::create([
            'reference_number' => WorkoutPlan::generateReferenceNumber(),
            'trainer_id' => $trainer->id,
            'status' => 'pending',
            'created_by' => $user->id,
        ]);
        $plan->items()->create([
            'exercise_name' => 'Squats',
            'quantity' => 3,
            'unit' => 'sets',
        ]);

        $this->actingAs($user)
            ->put(route('plans.update', $plan), [
                'trainer_id' => $trainer->id,
                'notes' => 'Updated notes',
                'items' => [
                    ['exercise_name' => 'Lunges', 'quantity' => 4, 'unit' => 'sets'],
                ],
            ])
            ->assertRedirect(route('plans.show', $plan));

        $this->assertDatabaseHas('workout_plans', [
            'id' => $plan->id,
            'notes' => 'Updated notes',
        ]);
        $this->assertDatabaseHas('workout_plan_items', [
            'workout_plan_id' => $plan->id,
            'exercise_name' => 'Lunges',
        ]);
        $this->assertDatabaseMissing('workout_plan_items', [
            'workout_plan_id' => $plan->id,
            'exercise_name' => 'Squats',
        ]);

        $this->delete(route('plans.destroy', $plan))
            ->assertRedirect(route('plans.index'));
        $this->assertDatabaseMissing('workout_plans', ['id' => $plan->id]);
    }

    public function test_plan_approval_records_the_approver_and_notifies_the_creator(): void
    {
        Mail::fake();
        $approver = User::factory()->create();
        $creator = User::factory()->create(['email' => 'creator@example.com']);
        $trainer = Trainer::create([
            'name' => 'Coach One',
            'email' => 'coach@example.com',
            'specialty' => 'Strength',
        ]);
        $plan = WorkoutPlan::create([
            'reference_number' => WorkoutPlan::generateReferenceNumber(),
            'trainer_id' => $trainer->id,
            'status' => 'pending',
            'created_by' => $creator->id,
        ]);

        $this->actingAs($approver)
            ->post(route('plans.approve', $plan))
            ->assertRedirect();

        $this->assertDatabaseHas('workout_plans', [
            'id' => $plan->id,
            'status' => 'approved',
            'approved_by' => $approver->id,
        ]);
        Mail::assertSent(\App\Mail\PlanApprovedMail::class, function ($mail) use ($creator): bool {
            return $mail->hasTo($creator->email);
        });
    }
}
