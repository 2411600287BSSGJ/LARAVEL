@extends('layouts.app')

@section('content')
    <div class="row">
        @include('partials.sidebar')

        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <div class="panel-heading mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="eyebrow mb-1">Trainer Profile</p>
                        <h2>{{ $trainer->name }}</h2>
                    </div>
                    <a href="{{ route('trainers.index') }}" class="btn btn-outline-secondary btn-sm">Back to Trainers</a>
                </div>

                <!-- Trainer Info -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="metric-card">
                            <span class="metric-label">Email</span>
                            <strong>{{ $trainer->email }}</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="metric-card">
                            <span class="metric-label">Specialty</span>
                            <strong>{{ $trainer->specialty }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Contact:</strong> {{ $trainer->contact_person ?: 'Not provided' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Phone:</strong> {{ $trainer->phone ?: 'Not provided' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Lead Time:</strong> {{ $trainer->lead_time_days }} days</p>
                    </div>
                </div>

                <!-- Workout Plans Section -->
                <div class="panel mb-4">
                    <div class="panel-heading mb-3">
                        <h4>Workout Plans</h4>
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse($trainer->workoutPlans as $plan)
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                                <a href="{{ route('plans.show', $plan) }}" class="fw-bold text-decoration-none">
                                    {{ $plan->reference_number }}
                                </a>
                                <span class="badge {{ $plan->status === 'approved' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($plan->status) }}
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item bg-transparent px-0 text-muted">No workout plans generated yet.</li>
                        @endforelse
                    </ul>
                </div>

                <!-- Assigned Workouts Section (The Lab 7 Integration) -->
                <div class="panel">
                    <div class="panel-heading mb-3">
                        <h4>Assigned Workouts</h4>
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse($trainer->workouts as $workout)
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                                <span><strong>{{ $workout->name }}</strong> ({{ $workout->type }})</span>
                                <span class="status-badge status-{{ $workout->status }}">
                                    <span></span>{{ ucfirst($workout->status) }}
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item bg-transparent px-0 text-muted">No workouts assigned yet.</li>
                        @endforelse
                    </ul>
                </div>

            </div>
        </main>
    </div>
@endsection