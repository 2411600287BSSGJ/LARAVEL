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

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'lead_time_days' => 'integer',
        ];
    }

    public function workoutPlans()
    {
        return $this->hasMany(WorkoutPlan::class);
    }
}