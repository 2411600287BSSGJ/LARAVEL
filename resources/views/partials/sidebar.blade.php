<aside class="col-lg-3 col-xl-2">
    <div class="card sidebar-card shadow-sm border-0">
        <div class="card-body p-3">
            <h6 class="text-muted text-uppercase fw-bold small mb-3 tracking-wide">
                <i class="bi bi-grid-fill me-1"></i> Navigation
            </h6>
            <nav class="nav flex-column nav-pills gap-1">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                    <i class="bi bi-box-seam me-2"></i>Products
                </a>
                <a class="nav-link {{ request()->routeIs('transactions.*') && !request()->routeIs('adjustments.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                    <i class="bi bi-arrow-left-right me-2"></i>Transactions
                </a>
                <a class="nav-link {{ request()->routeIs('adjustments.*') ? 'active' : '' }}" href="{{ route('adjustments.create') }}">
                    <i class="bi bi-sliders me-2"></i>Stock Adjustment
                </a>
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="bi bi-clipboard-data me-2"></i>Reports & Exports
                </a>

                <h6 class="text-muted text-uppercase fw-bold small mt-3 mb-2 tracking-wide">
                    <i class="bi bi-gear-fill me-1"></i> Management
                </h6>
                <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                    <i class="bi bi-tags me-2"></i>Categories
                </a>
                <a class="nav-link {{ request()->routeIs('brands.*') ? 'active' : '' }}" href="{{ route('brands.index') }}">
                    <i class="bi bi-award me-2"></i>Brands
                </a>
                <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                    <i class="bi bi-truck me-2"></i>Suppliers
                </a>
                <a class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}">
                    <i class="bi bi-receipt me-2"></i>Purchase Orders
                </a>
                @if (auth()->user()->isAdmin())
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <i class="bi bi-people me-2"></i>Users & Roles
                    </a>
                @endif
            </nav>
            <hr>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-danger w-100 text-start btn-sm" type="submit">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </button>
            </form>
        </div>
    </div>
</aside>
