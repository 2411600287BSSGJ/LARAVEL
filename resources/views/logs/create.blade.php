@extends('layouts.app')

@section('content')
    <div class="row">
        @include('partials.sidebar')
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <h2>Log Activity Manually</h2>
                <form action="{{ route('logs.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Workout</label>
                        <select name="workout_id" class="form-select" required>
                            @foreach($workouts as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="update">Manual Update</option>
                            <option value="completed">Marked Completed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Minutes Changed</label>
                        <input type="number" name="minutes_changed" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Reason for change">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Log</button>
                </form>
            </div>
        </main>
    </div>
@endsection