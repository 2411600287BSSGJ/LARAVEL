<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkoutController;
use Illuminate\Support\Facades\Route;
use App\Models\Workout; // Make sure this is at the top

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('workouts', WorkoutController::class);
    
    // SCHEDULE: Show upcoming workouts
    Route::get('/schedule', function () {
        $scheduledWorkouts = Workout::where('status', 'scheduled')->orderBy('id')->get();
        return view('placeholder', ['title' => 'Schedule', 'type' => 'schedule', 'data' => $scheduledWorkouts]);
    })->name('schedule');

    // PROGRESS: Show completed workouts
    Route::get('/progress', function () {
        $completedWorkouts = Workout::where('status', 'completed')->orderByDesc('calories')->get();
        return view('placeholder', ['title' => 'Progress', 'type' => 'progress', 'data' => $completedWorkouts]);
    })->name('progress');

    // SETTINGS: Just a view
    Route::view('/settings', 'placeholder', ['title' => 'Settings', 'type' => 'settings'])->name('settings');
});

require __DIR__.'/auth.php';