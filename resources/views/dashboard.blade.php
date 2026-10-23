@extends('layouts.app')

@section('content')
    <div class="row">

        <!-- SIDEBAR -->
        @include('partials.sidebar')

        <!-- MAIN CONTENT -->
        <main class="col-lg-9 col-xl-10">

            <!-- HEADER -->
            <div class="dashboard-heading d-flex justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <p class="eyebrow mb-2">Training intelligence / Overview</p>
                    <h1 class="fw-bold mb-1">Welcome back, Admin!</h1>
                    <p class="text-muted mb-0">Turn every workout into a clearer picture of your progress.</p>
                </div>
            </div>

            <!-- METRIC CARDS -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="metric-card">
                        <span class="metric-label">Workouts</span>
                        <strong>{{ $totalWorkouts }}</strong>
                        <span class="metric-meta"><i class="bi bi-dumbbell"></i> Total sessions</span>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="metric-card">
                        <span class="metric-label">Minutes</span>
                        <strong>{{ number_format($totalMinutes) }}</strong>
                        <span class="metric-meta"><i class="bi bi-stopwatch"></i> Total active time</span>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="metric-card metric-warning">
                        <span class="metric-label">Calories</span>
                        <strong>{{ number_format($totalCalories) }}</strong>
                        <span class="metric-meta"><i class="bi bi-fire"></i> Burned total</span>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="metric-card metric-danger">
                        <span class="metric-label">Focus</span>
                        <strong>{{ $focusCount }}</strong>
                        <span class="metric-meta"><i class="bi bi-exclamation-triangle"></i> Need attention</span>
                    </div>
                </div>
            </div>

            <!-- FOCUS WORKOUTS ALERTS -->
            <div class="panel mb-4">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow mb-1">Attention</p>
                        <h2>Focus workouts</h2>
                    </div>
                    <span class="badge bg-danger" id="alertBadge">{{ $focusCount }}</span>
                </div>
                <div class="mt-3" id="alertContainer">
                    @if($focusWorkouts->isEmpty())
                        <div class="text-center py-2">
                            <i class="bi bi-check-circle-fill text-success fs-1 d-block"></i>
                            <p class="text-muted mb-0">No pending workouts. You're on track!</p>
                        </div>
                    @else
                        <div id="alertsList">
                            @foreach($focusWorkouts as $w)
                                @php $isMissed = $w->status === 'missed'; @endphp
                                <div
                                    class="alert {{ $isMissed ? 'bg-danger text-white' : 'bg-warning text-dark' }} alert-dismissible fade show d-flex align-items-center mb-2">
                                    <i class="bi {{ $isMissed ? 'bi-x-circle-fill' : 'bi-clock-fill' }} me-3 fs-4"></i>
                                    <div class="flex-grow-1">
                                        <strong>{{ ucfirst($w->status) }}:</strong> {{ $w->name }} ({{ $w->type }}) —
                                        {{ $w->minutes }} min, {{ $w->calories }} kcal
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- 4 CHARTS -->
            <div class="row g-3 mb-4">
                <div class="col-lg-7">
                    <div class="panel chart-panel">
                        <div class="panel-heading">
                            <div>
                                <p class="eyebrow mb-1">Weekly movement</p>
                                <h2>Minutes by workout type</h2>
                            </div>
                        </div>
                        <div class="chart-wrap"><canvas id="typeChart"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="panel chart-panel">
                        <div class="panel-heading">
                            <div>
                                <p class="eyebrow mb-1">Training mix</p>
                                <h2>Status distribution</h2>
                            </div>
                        </div>
                        <div class="chart-wrap doughnut-wrap"><canvas id="statusChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-7">
                    <div class="panel chart-panel">
                        <div class="panel-heading">
                            <div>
                                <p class="eyebrow mb-1">Top performers</p>
                                <h2>Top workouts by calories</h2>
                            </div>
                        </div>
                        <div class="chart-wrap"><canvas id="topWorkoutsChart"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="panel chart-panel">
                        <div class="panel-heading">
                            <div>
                                <p class="eyebrow mb-1">Activity log</p>
                                <h2>Activity by category</h2>
                            </div>
                        </div>
                        <div class="chart-wrap"><canvas id="activityChart"></canvas></div>
                    </div>
                </div>
            </div>

            <!-- WORKOUT SESSIONS & FILTERS (Combined like Lab 4) -->
            <div class="panel workout-panel mb-4">
                <div class="panel-heading inventory-tools flex-wrap gap-3">
                    <div>
                        <p class="eyebrow mb-1">Activity log</p>
                        <h2>Workout sessions</h2>
                        <span class="text-muted small" id="resultCount">Showing {{ count($recentWorkouts) }} workouts</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-end">
                        <!-- Filters -->
                        <form action="{{ route('dashboard') }}" method="GET" class="d-flex flex-wrap gap-2">
                            <div>
                                <label class="form-label small text-muted mb-0">Type</label>
                                <select name="type" class="form-select form-select-sm" style="width: 130px;">
                                    <option value="all">All Types</option>
                                    @foreach($workoutTypes as $type)
                                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label small text-muted mb-0">Status</label>
                                <select name="status" class="form-select form-select-sm" style="width: 130px;">
                                    <option value="all">All Statuses</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>
                                        Scheduled</option>
                                    <option value="missed" {{ request('status') == 'missed' ? 'selected' : '' }}>Missed
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label small text-muted mb-0">Min Calories</label>
                                <input type="number" name="min_calories" class="form-control form-control-sm"
                                    value="{{ request('min_calories') }}" placeholder="0" style="width: 100px;">
                            </div>
                            <div class="d-flex gap-1 mt-4">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                            </div>
                        </form>
                        <!-- Export Button -->
                        <button class="btn btn-success btn-sm mt-4" id="exportBtn">
                            <i class="bi bi-download me-1"></i>Export CSV
                        </button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table inventory-table mb-0" id="workoutTable">
                        <thead>
                            <tr>
                                <th>Workout</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Calories</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentWorkouts as $w)
                                <tr>
                                    <td><strong>{{ $w->name }}</strong></td>
                                    <td>{{ $w->type }}</td>
                                    <td>{{ $w->minutes }} min</td>
                                    <td>{{ $w->calories }} kcal</td>
                                    <td><span
                                            class="status-badge status-{{ $w->status }}"><span></span>{{ ucfirst($w->status) }}</span>
                                    </td>
                                    <td class="text-muted">{{ $w->date_string }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No workouts match these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RECENT ACTIVITY LOG -->
            <div class="panel mb-4">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow mb-1">Activity log</p>
                        <h2>Recent activity</h2>
                    </div>
                    <span class="badge bg-light text-dark">{{ count($recentActivities) }} events</span>
                </div>
                <div class="table-responsive activity-scroll mt-2" style="max-height: 300px; overflow-y: auto;">
                    <table class="table inventory-table mb-0">
                        <thead>
                            <tr>
                                <th>Date &amp; Time</th>
                                <th>Workout</th>
                                <th>Activity</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivities as $a)
                                <tr>
                                    <td><small class="text-muted">{{ $a->created_at->format('Y-m-d H:i') }}</small></td>
                                    <td><strong>{{ $a->workout->name ?? 'Unknown' }}</strong><br><small
                                            class="text-muted">#{{ $a->workout_id }}</small></td>
                                    <td>{{ $a->description }}</td>
                                    <td><span
                                            class="status-badge status-{{ strtolower($a->category) }}"><span></span>{{ $a->category }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        // 1. Minutes by Type (Bar Chart)
        const typeData = @json($minutesByType);
        new Chart(document.getElementById('typeChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(typeData),
                datasets: [{ label: 'Minutes', data: Object.values(typeData), backgroundColor: '#d62828', borderRadius: 6 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // 2. Status Distribution (Doughnut Chart)
        const statusData = @json($statusDist);
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Scheduled', 'Missed'],
                datasets: [{ data: [statusData.completed, statusData.scheduled, statusData.missed], backgroundColor: ['#2d8a5b', '#b88a5a', '#d62828'], borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '55%' }
        });

        // 3. Top Workouts by Calories (Horizontal Bar)
        const topNames = @json($topWorkoutNames);
        const topCalories = @json($topWorkoutCalories);
        new Chart(document.getElementById('topWorkoutsChart'), {
            type: 'bar',
            data: {
                labels: topNames,
                datasets: [{ label: 'Calories', data: topCalories, backgroundColor: '#d62828', borderRadius: 6 }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } }
            }
        });

        // 4. Activity by Category (Bar Chart)
        const activityData = @json($activityStats);
        new Chart(document.getElementById('activityChart'), {
            type: 'bar',
            data: {
                labels: ['Completed', 'Scheduled', 'Missed'],
                datasets: [{
                    label: 'Events',
                    data: [activityData.Completed, activityData.Scheduled, activityData.Missed],
                    backgroundColor: ['#2d8a5b', '#b88a5a', '#d62828'],
                    borderRadius: 8
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // 5. Export to CSV Functionality
        document.getElementById('exportBtn').addEventListener('click', function () {
            let csvContent = "";
            const rows = document.querySelectorAll("#workoutTable tr");

            rows.forEach(row => {
                const cols = row.querySelectorAll("td, th");
                let rowData = [];
                cols.forEach(col => {
                    let text = col.innerText.replace(/(\r\n|\n|\r)/gm, "").trim();
                    rowData.push(`"${text.replace(/"/g, '""')}"`);
                });
                csvContent += rowData.join(",") + "\n";
            });

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.setAttribute("href", url);
            link.setAttribute("download", "fitness_workouts.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
@endpush