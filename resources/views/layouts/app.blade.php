@php
    // Automatically fetch low stock data for the navbar
    $lowStockProducts = \App\Models\Product::whereColumn('quantity', '<=', 'reorder_level')->orderBy('quantity', 'asc')->take(5)->get();
    $lowStockCount = $lowStockProducts->count();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - Gym Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="dashboard-body">
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
                <i class="bi bi-boxes me-2"></i>Gym Inventory
            </a>
            <div class="ms-auto d-flex align-items-center gap-3 text-white">
                <span class="d-none d-md-inline">{{ auth()->user()->name }} · {{ ucfirst(auth()->user()->role) }}</span>
                <div class="dropdown">
                    <button id="lowStockBell" class="btn btn-sm {{ $lowStockCount > 0 ? 'btn-danger' : 'btn-outline-light' }} position-relative"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        aria-label="{{ $lowStockCount > 0 ? $lowStockCount.' low-stock alerts' : 'No low-stock alerts' }}">
                        <i class="bi bi-bell-fill"></i>
                        @if ($lowStockCount > 0)
                            <span id="lowStockBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-light text-danger">
                                {{ $lowStockCount }}
                            </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow" style="min-width: 290px">
                        <h6 class="dropdown-header">Low-stock alerts</h6>
                        @forelse ($lowStockProducts as $product)
                            <a class="dropdown-item text-wrap" href="{{ route('products.index', ['search' => $product->sku]) }}">
                                <strong>{{ $product->name }}</strong>
                                <span class="d-block small {{ $product->quantity === 0 ? 'text-danger' : 'text-warning' }}">
                                    {{ $product->quantity === 0 ? 'Out of stock' : 'Low stock' }}
                                    · {{ $product->quantity }} on hand / reorder at {{ $product->reorder_level }}
                                </span>
                            </a>
                        @empty
                            <span class="dropdown-item-text text-muted">Inventory levels are healthy.</span>
                        @endforelse
                        @if ($lowStockCount > 5)
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('reports.index') }}">View all inventory alerts</a>
                        @endif
                    </div>
                </div>
                <a class="btn btn-sm btn-outline-light" href="{{ route('profile.edit') }}">Profile</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="row g-4">
            @include('partials.sidebar')
            
            <main class="col-lg-9 col-xl-10">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                
                @yield('content')
                
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('scripts')
</body>
</html>