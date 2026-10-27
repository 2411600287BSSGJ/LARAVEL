@extends('layouts.app')

@section('content')
    <div class="row">
        @include('partials.sidebar')

        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <div class="panel-heading mb-4">
                    <div>
                        <p class="eyebrow mb-1">Adjustment Feature</p>
                        <h2>Adjust Workout Minutes</h2>
                    </div>
                    <a href="{{ route('logs.index') }}" class="btn btn-outline-secondary btn-sm">Back to Logs</a>
                </div>

                <form action="{{ route('logs.adjust.process') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Workout</label>
                            <select name="workout_id" class="form-select" required>
                                @foreach($workouts as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }} (Current: {{ $w->minutes }} mins)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">New Minutes (Physical Count)</label>
                            <input type="number" name="new_minutes" class="form-control" required min="0">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Reason for Adjustment</label>
                            <select name="reason" class="form-select" required>
                                <option value="Forgot to log">Forgot to log time</option>
                                <option value="System error">System error</option>
                                <option value="Corrected manual entry">Corrected manual entry</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Save Adjustment</button>
                </form>
            </div>
        </main>
    </div>
@endsection