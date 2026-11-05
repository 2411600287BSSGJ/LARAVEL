@extends('layouts.app')

@section('content')
    <div class="row">
        @include('partials.sidebar')

        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <div class="panel-heading mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="eyebrow mb-1">Procurement</p>
                        <h2>Workout Plans</h2>
                    </div>
                    <a href="{{ route('plans.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Create Plan
                    </a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table inventory-table mb-0">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Trainer</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $plan)
                                <tr>
                                    <td><strong>{{ $plan->reference_number }}</strong></td>
                                    <td>{{ $plan->trainer->name ?? 'Unknown' }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $plan->status === 'approved' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ ucfirst($plan->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('plans.show', $plan) }}"
                                            class="btn btn-sm btn-outline-secondary">View</a>
                                        <a href="{{ route('plans.edit', $plan) }}"
                                            class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('plans.destroy', $plan) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Delete this plan?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No workout plans found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $plans->links() }}
                </div>
            </div>
        </main>
    </div>
@endsection