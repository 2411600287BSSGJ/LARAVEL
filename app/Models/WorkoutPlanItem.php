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

    // Since we don't have prices in a fitness tracker, we can just return the quantity
    public function getLineTotalAttribute()
    {
        return $this->quantity;
    }

    public function getOutstandingQuantityAttribute()
    {
        // Return 0 because we don't track partial completions in this lab
        return 0; 
    }
}