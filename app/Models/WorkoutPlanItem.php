<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutPlanItem extends Model
{
    protected $fillable = ['workout_plan_id', 'exercise_name', 'quantity', 'unit'];

    public function workoutPlan()
    {
        return $this->belongsTo(WorkoutPlan::class, 'workout_plan_id');
    }
}
