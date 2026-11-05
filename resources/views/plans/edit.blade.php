@extends('layouts.app')
@section('content')
    <div class="row">
        @include('partials.sidebar')
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <h2>Edit Workout Plan: {{ $plan->reference_number }}</h2>
                <form action="{{ route('plans.update', $plan) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="trainer_id">Trainer</label>
                        <select id="trainer_id" name="trainer_id" class="form-select" required>
                            @foreach($trainers as $trainer)
                                <option value="{{ $trainer->id }}" @selected(old('trainer_id', $plan->trainer_id) == $trainer->id)>{{ $trainer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @foreach(old('items', $plan->items->map(fn ($item) => $item->only(['exercise_name', 'quantity', 'unit']))->all()) as $index => $item)
                        <div class="mb-3">
                            <label for="exercise-{{ $index }}">Exercise {{ $index + 1 }}</label>
                            <input id="exercise-{{ $index }}" type="text" name="items[{{ $index }}][exercise_name]" class="form-control" value="{{ $item['exercise_name'] }}" required>
                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control mt-1" value="{{ $item['quantity'] }}" min="1" required>
                            <input type="text" name="items[{{ $index }}][unit]" class="form-control mt-1" value="{{ $item['unit'] }}" required>
                        </div>
                    @endforeach
                    <textarea name="notes" class="form-control mb-3" placeholder="Notes">{{ old('notes', $plan->notes) }}</textarea>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </main>
    </div>
@endsection
