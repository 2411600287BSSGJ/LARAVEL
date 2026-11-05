<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutPlan;

class WorkoutPlanPolicy
{
    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, WorkoutPlan $plan): bool
    {
        // For this lab, we are assuming Admin (User ID 1) is the only one who can approve.
        return $user->id === 1 && $plan->status === 'pending';
    }
}