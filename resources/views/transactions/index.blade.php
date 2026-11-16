@extends('layouts.app')

@section('title', 'Inventory Transactions')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-arrow-left-right text-primary me-1"></i>Stock Movement Ledger
            </p>
            <h1 class="h2 fw-bold mb-1">Inventory Transactions</h1>
            <p class="text-muted mb-0">Audit trail of all inbound stock receipts, outbound dispatches, and inventory count adjustments.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-success" href="{{ route('reports.export.stock-movement') }}">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
            </a>
            <a class="btn btn-outline-secondary" href="{{ route('adjustments.create') }}">
                <i class="bi bi-sliders me-1"></i>Stock Adjustment
            </a>
            <a class="btn btn-primary" href="{{ route('transactions.create') }}">
                <i class="bi bi-plus-lg me-1"></i>Record Transaction
            </a>
        </div>
    </div>

    <!-- Filter Form Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('transactions.index') }}" class="row g-2 align-items-end">
                <!-- Filter by Product -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Filter by Product</label>
                    <select class="form-select form-select-sm" name="product_id">
                        <option value="">All Products</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>
                                {{ $p->name }} ({{ $p->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter by Type -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Transaction Type</label>
                    <select class="form-select form-select-sm" name="type">
                        <option value="">All Movement Types</option>
                        <option value="stock_in" @selected(request('type') === 'stock_in')>Stock In (Replenishment)</option>
                        <option value="stock_out" @selected(request('type') === 'stock_out')>Stock Out (Usage / Sale)</option>
                        <option value="adjustment" @selected(request('type') === 'adjustment')>Adjustment (Audit / Count)</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Start Date</label>
                    <input type="date" class="form-control form-control-sm" name="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">End Date</label>
                    <input type="date" class="form-control form-control-sm" name="end_date" value="{{ request('end_date') }}">
                </div>

                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table Card -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th style="width: 25%;">Product Name</th>
                        <th>Type</th>
                        <th class="text-center">Quantity</th>
                        <th>Reference Number</th>
                        <th>Authorized By</th>
                        <th>Notes</th>
                        <th class="text-end" style="width: 12%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        @php
                            $isStockIn = in_array(strtolower($tx->type), ['stock_in', 'in', 'inbound', 'purchase']);
                            $isStockOut = in_array(strtolower($tx->type), ['stock_out', 'out', 'outbound', 'sale']);
                            $sign = $isStockIn ? '+' : ($isStockOut ? '-' : ($tx->quantity >= 0 ? '+' : ''));
                            $colorClass = $isStockIn ? 'text-success' : ($isStockOut ? 'text-danger' : 'text-warning');
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $tx->created_at->format('M d, Y') }}</strong>
                                <small class="text-muted d-block">{{ $tx->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                @if($tx->product)
                                    <a href="{{ route('products.show', $tx->product) }}" class="fw-bold text-decoration-none text-dark d-block">
                                        {{ $tx->product->name }}
                                    </a>
                                    <small class="text-muted font-monospace">{{ $tx->product->sku }}</small>
                                @else
                                    <span class="text-muted fst-italic">Archived Product</span>
                                @endif
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
                            <td>
                                <code>{{ $tx->reference_number ?? '—' }}</code>
                            </td>
                            <td>
                                <small class="text-muted">{{ $tx->user?->name ?? 'System' }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ Str::limit($tx->notes ?? '—', 35) }}</small>
                            </td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-info" href="{{ route('transactions.show', $tx) }}" title="View Transaction Details">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                @if(auth()->user()->isAdmin())
                                    <form class="d-inline" method="POST" action="{{ route('transactions.destroy', $tx) }}" onsubmit="return confirm('Warning: Deleting this transaction will reverse its effect on product inventory. Proceed?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete & Reverse Stock">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-arrow-left-right fs-1 d-block mb-2 text-secondary"></i>
                                <h5>No Transactions Found</h5>
                                <p class="small mb-3">No inventory transactions matched your selected filters.</p>
                                <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">Record New Transaction</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted">
                    Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} movements
                </span>
                <div>
                    {{ $transactions->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
