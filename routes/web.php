<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;
use App\Models\Workout;

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
    
    // LAB 6: ACTIVITY LOGS
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
    Route::get('/logs/create', [ActivityLogController::class, 'create'])->name('logs.create');
    Route::post('/logs', [ActivityLogController::class, 'store'])->name('logs.store');
    Route::get('/logs/{log}', [ActivityLogController::class, 'show'])->name('logs.show');

    // LAB 6: NOTIFICATIONS
    Route::get('/notifications/{id}/read', function ($id) {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        $workoutId = $notification->data['workout_id'] ?? null;
        if ($workoutId) {
            return redirect()->route('workouts.edit', $workoutId);
        }
        return redirect()->route('dashboard');
    })->name('notifications.read');

    // LAB 6: API for AJAX Polling
    Route::get('/api/dashboard-stats', function() {
        return response()->json([
            'total_workouts' => Workout::count(),
            'total_calories' => Workout::where('status', 'completed')->sum('calories'),
            'focus_count' => Workout::whereIn('status', ['scheduled', 'missed'])->count(),
        ]);
    }); 
});

require __DIR__.'/auth.php';