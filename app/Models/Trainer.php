<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trainer extends Model
{
    use SoftDeletes; // Enables soft deletes

    protected $fillable = [
        'name', 'contact_person', 'email', 'phone', 'specialty', 'lead_time_days', 'is_active'
    ];

    public function workoutPlans()
    {
        return $this->hasMany(WorkoutPlan::class);
    }
}