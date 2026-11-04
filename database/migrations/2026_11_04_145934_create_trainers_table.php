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
    Schema::create('trainers', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Company/Trainer Name
        $table->string('contact_person')->nullable();
        $table->string('email')->unique();
        $table->string('phone')->nullable();
        $table->string('specialty')->nullable(); // Cardio, Strength
        $table->integer('lead_time_days')->default(3); 
        $table->boolean('is_active')->default(true);
        $table->softDeletes(); // LAB 7 REQUIREMENT
        $table->timestamps();
    });
}
};
