<?php

namespace App\Observers;

use App\Models\Workout;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\GoalAlertNotification;
use Illuminate\Support\Facades\Auth;

class WorkoutObserver
{
    public function updated(Workout $workout)
    {
        // 1. Log the transaction
        if ($workout->isDirty('minutes') || $workout->isDirty('calories')) {
            $minutesDiff = $workout->minutes - $workout->getOriginal('minutes');
            $caloriesDiff = $workout->calories - $workout->getOriginal('calories');

            ActivityLog::create([
                'workout_id' => $workout->id,
                'user_id' => Auth::id() ?? 1,
                'type' => 'update',
                'minutes_changed' => $minutesDiff,
                'calories_changed' => $caloriesDiff,
                'notes' => "Workout details updated."
            ]);
        }

        // 2. Send Goal Alert
        if ($workout->isDirty('status') && $workout->status === 'missed') {
            $user = User::find(Auth::id() ?? 1);
            
            if ($user) {
                // Pass the workout ID as the second argument
                $user->notify(new GoalAlertNotification(
                    "You missed your workout: {$workout->name}. Let's get back on track!", 
                    $workout->id
                ));
            }
        }
    }
}