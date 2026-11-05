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
            <!-- SCHEDULE TAB CONTENT                       -->
            <!-- ========================================== -->
            @if($type === 'schedule')

                <!-- Scheduled Workouts List (TOP) -->
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

                                            <!-- TRAINER ADDED HERE -->
                                            <p class="small text-muted mb-2">
                                                <i class="bi bi-person-badge me-1"></i>
                                                Trainer: <strong>{{ $w->trainer->name ?? 'Unassigned' }}</strong>
                                            </p>

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

                <!-- FullCalendar Widget (BOTTOM) -->
                <div class="panel mb-4">
                    <div class="panel-heading mb-3">
                        <h2>Calendar View</h2>
                    </div>
                    <div class="card sidebar-card border-0 shadow-sm p-3">
                        <div id="calendar" style="max-width: 800px; margin: 0 auto;"></div>
                    </div>
                </div>

            @endif

            <!-- ========================================== -->
            <!-- PROGRESS TAB CONTENT                       -->
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
            <!-- SETTINGS TAB CONTENT (CLEAN VERSION)       -->
            <!-- ========================================== -->
            @if($type === 'settings')
                <div class="panel mb-4">
                    <div class="panel-heading mb-4">
                        <div>
                            <p class="eyebrow mb-1">Account</p>
                            <h2>Profile Settings</h2>
                        </div>
                    </div>

                    <form onsubmit="event.preventDefault(); alert('Settings saved successfully!');">
                        <div class="row g-4">

                            <!-- Personal Info Section -->
                            <div class="col-md-6">
                                <div class="card sidebar-card border-0 shadow-sm h-100 p-3">
                                    <h6 class="text-muted text-uppercase fw-bold small mb-3">Personal Information</h6>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold"><i class="bi bi-person me-1"></i> Display
                                            Name</label>
                                        <input type="text" class="form-control" value="{{ auth()->user()->name }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold"><i class="bi bi-envelope me-1"></i> Email
                                            Address</label>
                                        <input type="email" class="form-control" value="{{ auth()->user()->email }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Preferences Section -->
                            <div class="col-md-6">
                                <div class="card sidebar-card border-0 shadow-sm h-100 p-3">
                                    <h6 class="text-muted text-uppercase fw-bold small mb-3">App Preferences</h6>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold"><i class="bi bi-palette me-1"></i> App
                                            Theme</label>
                                        <select class="form-select">
                                            <option>Glassmorphism (Default)</option>
                                            <option>Dark Mode</option>
                                            <option>Light Mode</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold"><i class="bi bi-bell me-1"></i>
                                            Notifications</label>
                                        <select class="form-select">
                                            <option>Enabled</option>
                                            <option>Disabled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Save Button -->
                            <div class="col-12 d-flex justify-content-end mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary px-5 fw-bold">
                                    <i class="bi bi-save me-2"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

        </main>
    </div>
@endsection

@if($type === 'schedule')
    @push('scripts')
        <!-- FullCalendar Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var calendarEl = document.getElementById('calendar');

                if (calendarEl) {
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        themeSystem: 'bootstrap5',
                        height: 'auto',
                        contentHeight: 500,
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,listWeek'
                        },
                        events: @json($events ?? []),
                        eventClick: function (info) {
                            alert('Workout: ' + info.event.title);
                        }
                    });
                    calendar.render();
                }
            });
        </script>
    @endpush
@endif