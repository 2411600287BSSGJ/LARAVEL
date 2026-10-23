@extends('layouts.app')

@section('content')
    <div class="row">

        @include('partials.sidebar')

        <main class="col-lg-9 col-xl-10">
            <div class="dashboard-heading d-flex justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <p class="eyebrow mb-2">Fitness Tracker / {{ $title }}</p>
                    <h1 class="fw-bold mb-1">{{ $title }}</h1>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- SCHEDULE TAB CONTENT -->
            <!-- ========================================== -->
            @if($type === 'schedule')
                <div class="panel mb-4">
                    <div class="panel-heading mb-3">
                        <h2>Upcoming Scheduled Workouts</h2>
                    </div>
                    @if($data->isEmpty())
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">You have no scheduled workouts right now.</p>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($data as $w)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card sidebar-card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <span class="badge bg-warning text-dark mb-2">Scheduled</span>
                                            <h4 class="fw-bold mb-1">{{ $w->name }}</h4>
                                            <p class="text-muted small mb-2">{{ $w->type }}</p>
                                            <hr>
                                            <div class="d-flex justify-content-between text-muted small">
                                                <span><i class="bi bi-clock me-1"></i> {{ $w->minutes }} mins</span>
                                                <span><i class="bi bi-fire me-1"></i> {{ $w->calories }} kcal</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <!-- ========================================== -->
            <!-- PROGRESS TAB CONTENT -->
            <!-- ========================================== -->
            @if($type === 'progress')
                <div class="panel mb-4">
                    <div class="panel-heading mb-3">
                        <h2>All-Time Completed Workouts</h2>
                    </div>
                    @if($data->isEmpty())
                        <p class="text-muted">No completed workouts yet. Go crush a session!</p>
                    @else
                        <div class="table-responsive">
                            <table class="table inventory-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Workout Name</th>
                                        <th>Calories Burned</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $index => $w)
                                        <tr>
                                            <td><strong>#{{ $index + 1 }}</strong></td>
                                            <td>{{ $w->name }}</td>
                                            <td><span class="badge bg-danger">{{ number_format($w->calories) }} kcal</span></td>
                                            <td>{{ $w->minutes }} min</td>
                                            <td><span class="status-badge status-completed"><span></span>Completed</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif

            <!-- ========================================== -->
            <!-- SETTINGS TAB CONTENT -->
            <!-- ========================================== -->
            @if($type === 'settings')
                <div class="panel mb-4">
                    <div class="panel-heading mb-3">
                        <h2>Profile Settings</h2>
                    </div>
                    <form onsubmit="event.preventDefault(); alert('Settings saved successfully!');">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Display Name</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control" value="{{ auth()->user()->email }}">
                            </div>
                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold">App Theme</label>
                                <select class="form-select">
                                    <option>Glassmorphism (Default)</option>
                                    <option>Dark Mode</option>
                                    <option>Light Mode</option>
                                </select>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

        </main>
    </div>
@endsection