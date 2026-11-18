@extends('layouts.app')

@section('title', $product->name . ' - Product Details')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-activity text-danger me-1"></i>Equipment Specification
            </p>
            <h1 class="h2 fw-bold mb-1">{{ $product->name }}</h1>
            <p class="text-muted mb-0 font-monospace">SKU: {{ $product->sku }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('products.index') }}">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <!-- Barcode Label Print -->
            <a class="btn btn-outline-dark" href="{{ route('products.barcode', $product) }}" target="_blank" title="Print Barcode Tag">
                <i class="bi bi-upc-scan me-1"></i>Barcode Label
            </a>
            <!-- Quick Stock In -->
            <a class="btn btn-outline-success" href="{{ route('transactions.create', ['product_id' => $product->id, 'type' => 'stock_in']) }}" title="Quick Stock In">
                <i class="bi bi-box-arrow-in-down me-1"></i>Stock In
            </a>
            <!-- Quick Stock Out -->
            <a class="btn btn-outline-danger" href="{{ route('transactions.create', ['product_id' => $product->id, 'type' => 'stock_out']) }}" title="Quick Stock Out">
                <i class="bi bi-box-arrow-up me-1"></i>Stock Out
            </a>
            <!-- Reorder Purchase Order -->
            @can('create', \App\Models\PurchaseOrder::class)
                <a class="btn btn-outline-warning text-dark" href="{{ route('purchase-orders.create', ['product_id' => $product->id, 'supplier_id' => $product->supplier_id]) }}" title="Create Purchase Order">
                    <i class="bi bi-cart-plus me-1"></i>Reorder
                </a>
            @endcan
            <!-- Edit -->
            <a class="btn btn-outline-primary" href="{{ route('products.edit', $product) }}">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            @if(Route::has('products.destroy'))
                <!-- Delete -->
                <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Delete this product permanently?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Product Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <small class="text-muted text-uppercase fw-semibold d-block">Current Stock</small>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="fs-2 fw-bold text-{{ $product->stock_status_color }}">{{ $product->quantity }}</span>
                        <span class="text-muted">units</span>
                    </div>
                    <span class="badge bg-{{ $product->stock_status_color }} mt-2">
                        {{ $product->stock_status }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <small class="text-muted text-uppercase fw-semibold d-block">Reorder Point</small>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="fs-2 fw-bold text-dark">{{ $product->reorder_level }}</span>
                        <span class="text-muted">units</span>
                    </div>
                    <small class="text-muted mt-2 d-block">Alert threshold</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <small class="text-muted text-uppercase fw-semibold d-block">Unit Price</small>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="fs-2 fw-bold text-dark">₱{{ number_format((float) $product->unit_price, 2) }}</span>
                    </div>
                    <small class="text-muted mt-2 d-block">Catalog price</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <small class="text-muted text-uppercase fw-semibold d-block">Total Inventory Value</small>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="fs-2 fw-bold text-success">₱{{ number_format($product->total_value, 2) }}</span>
                    </div>
                    <small class="text-muted mt-2 d-block">Qty × Unit Price</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Metadata Detail -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle text-primary me-2"></i>Product Information</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted fw-semibold d-block">Category</small>
                    <span class="badge bg-secondary-subtle text-secondary fs-6 px-3 py-2 mt-1">
                        {{ $product->category_name }}
                    </span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted fw-semibold d-block">Supplier / Vendor</small>
                    <div class="fs-6 fw-semibold text-dark mt-1">{{ $product->supplier_name }}</div>
                </div>
                <div class="col-md-4">
                    <small class="text-muted fw-semibold d-block">SKU Code</small>
                    <div class="fs-6 font-monospace text-dark mt-1">{{ $product->sku }}</div>
                </div>
                <div class="col-12 mt-3">
                    <small class="text-muted fw-semibold d-block">Description</small>
                    <p class="text-dark mb-0 mt-1">
                        {{ $product->description ?: 'No additional specifications or description provided.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Transaction History with Running Balance -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-clock-history text-info me-2"></i>Transaction History & Running Balance
                </h5>
                <small class="text-muted">Audit trail of all inbound, outbound, and adjustment stock movements</small>
            </div>
            <a href="{{ route('transactions.create', ['product_id' => $product->id]) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New Transaction
            </a>
        </div>

        <!-- Date Range Filter Form -->
        <div class="card-body bg-light border-bottom p-3">
            <form method="GET" action="{{ route('products.show', $product) }}" class="row g-2 align-items-end">
                <div class="col-sm-4 col-md-3">
                    <label class="form-label small fw-semibold">Start Date</label>
                    <input type="date" class="form-control form-control-sm" name="start_date" value="{{ $startDate ?? '' }}">
                </div>
                <div class="col-sm-4 col-md-3">
                    <label class="form-label small fw-semibold">End Date</label>
                    <input type="date" class="form-control form-control-sm" name="end_date" value="{{ $endDate ?? '' }}">
                </div>
                <div class="col-sm-4 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-filter me-1"></i>Filter Range
                    </button>
                    @if(!empty($startDate) || !empty($endDate))
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Transactions Table with Running Balance Column -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th class="text-center">Movement Qty</th>
                        <th class="text-center fw-bold text-primary">Running Balance</th>
                        <th>Reference Number</th>
                        <th>Authorized By</th>
                        <th>Notes / Context</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        @php
                            $isStockIn = in_array(strtolower($tx->type), ['stock_in', 'in', 'purchase', 'inbound']);
                            $isStockOut = in_array(strtolower($tx->type), ['stock_out', 'out', 'sale', 'outbound']);
                            $sign = $isStockIn ? '+' : ($isStockOut ? '-' : ($tx->quantity >= 0 ? '+' : ''));
                            $colorClass = $isStockIn ? 'text-success' : ($isStockOut ? 'text-danger' : 'text-warning');
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $tx->created_at->format('M d, Y') }}</strong>
                                <small class="text-muted d-block">{{ $tx->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $tx->type_badge_color }}">
                                    {{ $tx->type_display }}
                                </span>
                            </td>
                            <td class="text-center">
                                <strong class="{{ $colorClass }} fs-6">
                                    {{ $sign }}{{ abs($tx->quantity) }}
                                </strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border fs-6 px-3 py-1">
                                    {{ $tx->running_balance }}
                                </span>
                            </td>
                            <td>
                                <code>{{ $tx->reference_number ?? '—' }}</code>
                            </td>
                            <td>
                                <small class="text-muted">{{ $tx->user?->name ?? 'System' }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $tx->notes ?: '—' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-clock-history fs-3 d-block mb-1 text-secondary"></i>
                                No transactions recorded for this product in the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
