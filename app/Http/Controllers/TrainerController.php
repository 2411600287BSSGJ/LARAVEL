<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TrainerController extends Controller
{
    public function index(): View
    {
        $trainers = Trainer::latest()->paginate(10);

        return view('trainer.index', compact('trainers'));
    }

    public function create(): View
    {
        return view('trainer.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:trainers,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'specialty' => ['required', 'string', 'max:255'],
            'lead_time_days' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        Trainer::create($validated);

        return redirect()->route('trainers.index')->with('success', 'Trainer added.');
    }

    public function show(Trainer $trainer): View
    {
        $trainer->load('workoutPlans');

        return view('trainer.show', compact('trainer'));
    }

    public function edit(Trainer $trainer): View
    {
        return view('trainer.edit', compact('trainer'));
    }

    public function update(Request $request, Trainer $trainer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('trainers', 'email')->ignore($trainer)],
            'phone' => ['nullable', 'string', 'max:50'],
            'specialty' => ['required', 'string', 'max:255'],
            'lead_time_days' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $trainer->update($validated);

        return redirect()->route('trainers.index')->with('success', 'Trainer updated.');
    }

    public function destroy(Trainer $trainer): RedirectResponse
    {
        $trainer->delete();

        return redirect()->route('trainers.index')->with('success', 'Trainer removed.');
    }
}
