@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-hammer text-danger me-1"></i>Hardware Store Operations
            </p>
            <h1 class="h2 fw-bold mb-1">Inventory Dashboard</h1>
            <p class="text-muted mb-0">Real-time inventory levels, stock alerts, and valuation.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('adjustments.create') }}">
                <i class="bi bi-sliders me-1"></i>Stock Adjustment
            </a>
            <a class="btn btn-success" href="{{ route('products.create') }}">
                <i class="bi bi-plus-circle me-1"></i>Add Product
            </a>
            <a class="btn btn-primary" href="{{ route('transactions.create') }}">
                <i class="bi bi-arrow-left-right me-1"></i>Record Transaction
            </a>
        </div>
    </div>

    <!-- Real-time Polling Status Bar -->
    <div class="d-flex justify-content-between align-items-center bg-white border rounded px-3 py-2 mb-4 shadow-sm">
        <span class="small text-muted d-flex align-items-center">
            <span class="spinner-grow spinner-grow-sm text-success me-2" role="status" style="width: 0.65rem; height: 0.65rem;"></span>
            Auto-refresh active (30s polling)
        </span>
        <span class="small text-muted">
            Last Updated: <strong id="last-updated-timestamp">{{ now()->format('M d, Y h:i:s A') }}</strong>
        </span>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Products -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-stat h-100">
                <div class="card-body d-flex justify-content-between align-items-center p-3">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Total Products</span>
                        <h2 class="fw-bold mb-0 mt-1" id="stat-total-products">{{ number_format($totalProducts) }}</h2>
                        <small class="text-secondary">Catalog items</small>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-boxes"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-stat h-100 border-start border-warning border-4">
                <div class="card-body d-flex justify-content-between align-items-center p-3">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Low Stock Items</span>
                        <h2 class="fw-bold text-warning mb-0 mt-1" id="stat-low-stock">{{ number_format($lowStock) }}</h2>
                        <small class="text-muted">≤ Reorder Threshold</small>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Out of Stock -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-stat h-100 border-start border-danger border-4">
                <div class="card-body d-flex justify-content-between align-items-center p-3">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Out of Stock</span>
                        <h2 class="fw-bold text-danger mb-0 mt-1" id="stat-out-of-stock">{{ number_format($outOfStock) }}</h2>
                        <small class="text-danger">0 units available</small>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-x-octagon-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Inventory Value -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-stat h-100 border-start border-success border-4">
                <div class="card-body d-flex justify-content-between align-items-center p-3">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Inventory Valuation</span>
                        <h2 class="fw-bold text-success mb-0 mt-1" id="stat-inventory-value">${{ number_format((float) $inventoryValue, 2) }}</h2>
                        <small class="text-muted">Quantity × Unit Price</small>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts Section -->
    <div class="card shadow-sm border-0 mb-4" id="low-stock-card">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="badge bg-warning text-dark me-2"><i class="bi bi-bell-fill"></i> Attention</span>
                <h5 class="mb-0 fw-bold">Low Stock & Reorder Alerts</h5>
            </div>
            <a href="{{ route('products.index', ['stock' => 'low']) }}" class="btn btn-sm btn-outline-warning text-dark">
                View All Low Stock
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>In Stock</th>
                        <th>Reorder Level</th>
                        <th>Unit Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="dashboard-low-stock-tbody">
                    @php
                        $alertProducts = \App\Models\Product::with(['category', 'supplier'])
                            ->whereColumn('quantity', '<=', 'reorder_level')
                            ->orderBy('quantity', 'asc')
                            ->take(6)
                            ->get();
                    @endphp
                    @forelse ($alertProducts as $item)
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $item->id) }}" class="fw-bold text-decoration-none text-dark">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td><code>{{ $item->sku }}</code></td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $item->category_name }}</span></td>
                            <td><strong class="text-{{ $item->quantity == 0 ? 'danger' : 'warning' }}">{{ $item->quantity }}</strong></td>
                            <td>{{ $item->reorder_level }}</td>
                            <td>${{ number_format($item->unit_price, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $item->stock_status_color }}">
                                    {{ $item->stock_status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('transactions.create', ['product_id' => $item->id, 'type' => 'stock_in']) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-box-arrow-in-down me-1"></i>Replenish
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">
                                <i class="bi bi-check-circle-fill text-success me-1"></i> All products are currently stocked above reorder thresholds.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Products & Recent Transactions -->
    <div class="row g-4 mb-4">
        <!-- Recent Products -->
        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-box-seam text-primary me-2"></i>Recently Added Products</h5>
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-primary">View Catalog</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product / SKU</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Unit Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentProducts as $product)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $product->id) }}" class="fw-semibold text-decoration-none text-dark d-block">
                                            {{ $product->name }}
                                        </a>
                                        <small class="text-muted">{{ $product->sku }}</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $product->category_name }}</span></td>
                                    <td><strong>{{ $product->quantity }}</strong></td>
                                    <td>${{ number_format($product->unit_price, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $product->stock_status_color }}">
                                            {{ $product->stock_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No products recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-xl-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history text-info me-2"></i>Recent Stock Movements</h5>
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-info">All Transactions</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody id="dashboard-recent-transactions-tbody">
                            @forelse ($recentTransactions as $tx)
                                <tr>
                                    <td><small class="text-muted">{{ $tx->created_at->diffForHumans() }}</small></td>
                                    <td>
                                        <a href="{{ $tx->product ? route('products.show', $tx->product_id) : '#' }}" class="text-dark fw-semibold text-decoration-none">
                                            {{ $tx->product?->name ?? 'Deleted Item' }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $tx->type_badge_color }}">
                                            {{ $tx->type_display }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-{{ in_array($tx->type, ['stock_in', 'in', 'inbound']) ? 'success' : (in_array($tx->type, ['stock_out', 'out', 'outbound']) ? 'danger' : 'secondary') }}">
                                            {{ in_array($tx->type, ['stock_in', 'in', 'inbound']) ? '+' : (in_array($tx->type, ['stock_out', 'out', 'outbound']) ? '-' : '') }}{{ abs($tx->quantity) }}
                                        </strong>
                                    </td>
                                    <td><small>{{ $tx->user?->name ?? 'System' }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No transactions recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    /**
     * Real-time Dashboard AJAX Polling (every 30 seconds)
     */
    function animateChange(element, newValue) {
        if (!element) return;
        if (element.textContent.trim() !== newValue.trim()) {
            element.textContent = newValue;
            element.classList.add('stat-updated');
            setTimeout(() => element.classList.remove('stat-updated'), 1000);
        }
    }

    async function pollDashboardData() {
        try {
            // 1. Fetch Stats
            const statsRes = await fetch("{{ route('api.dashboard.stats') }}", {
                headers: { 'Accept': 'application/json' }
            });
            if (statsRes.ok) {
                const data = await statsRes.json();
                animateChange(document.getElementById('stat-total-products'), Number(data.total_products).toLocaleString());
                animateChange(document.getElementById('stat-low-stock'), Number(data.low_stock_count).toLocaleString());
                animateChange(document.getElementById('stat-out-of-stock'), Number(data.out_of_stock_count).toLocaleString());
                animateChange(document.getElementById('stat-inventory-value'), data.formatted_inventory_value);
                document.getElementById('last-updated-timestamp').textContent = data.last_updated;
            }

            // 2. Fetch Low Stock Items
            const lowStockRes = await fetch("{{ route('api.products.low-stock') }}", {
                headers: { 'Accept': 'application/json' }
            });
            if (lowStockRes.ok) {
                const lowStockList = await lowStockRes.json();
                const tbody = document.getElementById('dashboard-low-stock-tbody');
                if (tbody && lowStockList.length > 0) {
                    let rows = '';
                    lowStockList.slice(0, 6).forEach(item => {
                        rows += `
                            <tr>
                                <td>
                                    <a href="${item.show_url}" class="fw-bold text-decoration-none text-dark">
                                        ${item.name}
                                    </a>
                                </td>
                                <td><code>${item.sku}</code></td>
                                <td><span class="badge bg-secondary-subtle text-secondary">${item.category}</span></td>
                                <td><strong class="text-${item.quantity === 0 ? 'danger' : 'warning'}">${item.quantity}</strong></td>
                                <td>${item.reorder_level}</td>
                                <td>${item.formatted_price}</td>
                                <td><span class="badge bg-${item.status_color}">${item.status}</span></td>
                                <td>
                                    <a href="/transactions/create?product_id=${item.id}&type=stock_in" class="btn btn-sm btn-primary">
                                        <i class="bi bi-box-arrow-in-down me-1"></i>Replenish
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = rows;
                }
            }

            // 3. Fetch Recent Transactions
            const txRes = await fetch("{{ route('api.transactions.recent') }}", {
                headers: { 'Accept': 'application/json' }
            });
            if (txRes.ok) {
                const txList = await txRes.json();
                const txTbody = document.getElementById('dashboard-recent-transactions-tbody');
                if (txTbody && txList.length > 0) {
                    let txRows = '';
                    txList.slice(0, 8).forEach(tx => {
                        const isPlus = ['stock_in', 'in', 'inbound', 'purchase'].includes(tx.raw_type.toLowerCase());
                        const isMinus = ['stock_out', 'out', 'outbound', 'sale'].includes(tx.raw_type.toLowerCase());
                        const prefix = isPlus ? '+' : (isMinus ? '-' : '');
                        const colorClass = isPlus ? 'success' : (isMinus ? 'danger' : 'secondary');

                        txRows += `
                            <tr>
                                <td><small class="text-muted">${tx.time_ago}</small></td>
                                <td>
                                    <a href="${tx.product_url}" class="text-dark fw-semibold text-decoration-none">
                                        ${tx.product_name}
                                    </a>
                                </td>
                                <td><span class="badge bg-${tx.type_badge}">${tx.type}</span></td>
                                <td><strong class="text-${colorClass}">${prefix}${Math.abs(tx.quantity)}</strong></td>
                                <td><small>${tx.user_name}</small></td>
                            </tr>
                        `;
                    });
                    txTbody.innerHTML = txRows;
                }
            }
        } catch (error) {
            console.warn('Dashboard polling failed (will retry):', error);
        }
    }

    // Initialize polling interval (30 seconds)
    setInterval(pollDashboardData, 30000);
</script>
@endpush
