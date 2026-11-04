<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutPlan extends Model
{
    protected $fillable = [
        'reference_number',
        'trainer_id',
        'status',
        'notes',
        'created_by',
        'approved_by',
    ];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(WorkoutPlanItem::class);
    }
}
