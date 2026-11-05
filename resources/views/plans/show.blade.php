@extends('layouts.app')

@section('content')
    <div class="row">
        @include('partials.sidebar')

        <main class="col-lg-9 col-xl-10">

            <!-- Page Header -->
            <div class="dashboard-heading d-flex justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <p class="eyebrow mb-2">Procurement / Plan Details</p>
                    <h1 class="fw-bold mb-1">{{ $plan->reference_number }}</h1>
                    <p class="text-muted mb-0">Created by {{ $plan->creator->name ?? 'System' }} on
                        {{ $plan->created_at->format('M d, Y') }}</p>
                </div>
                <a href="{{ route('plans.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Plans
                </a>
            </div>

            <div class="row g-3 mb-4">
                <!-- Trainer Info Card -->
                <div class="col-md-4">
                    <div class="metric-card h-100">
                        <span class="metric-label"><i class="bi bi-person-badge me-1"></i> Trainer</span>
                        <strong class="text-truncate">{{ $plan->trainer->name ?? 'Unknown' }}</strong>
                        <span class="metric-meta">{{ $plan->trainer->specialty ?? '' }}</span>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="col-md-4">
                    <div class="metric-card h-100 {{ $plan->status === 'approved' ? 'metric-success' : 'metric-warning' }}">
                        <span class="metric-label"><i class="bi bi-info-circle me-1"></i> Current Status</span>
                        <strong class="text-uppercase">{{ $plan->status }}</strong>
                        <span class="metric-meta">
                            @if($plan->status === 'pending')
                                Awaiting Admin Approval
                            @elseif($plan->status === 'approved')
                                Approved by {{ $plan->approver->name ?? 'Admin' }}
                            @else
                                Plan is in Draft
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Notes Card -->
                <div class="col-md-4">
                    <div class="metric-card h-100">
                        <span class="metric-label"><i class="bi bi-card-text me-1"></i> Notes</span>
                        <strong class="fs-6 fw-normal mt-2">{{ $plan->notes ?: 'No notes provided.' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Exercises Table -->
            <div class="panel mb-4">
                <div class="panel-heading mb-3">
                    <h2>Prescribed Exercises</h2>
                </div>

                <div class="table-responsive">
                    <table class="table inventory-table mb-0">
                        <thead>
                            <tr>
                                <th>Exercise</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plan->items as $item)
                                <tr>
                                    <td><strong>{{ $item->exercise_name }}</strong></td>
                                    <td>{{ $item->quantity }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $item->unit }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No exercises in this plan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Action Area -->
            <div class="panel">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Plan Actions</h4>
                        <p class="text-muted small mb-0">Only Admins can approve pending plans.</p>
                    </div>
                    <div>
                        @if($plan->status === 'pending')
                            <!-- Using the Policy to check authorization -->
                            @can('approve', $plan)
                                <form action="{{ route('plans.approve', $plan) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle me-1"></i> Approve Plan
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small fst-italic">You do not have permission to approve this plan.</span>
                            @endcan
                        @elseif($plan->status === 'approved')
                            <button class="btn btn-outline-success" disabled>
                                <i class="bi bi-check-all me-1"></i> Already Approved
                            </button>
                        @endif
                    </div>
                </div>
            </div>

        </main>
    </div>
@endsection