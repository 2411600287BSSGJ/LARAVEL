<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('workout_plan_items');
        Schema::dropIfExists('workout_plans');
        Schema::dropIfExists('workouts');
        Schema::dropIfExists('trainers');
        // keep notifications table for low stock alerts
    }

    public function down(): void
    {
        // Fitness tables were intentionally removed; their data cannot be restored.
    }
};
