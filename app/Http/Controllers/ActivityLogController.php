<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    // PART 8: ADJUSTMENT FEATURE
    public function adjust()
    {
        $workouts = Workout::all();
        return view('logs.adjust', compact('workouts'));
    }

    public function processAdjust(Request $request)
    {
        $request->validate([
            'workout_id' => 'required|exists:workouts,id',
            'new_minutes' => 'required|integer|min:0',
            'reason' => 'required|string'
        ]);

        $workout = Workout::find($request->workout_id);
        $difference = $request->new_minutes - $workout->minutes;

        // Update the workout (Observer will automatically log the diff)
        $workout->update(['minutes' => $request->new_minutes]);

        // Add the reason to the most recent log entry
        $latestLog = ActivityLog::where('workout_id', $workout->id)->latest()->first();
        if ($latestLog) {
            $latestLog->update(['notes' => "Adjustment: " . $request->reason]);
        }

        return redirect()->route('logs.index')->with('success', 'Workout adjusted successfully!');
    }
}