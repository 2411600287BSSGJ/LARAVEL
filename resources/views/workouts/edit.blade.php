@extends('layouts.app')

@section('content')
    <div class="row">
        <!-- SIDEBAR -->
        @include('partials.sidebar')

        <!-- MAIN CONTENT -->
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <div class="panel-heading mb-4">
                    <div>
                        <p class="eyebrow mb-1">Edit entry</p>
                        <h2>Edit Workout</h2>
                    </div>
                    <a href="{{ route('workouts.index') }}" class="btn btn-outline-secondary btn-sm">Back to List</a>
                </div>

                <!-- Display Laravel Validation Errors -->
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('workouts.update', $workout) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $workout->name }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type</label>
                            <input type="text" name="type" class="form-control" value="{{ $workout->type }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Minutes</label>
                            <input type="number" name="minutes" class="form-control" value="{{ $workout->minutes }}"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Calories</label>
                            <input type="number" name="calories" class="form-control" value="{{ $workout->calories }}"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="completed" {{ $workout->status == 'completed' ? 'selected' : '' }}>Completed
                                </option>
                                <option value="scheduled" {{ $workout->status == 'scheduled' ? 'selected' : '' }}>Scheduled
                                </option>
                                <option value="missed" {{ $workout->status == 'missed' ? 'selected' : '' }}>Missed</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date String</label>
                            <input type="text" name="date_string" class="form-control" value="{{ $workout->date_string }}"
                                required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4 px-4">Update Workout</button>
                </form>
            </div>
        </main>
    </div>
@endsection