<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\Activity;
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
        
        // 3. Dashboard Metrics (based on all data, not just filtered)
        $totalWorkouts = Workout::count();
        $totalMinutes = Workout::where('status', 'completed')->sum('minutes');
        $totalCalories = Workout::where('status', 'completed')->sum('calories');
        $focusCount = Workout::whereIn('status', ['scheduled', 'missed'])->count();
        $focusWorkouts = Workout::whereIn('status', ['scheduled', 'missed'])->get();

        // 4. Chart Data
        // Chart 1: Minutes by Type
        $minutesByType = Workout::selectRaw('type, SUM(minutes) as total')
            ->groupBy('type')->pluck('total', 'type');

        // Chart 2: Status Distribution
        $statusDist = [
            'completed' => Workout::where('status', 'completed')->count(),
            'scheduled' => Workout::where('status', 'scheduled')->count(),
            'missed' => Workout::where('status', 'missed')->count(),
        ];

        // Chart 3: Top Workouts by Calories
        $topWorkouts = Workout::where('status', 'completed')->orderByDesc('calories')->take(5)->get();
        $topWorkoutNames = $topWorkouts->pluck('name');
        $topWorkoutCalories = $topWorkouts->pluck('calories');

        // Chart 4: Activity by Category
        $activityStats = [
            'Completed' => Activity::where('category', 'Completed')->count(),
            'Scheduled' => Activity::where('category', 'Scheduled')->count(),
            'Missed' => Activity::where('category', 'Missed')->count(),
        ];
        
        // Recent Activity Log
        $recentActivities = Activity::latest()->take(10)->get();

        // Workout Types for the dropdown
        $workoutTypes = Workout::select('type')->distinct()->pluck('type');

        return view('dashboard', compact(
            'totalWorkouts', 'totalMinutes', 'totalCalories', 'focusCount', 'focusWorkouts',
            'minutesByType', 'statusDist', 'topWorkoutNames', 'topWorkoutCalories',
            'activityStats', 'recentActivities', 'recentWorkouts', 'workoutTypes'
        ));
    }
}