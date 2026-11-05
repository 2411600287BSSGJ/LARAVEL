<?php

namespace App\Http\Controllers;

use App\Mail\PlanApprovedMail;
use App\Models\Trainer;
use App\Models\WorkoutPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class WorkoutPlanController extends Controller
{
    public function index(): View
    {
        $plans = WorkoutPlan::with('trainer')->latest()->paginate(10);

        return view('plans.index', compact('plans'));
    }

    public function create(): View
    {
        $trainers = Trainer::query()->orderBy('name')->get();

        return view('plans.create', compact('trainers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trainer_id' => ['required', 'exists:trainers,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.exercise_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['required', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $plan = WorkoutPlan::create([
                'reference_number' => WorkoutPlan::generateReferenceNumber(),
                'trainer_id' => $validated['trainer_id'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $plan->items()->createMany($validated['items']);
        });

        return redirect()->route('plans.index')->with('success', 'Workout plan created.');
    }

    public function show(WorkoutPlan $plan): View
    {
        $plan->load(['trainer', 'items', 'creator']);

        return view('plans.show', compact('plan'));
    }

    public function edit(WorkoutPlan $plan): View
    {
        $plan->load('items');
        $trainers = Trainer::query()->orderBy('name')->get();

        return view('plans.edit', compact('plan', 'trainers'));
    }

    public function update(Request $request, WorkoutPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'trainer_id' => ['required', 'exists:trainers,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.exercise_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['required', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($validated, $plan): void {
            $plan->update([
                'trainer_id' => $validated['trainer_id'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $plan->items()->delete();
            $plan->items()->createMany($validated['items']);
        });

        return redirect()->route('plans.show', $plan)->with('success', 'Workout plan updated.');
    }

    public function destroy(WorkoutPlan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('plans.index')->with('success', 'Workout plan deleted.');
    }

    public function approve(WorkoutPlan $plan): RedirectResponse
    {
        $this->authorize('approve', $plan);

        $plan->update([
            'status' => 'approved',
            'approved_by' => request()->user()->id,
        ]);

        if ($plan->creator !== null) {
            Mail::to($plan->creator->email)->send(new PlanApprovedMail($plan));
        }

        return redirect()->back()->with('success', 'Plan approved.');
    }
}
