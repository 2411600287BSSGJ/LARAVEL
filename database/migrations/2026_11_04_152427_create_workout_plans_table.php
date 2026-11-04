<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('workout_plans', function (Blueprint $table) {
        $table->id();
        $table->string('reference_number')->unique(); 
        $table->foreignId('trainer_id')->constrained('trainers');
        $table->string('status')->default('draft'); // draft, pending, approved
        $table->text('notes')->nullable();
        $table->foreignId('created_by')->constrained('users');
        $table->foreignId('approved_by')->nullable()->constrained('users');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_plans');
    }
};
