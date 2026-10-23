<?php
namespace Database\Seeders;
use App\Models\Workout;
use Illuminate\Database\Seeder;

class WorkoutSeeder extends Seeder
{
    public function run(): void
    {
        $workouts = [
            ['name' => 'Morning Run', 'type' => 'Running', 'minutes' => 45, 'calories' => 865, 'status' => 'completed', 'date_string' => 'Today, 6:30 AM'],
            ['name' => 'Upper Body Workout', 'type' => 'Strength', 'minutes' => 60, 'calories' => 450, 'status' => 'completed', 'date_string' => 'Today, 12:15 PM'],
            ['name' => 'Yoga Session', 'type' => 'Yoga', 'minutes' => 30, 'calories' => 180, 'status' => 'scheduled', 'date_string' => 'Today, 6:00 PM'],
            ['name' => 'Cycling', 'type' => 'Cycling', 'minutes' => 50, 'calories' => 380, 'status' => 'scheduled', 'date_string' => 'Tomorrow, 7:00 AM'],
            ['name' => 'Swimming', 'type' => 'Swimming', 'minutes' => 40, 'calories' => 290, 'status' => 'missed', 'date_string' => 'Yesterday'],
            ['name' => 'Core Circuit', 'type' => 'Strength', 'minutes' => 25, 'calories' => 210, 'status' => 'completed', 'date_string' => 'Yesterday, 5:30 PM'],
            ['name' => 'Evening Walk', 'type' => 'Walking', 'minutes' => 35, 'calories' => 160, 'status' => 'completed', 'date_string' => 'Mon, 7:00 PM'],
            ['name' => 'Hill Intervals', 'type' => 'Running', 'minutes' => 38, 'calories' => 340, 'status' => 'completed', 'date_string' => 'Sun, 7:45 AM'],
            ['name' => 'Spin Class', 'type' => 'Cycling', 'minutes' => 42, 'calories' => 360, 'status' => 'completed', 'date_string' => 'Sat, 8:00 AM'],
            ['name' => 'Pilates Flow', 'type' => 'Yoga', 'minutes' => 28, 'calories' => 170, 'status' => 'completed', 'date_string' => 'Fri, 6:15 PM'],
            ['name' => 'Beach Walk', 'type' => 'Walking', 'minutes' => 55, 'calories' => 260, 'status' => 'missed', 'date_string' => 'Thu, 9:00 AM'],
            ['name' => 'HIIT Blast', 'type' => 'Strength', 'minutes' => 32, 'calories' => 280, 'status' => 'scheduled', 'date_string' => 'Wed, 5:45 PM'],
            ['name' => 'Lap Swim', 'type' => 'Swimming', 'minutes' => 48, 'calories' => 330, 'status' => 'completed', 'date_string' => 'Tue, 7:10 AM'],
            ['name' => 'Recovery Stretch', 'type' => 'Yoga', 'minutes' => 20, 'calories' => 110, 'status' => 'completed', 'date_string' => 'Mon, 8:00 PM'],
            ['name' => 'Stair Climber', 'type' => 'Cardio', 'minutes' => 20, 'calories' => 200, 'status' => 'completed', 'date_string' => 'Yesterday'],
        ];
        foreach($workouts as $w) { Workout::create($w); }
    }
}