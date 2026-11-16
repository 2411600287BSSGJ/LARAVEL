<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Gym Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    @stack('styles')
</head>
<body class="dashboard-body">
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <i class="bi bi-activity text-danger me-2 fs-4"></i>
                <span>Gym Inventory</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav" aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="topNav">
                <div class="ms-auto d-flex align-items-center gap-3 mt-2 mt-lg-0">
                    <!-- Notifications Dropdown -->
                    @php
                        $unreadNotifications = auth()->user()->unreadNotifications()->take(5)->get();
                        $unreadCount = auth()->user()->unreadNotifications()->count();
                    @endphp
                    <div class="dropdown" id="notificationDropdownContainer">
                        <button class="btn btn-outline-light position-relative rounded-circle p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Low Stock Alerts">
                            <i class="bi bi-bell-fill"></i>
                            <span id="nav-notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $unreadCount > 0 ? '' : 'd-none' }}">
                                {{ $unreadCount }}
                                <span class="visually-hidden">unread alerts</span>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow notification-menu p-0">
                            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Low Stock Alerts</h6>
                                @if($unreadCount > 0)
                                    <form action="{{ route('notifications.markAllRead') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            <div class="list-group list-group-flush" id="notificationList">
                                @forelse($unreadNotifications as $notification)
                                    <a href="{{ route('products.show', $notification->data['product_id'] ?? 1) }}" class="list-group-item list-group-item-action notification-item py-2 px-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <strong class="text-dark">{{ $notification->data['product_name'] ?? 'Product Alert' }}</strong>
                                            <span class="badge bg-warning text-dark">{{ $notification->data['current_quantity'] ?? 0 }} left</span>
                                        </div>
                                        <p class="mb-1 text-muted small">{{ $notification->data['message'] ?? 'Stock is at or below reorder level.' }}</p>
                                        <small class="text-secondary">{{ $notification->created_at->diffForHumans() }}</small>
                                    </a>
                                @empty
                                    <div class="p-4 text-center text-muted small">
                                        <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                                        All stock levels are optimal. No alerts.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- User Dropdown with Logout -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5"></i>
                            <span class="d-none d-md-inline fw-semibold">{{ auth()->user()->name }}</span>
                            <span class="badge bg-secondary text-uppercase">{{ auth()->user()->role ?? 'Staff' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header">{{ auth()->user()->email }}</h6></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Layout -->
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="row g-4">
            @include('partials.sidebar')
            <main class="col-lg-9 col-xl-10">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
