@extends('layouts.app')

@section('content')
    <div class="row">
        @include('partials.sidebar')

        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <div class="panel-heading mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="eyebrow mb-1">Workout Details</p>
                        <h2 class="mb-0">{{ $workout->name }}</h2>
                    </div>
                    <a href="{{ route('workouts.index') }}" class="btn btn-outline-secondary btn-sm">Back to Workouts</a>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="metric-card">
                            <span class="metric-label">Type</span>
                            <strong>{{ $workout->type }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card">
                            <span class="metric-label">Duration</span>
                            <strong>{{ $workout->minutes }} min</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card">
                            <span class="metric-label">Calories</span>
                            <strong>{{ $workout->calories }} kcal</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-card">
                            <span class="metric-label">Status</span>
                            <strong><span
                                    class="status-badge status-{{ $workout->status }}"><span></span>{{ ucfirst($workout->status) }}</span></strong>
                        </div>
                    </div>
                </div>

                <!-- This shows the "Related Transactions" required by the document -->
                <div class="panel mt-4">
                    <div class="panel-heading mb-3">
                        <h2>Activity History</h2>
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse($workout->activities as $activity)
                            <li class="list-group-item bg-transparent px-0">
                                <i class="bi bi-clock-history me-2 text-muted"></i>
                                {{ $activity->description }}
                                <small class="text-muted float-end">{{ $activity->created_at->diffForHumans() }}</small>
                            </li>
                        @empty
                            <li class="list-group-item bg-transparent px-0 text-muted">No activities logged for this workout
                                yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </main>
    </div>
@endsection