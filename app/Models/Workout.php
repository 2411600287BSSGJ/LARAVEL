<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    // Fillable attributes
    protected $fillable = [
        'name', 
        'type', 
        'minutes', 
        'calories', 
        'status', 
        'date_string'
    ];

    // Relationship to the Activity log
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    // Custom method for business logic (Lab 5 Requirement)
    // Instead of "low stock", we check if a workout needs the user's attention
    public function needsAttention()
    {
        return in_array($this->status, ['scheduled', 'missed']);
    }
}