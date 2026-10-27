<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body class="dashboard-body">

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <img src="{{ asset('js/assets/catsss.png') }}" alt="Fitness Tracker logo" class="navbar-brand-logo">
                <span>Fitness Tracker</span>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <span class="nav-link text-light me-3 d-none d-md-block">Welcome,
                    {{ auth()->user()->name ?? 'Admin' }}!</span>

                <!-- NOTIFICATION DROPDOWN -->
                <div class="nav-item dropdown me-3">
                    <a class="nav-link position-relative text-white" href="#" id="notifDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill fs-5"></i>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="notifDropdown"
                        style="width: 320px; z-index: 9999;">
                        <li>
                            <h6 class="dropdown-header text-uppercase fw-bold">Notifications</h6>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        @forelse(auth()->user()->unreadNotifications as $notification)
                            <li>
                                <!-- UPDATED LINK HERE -->
                                <a class="dropdown-item text-wrap text-dark"
                                    href="{{ route('notifications.read', $notification->id) }}">
                                    <i class="bi bi-info-circle-fill text-danger me-2"></i>
                                    {{ $notification->data['message'] }}
                                </a>
                            </li>
                        @empty
                            <li><span class="dropdown-item text-muted">No new alerts</span></li>
                        @endforelse
                    </ul>
                </div>

                <!-- LOGOUT BUTTON -->
                <form method="POST" action="{{ route('logout') }}" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('scripts')
</body>

</html>