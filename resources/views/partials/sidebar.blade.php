<aside class="col-lg-3 col-xl-2 mb-3 mb-lg-0">
    <div class="card sidebar-card shadow-sm border-0">
        <div class="card-body p-3">
            <h6 class="text-muted text-uppercase fw-bold small mb-3">Inventory</h6>
            <nav class="nav flex-column nav-pills gap-1">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'text-muted' }}"
                    href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : 'text-muted' }}"
                    href="{{ route('products.index') }}">
                    <i class="bi bi-box-seam me-2"></i>Products
                </a>
                <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : 'text-muted' }}"
                    href="{{ route('transactions.index') }}">
                    <i class="bi bi-arrow-left-right me-2"></i>Transactions
                </a>
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : 'text-muted' }}"
                    href="{{ route('reports.index') }}">
                    <i class="bi bi-clipboard-data me-2"></i>Reports
                </a>
                <a class="nav-link {{ request()->routeIs('brands.*') ? 'active' : 'text-muted' }}"
                    href="{{ route('brands.index') }}">
                    <i class="bi bi-award me-2"></i>Brands
                </a>
                <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : 'text-muted' }}"
                    href="{{ route('categories.index') }}">
                    <i class="bi bi-tags me-2"></i>Categories
                </a>

                @if (auth()->user()->role === 'admin')
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : 'text-muted' }}"
                        href="{{ route('users.index') }}">
                        <i class="bi bi-people me-2"></i>Users
                    </a>
                @endif
            </nav>
            <hr>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-secondary w-100 text-start" type="submit">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </button>
            </form>
        </div>
    </div>
</aside>