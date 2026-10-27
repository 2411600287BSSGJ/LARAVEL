<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Workout;
use Illuminate\Http\Request; // <-- ADD THIS
use Illuminate\Support\Facades\Auth; // <-- ADD THIS

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with(['workout', 'user'])->latest()->paginate(20);
        return view('logs.index', compact('logs'));
    }

    public function create()
    {
        $workouts = Workout::all();
        return view('logs.create', compact('workouts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'workout_id' => 'required|exists:workouts,id',
            'type' => 'required|string',
            'minutes_changed' => 'required|integer',
            'notes' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = Auth::id() ?? 1;
        ActivityLog::create($validated);

        return redirect()->route('logs.index')->with('success', 'Manual log recorded!');
    }

    public function show(ActivityLog $log)
    {
        return view('logs.show', compact('log'));
    }
}