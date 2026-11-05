<?php
namespace App\Services;
use App\Models\Workout;
use App\Models\WorkoutPlan;
use App\Models\Trainer;
use App\Models\User;

class ReorderService
{
    public function checkGoals(): string
    {
        $completedWorkouts = Workout::where('status', 'completed')->count();
        $goalThreshold = 5; // Reorder point

        if ($completedWorkouts >= $goalThreshold) {
            return 'Goals are met. No new plans needed.';
        }

        $trainer = Trainer::query()->where('is_active', true)->oldest('id')->first();
        if ($trainer === null) {
            return 'No active trainer is available; no workout plan was created.';
        }

        $creatorId = User::query()->oldest('id')->value('id');
        if ($creatorId === null) {
            return 'No user is available to own the workout plan.';
        }

        $notes = 'Automated goal replenishment: You need more workouts!';
        if (WorkoutPlan::query()->where('status', 'pending')->where('notes', $notes)->exists()) {
            return 'A pending automated workout plan already exists.';
        }

        WorkoutPlan::create([
            'reference_number' => WorkoutPlan::generateReferenceNumber(),
            'trainer_id' => $trainer->id,
            'status' => 'pending',
            'notes' => $notes,
            'created_by' => $creatorId,
        ]);

        return "Auto-generated a new workout plan from {$trainer->name}.";
    }
}