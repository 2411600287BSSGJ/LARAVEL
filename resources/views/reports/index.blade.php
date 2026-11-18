@extends('layouts.app')

@section('title', 'Inventory Reports')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-file-earmark-bar-graph text-primary me-1"></i>Business Intelligence
            </p>
            <h1 class="h2 fw-bold mb-1">Inventory Reports & Analysis</h1>
            <p class="text-muted mb-0">Generate stock movement audits, replenishment alerts, and financial inventory valuations.</p>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-file-earmark-arrow-down me-1"></i>Quick CSV Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="{{ route('reports.export.stock-movement') }}"><i class="bi bi-arrow-left-right me-2"></i>Stock Movements CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('reports.export.low-stock') }}"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Report CSV</a></li>
                <li><a class="dropdown-item" href="{{ route('reports.export.valuation') }}"><i class="bi bi-cash-stack me-2"></i>Inventory Valuation CSV</a></li>
            </ul>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body p-3">
                    <small class="text-muted text-uppercase fw-semibold d-block">Total Inventory Valuation</small>
                    <h3 class="fw-bold text-success mb-1 mt-1">₱{{ number_format($totalInventoryValue, 2) }}</h3>
                    <small class="text-muted">{{ number_format($totalItemsCount) }} total items across all categories</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                <div class="card-body p-3">
                    <small class="text-muted text-uppercase fw-semibold d-block">Low Stock & Out of Stock</small>
                    <h3 class="fw-bold text-warning mb-1 mt-1">{{ $lowStockProducts->count() }} Items</h3>
                    <small class="text-muted">Requires replenishment</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
                <div class="card-body p-3">
                    <small class="text-muted text-uppercase fw-semibold d-block">Audited Stock Movements</small>
                    <h3 class="fw-bold text-info mb-1 mt-1">{{ $movements->total() }} Logged</h3>
                    <small class="text-muted">Between {{ \Carbon\Carbon::parse($startDate)->format('M d') }} and {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs for the 3 Reports -->
    <ul class="nav nav-tabs nav-fill mb-4 bg-white rounded-top shadow-sm border-bottom-0" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold py-3" id="movement-tab" data-bs-toggle="tab" data-bs-target="#movement-pane" type="button" role="tab">
                <i class="bi bi-arrow-left-right me-1"></i>Stock Movement Report
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-3" id="lowstock-tab" data-bs-toggle="tab" data-bs-target="#lowstock-pane" type="button" role="tab">
                <i class="bi bi-exclamation-triangle text-warning me-1"></i>Low Stock Report
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-3" id="valuation-tab" data-bs-toggle="tab" data-bs-target="#valuation-pane" type="button" role="tab">
                <i class="bi bi-cash-stack text-success me-1"></i>Inventory Valuation Report
            </button>
        </li>
    </ul>

    <div class="tab-content" id="reportTabContent">
        <!-- 1. Stock Movement Report -->
        <div class="tab-pane fade show active" id="movement-pane" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0 fw-bold">Stock Movement Audit Trail</h5>
                        <small class="text-muted">Filtered transactions between selected dates</small>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="GET" action="{{ route('reports.index') }}" class="d-flex align-items-center gap-2">
                            <input type="date" class="form-control form-control-sm" name="start_date" value="{{ $startDate }}">
                            <span class="text-muted">to</span>
                            <input type="date" class="form-control form-control-sm" name="end_date" value="{{ $endDate }}">
                            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        </form>
                        <a href="{{ route('reports.export.stock-movement', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-download me-1"></i>Export CSV
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Product / SKU</th>
                                <th>Type</th>
                                <th class="text-center">Quantity</th>
                                <th>Unit Price (₱)</th>
                                <th>Reference</th>
                                <th>Authorized By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $tx)
                                @php
                                    $isStockIn = in_array(strtolower($tx->type), ['stock_in', 'in', 'inbound', 'purchase']);
                                    $isStockOut = in_array(strtolower($tx->type), ['stock_out', 'out', 'outbound', 'sale']);
                                    $sign = $isStockIn ? '+' : ($isStockOut ? '-' : ($tx->quantity >= 0 ? '+' : ''));
                                    $colorClass = $isStockIn ? 'text-success' : ($isStockOut ? 'text-danger' : 'text-warning');
                                @endphp
                                <tr>
                                    <td><small class="fw-semibold">{{ $tx->created_at->format('Y-m-d H:i') }}</small></td>
                                    <td>
                                        <strong>{{ $tx->product?->name ?? 'Archived Product' }}</strong>
                                        <small class="text-muted font-monospace d-block">{{ $tx->product?->sku }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $tx->type_badge_color }}">
                                            {{ $tx->type_display }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong class="{{ $colorClass }} fs-6">{{ $sign }}{{ abs($tx->quantity) }}</strong>
                                    </td>
                                    <td>₱{{ number_format($tx->unit_price ?? $tx->product?->unit_price ?? 0, 2) }}</td>
                                    <td><code>{{ $tx->reference_number ?? '—' }}</code></td>
                                    <td><small>{{ $tx->user?->name ?? 'System' }}</small></td>
                                    <td><small class="text-muted">{{ $tx->notes ?: '—' }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No stock movements found within the selected date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($movements->hasPages())
                    <div class="card-footer bg-white border-top p-3">
                        {{ $movements->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. Low Stock Report -->
        <div class="tab-pane fade" id="lowstock-pane" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">Low Stock & Reorder Requirements</h5>
                        <small class="text-muted">Products where current quantity is less than or equal to reorder threshold</small>
                    </div>
                    <a href="{{ route('reports.export.low-stock') }}" class="btn btn-sm btn-success">
                        <i class="bi bi-download me-1"></i>Export Low Stock CSV
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th class="text-center">Current Stock</th>
                                <th class="text-center">Reorder Point</th>
                                <th>Unit Price (₱)</th>
                                <th>Preferred Supplier</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockProducts as $p)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $p) }}" class="fw-bold text-decoration-none text-dark">
                                            {{ $p->name }}
                                        </a>
                                    </td>
                                    <td><code>{{ $p->sku }}</code></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $p->category_name }}</span></td>
                                    <td class="text-center">
                                        <strong class="fs-6 text-{{ $p->quantity == 0 ? 'danger' : 'warning' }}">{{ $p->quantity }}</strong>
                                    </td>
                                    <td class="text-center">{{ $p->reorder_level }}</td>
                                    <td>₱{{ number_format($p->unit_price, 2) }}</td>
                                    <td>{{ $p->supplier_name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $p->stock_status_color }}">
                                            {{ $p->stock_status }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('transactions.create', ['product_id' => $p->id, 'type' => 'stock_in']) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-box-arrow-in-down me-1"></i>Replenish
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="bi bi-check2-circle text-success fs-1 d-block mb-2"></i>
                                        All inventory items are currently well stocked above reorder thresholds.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. Inventory Valuation Report -->
        <div class="tab-pane fade" id="valuation-pane" role="tabpanel">
            <!-- Category Valuation Breakdown Cards -->
            <div class="row g-3 mb-4">
                @foreach($valuationByCategory as $cat)
                    <div class="col-sm-6 col-md-4 col-xl-2">
                        <div class="card shadow-sm border-0 h-100 bg-white">
                            <div class="card-body p-3">
                                <small class="text-muted text-uppercase fw-semibold d-block">{{ $cat['category'] }}</small>
                                <strong class="fs-5 text-dark d-block mt-1">₱{{ number_format($cat['total_value'], 2) }}</strong>
                                <small class="text-muted">{{ $cat['total_quantity'] }} units ({{ $cat['count'] }} SKUs)</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">Inventory Valuation Schedule</h5>
                        <small class="text-muted">Total financial asset valuation calculated as Quantity on Hand × Unit Price</small>
                    </div>
                    <a href="{{ route('reports.export.valuation') }}" class="btn btn-sm btn-success">
                        <i class="bi bi-download me-1"></i>Export Valuation CSV
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th class="text-center">Quantity on Hand</th>
                                <th class="text-end">Unit Price (₱)</th>
                                <th class="text-end">Total Valuation (₱)</th>
                                <th>Supplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($valuationProducts as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $item) }}" class="fw-bold text-decoration-none text-dark">
                                            {{ $item->name }}
                                        </a>
                                    </td>
                                    <td><code>{{ $item->sku }}</code></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $item->category_name }}</span></td>
                                    <td class="text-center"><strong>{{ $item->quantity }}</strong></td>
                                    <td class="text-end">₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end fw-bold text-success">₱{{ number_format($item->total_value, 2) }}</td>
                                    <td><small class="text-muted">{{ $item->supplier_name }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="fs-6">Grand Total Valuation</th>
                                <th class="text-center fs-6">{{ number_format($totalItemsCount) }} units</th>
                                <th></th>
                                <th class="text-end fs-5 text-success fw-bold">₱{{ number_format($totalInventoryValue, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
