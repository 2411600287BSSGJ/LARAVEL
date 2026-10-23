<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    // 1. Show all workouts
    public function index() {
        $workouts = Workout::latest()->get();
        return view('workouts.index', compact('workouts'));
    }

    // 2. Show the create form
    public function create() { 
        return view('workouts.create'); 
    }
    
    // 3. Save a new workout (With Validation & Custom Errors)
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255', 
            'type' => 'required|string',
            'minutes' => 'required|integer|min:1', 
            'calories' => 'required|integer|min:1',
            'status' => 'required|in:completed,scheduled,missed', 
            'date_string' => 'required|string'
        ], [
            // Custom error messages
            'minutes.min' => 'A workout must last at least 1 minute.',
            'calories.min' => 'Calories must be greater than 0.'
        ]);

        Workout::create($validated);
        return redirect()->route('workouts.index')->with('success', 'Workout added successfully!');
    }
    
    // 4. Show a single workout (Doc requirement)
    public function show(Workout $workout) {
        return view('workouts.show', compact('workout'));
    }

    // 5. Show the edit form
    public function edit(Workout $workout) { 
        return view('workouts.edit', compact('workout')); 
    }
    
    // 6. Update an existing workout
    public function update(Request $request, Workout $workout) {
        $validated = $request->validate([
            'name' => 'required|string|max:255', 
            'type' => 'required|string',
            'minutes' => 'required|integer|min:1', 
            'calories' => 'required|integer|min:1',
            'status' => 'required|in:completed,scheduled,missed', 
            'date_string' => 'required|string'
        ]);

        $workout->update($validated);
        return redirect()->route('workouts.index')->with('success', 'Workout updated successfully!');
    }
    
    // 7. Delete a workout
    public function destroy(Workout $workout) {
        $workout->delete();
        return redirect()->route('workouts.index')->with('success', 'Workout deleted successfully!');
    }
}