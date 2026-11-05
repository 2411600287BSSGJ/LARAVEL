<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\Activity;
use App\Models\WorkoutPlan; // lab 7
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Base Query with Filters
        $query = Workout::query();

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('min_calories')) {
            $query->where('calories', '>=', $request->min_calories);
        }

        // 2. Get Filtered Results
        $recentWorkouts = (clone $query)->latest()->take(5)->get();
        
        // 3. Dashboard Metrics
        $totalWorkouts = Workout::count();
        $totalMinutes = Workout::where('status', 'completed')->sum('minutes');
        $totalCalories = Workout::where('status', 'completed')->sum('calories');
        $focusCount = Workout::whereIn('status', ['scheduled', 'missed'])->count();
        $focusWorkouts = Workout::whereIn('status', ['scheduled', 'missed'])->get();

        // 4. Chart Data
        $minutesByType = Workout::selectRaw('type, SUM(minutes) as total')
            ->groupBy('type')->pluck('total', 'type');

        $statusDist = [
            'completed' => Workout::where('status', 'completed')->count(),
            'scheduled' => Workout::where('status', 'scheduled')->count(),
            'missed' => Workout::where('status', 'missed')->count(),
        ];

        $topWorkouts = Workout::where('status', 'completed')->orderByDesc('calories')->take(5)->get();
        $topWorkoutNames = $topWorkouts->pluck('name');
        $topWorkoutCalories = $topWorkouts->pluck('calories');

        $activityStats = [
            'Completed' => Activity::where('category', 'Completed')->count(),
            'Scheduled' => Activity::where('category', 'Scheduled')->count(),
            'Missed' => Activity::where('category', 'Missed')->count(),
        ];
        
        $recentActivities = Activity::latest()->take(10)->get();
        $workoutTypes = Workout::select('type')->distinct()->pluck('type');

        // LAB 7: Procurement Widgets
        $pendingPlansCount = WorkoutPlan::where('status', 'pending')->count();
        $recentPlans = WorkoutPlan::with('trainer')->latest()->take(3)->get();

        return view('dashboard', compact(
            'totalWorkouts', 'totalMinutes', 'totalCalories', 'focusCount', 'focusWorkouts',
            'minutesByType', 'statusDist', 'topWorkoutNames', 'topWorkoutCalories',
            'activityStats', 'recentActivities', 'recentWorkouts', 'workoutTypes',
            'pendingPlansCount', 'recentPlans' // <-- ADDED
        ));
    }
}