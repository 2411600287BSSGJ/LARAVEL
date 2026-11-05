<?php
namespace App\Policies;
use App\Models\User;
use App\Models\WorkoutPlan;

class WorkoutPlanPolicy
{
    public function approve(User $user, WorkoutPlan $plan)
    {
        // Only Admin (ID 1) can approve pending plans
        return $user->id === 1 && $plan->status === 'pending';
    }
}