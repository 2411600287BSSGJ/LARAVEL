<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WorkoutPlan extends Model
{
    protected $fillable = ['reference_number', 'trainer_id', 'status', 'notes', 'created_by', 'approved_by'];

    // Relationships
    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function items()
    {
        return $this->hasMany(WorkoutPlanItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Query Scopes (Required by Lab 7)
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['received', 'cancelled']);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helper Methods (Required by Lab 7)
    public function canBeApproved()
    {
        return $this->status === 'pending';
    }

    public function canBeReceived()
    {
        return in_array($this->status, ['approved', 'sent']);
    }

    public function calculateTotal()
    {
        // Since our plan items don't have prices, we can just return the count of exercises
        // Or if you want to pretend quantity is the cost, we can sum that up.
        return $this->items()->sum('quantity');
    }

    public static function generateReferenceNumber(): string
    {
        return 'PLAN-'.Str::upper(Str::random(12));
    }
}