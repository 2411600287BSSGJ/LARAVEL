@extends('layouts.app')
@section('content')
    <div class="row">
        @include('partials.sidebar')
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <h2>Create Workout Plan</h2>
                <form action="{{ route('plans.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Trainer</label>
                        <select name="trainer_id" class="form-select" required>
                            @foreach($trainers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @foreach(old('items', [['exercise_name' => '', 'quantity' => '', 'unit' => '']]) as $index => $item)
                    <div class="mb-3">
                        <label for="exercise-{{ $index }}">Exercise {{ $index + 1 }}</label>
                        <input id="exercise-{{ $index }}" type="text" name="items[{{ $index }}][exercise_name]" class="form-control" value="{{ $item['exercise_name'] }}" placeholder="e.g. Squats" required>
                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control mt-1" value="{{ $item['quantity'] }}" placeholder="3" min="1" required>
                        <input type="text" name="items[{{ $index }}][unit]" class="form-control mt-1" value="{{ $item['unit'] }}" placeholder="sets" required>
                    </div>
                    @endforeach
                    <textarea name="notes" class="form-control mb-3" placeholder="Notes"></textarea>
                    <button type="submit" class="btn btn-primary">Save Plan</button>
                </form>
            </div>
        </main>
    </div>
@endsection