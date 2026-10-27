<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = ['workout_id', 'category', 'description'];

    public function workout() 
    { 
        return $this->belongsTo(Workout::class); 
    }
}