@extends('layouts.app')
@section('content')
    <div class="row">
        @include('partials.sidebar')
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <div class="d-flex justify-content-between mb-3">
                    <h2>Workout Plans</h2>
                    <a href="{{ route('plans.create') }}" class="btn btn-primary">Create Plan</a>
                </div>
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <table class="table">
                    <thead>
                        <tr><th>Reference</th><th>Trainer</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan)
                            <tr>
                                <td>{{ $plan->reference_number }}</td>
                                <td>{{ $plan->trainer->name }}</td>
                                <td>{{ ucfirst($plan->status) }}</td>
                                <td>
                                    <a href="{{ route('plans.show', $plan) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    <a href="{{ route('plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('plans.destroy', $plan) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No workout plans found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $plans->links() }}
            </div>
        </main>
    </div>
@endsection
