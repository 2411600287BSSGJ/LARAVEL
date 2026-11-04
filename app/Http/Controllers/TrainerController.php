<?php
namespace App\Http\Controllers;
use App\Models\Trainer;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    public function index() {
        $trainers = Trainer::latest()->paginate(10);
        return view('trainers.index', compact('trainers'));
    }
    public function create() { return view('trainers.create'); }
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:trainers,email',
            'specialty' => 'required|string',
        ]);
        Trainer::create($request->all());
        return redirect()->route('trainers.index')->with('success', 'Trainer added!');
    }
    public function show(Trainer $trainer) {
        $trainer->load('workoutPlans');
        return view('trainers.show', compact('trainer'));
    }
    public function edit(Trainer $trainer) { return view('trainers.edit', compact('trainer')); }
    public function update(Request $request, Trainer $trainer) {
        $trainer->update($request->all());
        return redirect()->route('trainers.index')->with('success', 'Trainer updated!');
    }
    public function destroy(Trainer $trainer) {
        $trainer->delete(); // Soft deletes
        return redirect()->route('trainers.index')->with('success', 'Trainer removed.');
    }
}