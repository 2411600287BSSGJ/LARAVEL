<div class="col-12 col-lg-3 col-xl-2 mb-3 mb-lg-0">
    <div class="card sidebar-card shadow-sm border-0">
        <div class="card-body p-3">
            <h6 class="text-muted text-uppercase fw-bold small mb-3">Main Menu</h6>
            <ul class="nav flex-column nav-pills">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'text-muted' }}"
                        href="{{ route('dashboard') }}">
                        <i class="bi bi-grid-1x2-fill me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('workouts.*') ? 'active' : 'text-muted' }}"
                        href="{{ route('workouts.index') }}">
                        <i class="bi bi-dumbbell me-2"></i>Workouts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('schedule') ? 'active' : 'text-muted' }}"
                        href="{{ route('schedule') }}">
                        <i class="bi bi-calendar-event me-2"></i>Schedule
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress') ? 'active' : 'text-muted' }}"
                        href="{{ route('progress') }}">
                        <i class="bi bi-graph-up-arrow me-2"></i>Progress
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings') ? 'active' : 'text-muted' }}"
                        href="{{ route('settings') }}">
                        <i class="bi bi-gear me-2"></i>Settings
                    </a>
                </li>
            </ul>
            <hr>
            <h6 class="text-muted text-uppercase fw-bold small mb-3">Stats</h6>
            <div class="small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Today</span>
                    <span class="fw-bold text-primary">{{ date('M d, Y') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Streak</span>
                    <span class="fw-bold text-accent">0 days</span>
                </div>
            </div>
        </div>
    </div>
</div>