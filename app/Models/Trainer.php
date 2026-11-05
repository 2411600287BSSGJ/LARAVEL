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

    // Relationships
    public function workoutPlans()
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function workouts()
    {
        return $this->hasMany(Workout::class);
    }

    // Query Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithLeadTime($query, $days)
    {
        return $query->where('lead_time_days', '<=', $days);
    }

    // Helper Methods
    public function getFullContactAttribute()
    {
        return "{$this->contact_person} ({$this->phone}) - {$this->email}";
    }

    public function getPerformanceRatingAttribute()
    {
        $total = $this->workoutPlans()->count();
        if ($total === 0) return 0;
        
        $approved = $this->workoutPlans()->where('status', 'approved')->count();
        return round(($approved / $total) * 100);
    }
}