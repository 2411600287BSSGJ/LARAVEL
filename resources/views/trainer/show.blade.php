@extends('layouts.app')
@section('content')
    <div class="row">
        @include('partials.sidebar')
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <h2>{{ $trainer->name }}</h2>
                <p><strong>Email:</strong> {{ $trainer->email }}</p>
                <p><strong>Specialty:</strong> {{ $trainer->specialty }}</p>
                <p><strong>Contact:</strong> {{ $trainer->contact_person ?: 'Not provided' }}</p>
                <p><strong>Phone:</strong> {{ $trainer->phone ?: 'Not provided' }}</p>
                <p><strong>Lead time:</strong> {{ $trainer->lead_time_days }} days</p>
                <h3>Workout plans</h3>
                <ul>
                    @forelse($trainer->workoutPlans as $plan)
                        <li><a href="{{ route('plans.show', $plan) }}">{{ $plan->reference_number }}</a> ({{ ucfirst($plan->status) }})</li>
                    @empty
                        <li>No workout plans.</li>
                    @endforelse
                </ul>
                <a href="{{ route('trainers.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </main>
    </div>
@endsection
