<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    protected $fillable = [
        'name', 'type', 'minutes', 'calories', 'status', 'date_string'
    ];

    // Relationship for Lab 6 Logs
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Relationship for Lab 5 Dashboard Activity Charts
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function needsAttention()
    {
        return in_array($this->status, ['scheduled', 'missed']);
    }
}