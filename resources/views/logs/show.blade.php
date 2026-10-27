@extends('layouts.app')

@section('content')
    <div class="row">
        @include('partials.sidebar')
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <h2>Activity Details</h2>
                <hr>
                <p><strong>Workout:</strong> {{ $log->workout->name ?? 'Deleted' }}</p>
                <p><strong>Action:</strong> {{ ucfirst($log->type) }}</p>
                <p><strong>Minutes Changed:</strong> {{ $log->minutes_changed }}</p>
                <p><strong>Calories Changed:</strong> {{ $log->calories_changed }}</p>
                <p><strong>Notes:</strong> {{ $log->notes }}</p>
                <p><strong>Recorded By:</strong> {{ $log->user->name ?? 'System' }}</p>
                <p><strong>Date:</strong> {{ $log->created_at->format('M d, Y h:i A') }}</p>

                <a href="{{ route('logs.index') }}" class="btn btn-outline-secondary mt-3">Back to Logs</a>
            </div>
        </main>
    </div>
@endsection