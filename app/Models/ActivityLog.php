<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['workout_id', 'user_id', 'type', 'minutes_changed', 'calories_changed', 'notes'];

    public function workout() 
    { 
        return $this->belongsTo(Workout::class); 
    }
    
    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }
}