@extends('layouts.app')

@section('title', 'Audit Record #' . $transaction->id)

@push('styles')
<style>
@media print {
    .navbar, aside, .sidebar-card, .btn, form, .no-print, #notificationToast, .dropdown {
        display: none !important;
    }
    body {
        background: #ffffff !important;
        color: #000000 !important;
    }
    .container-fluid {
        padding: 0 !important;
        margin: 0 !important;
    }
    main {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
    }
    .card {
        border: 1px solid #ccc !important;
        box-shadow: none !important;
        background: transparent !important;
        break-inside: avoid;
    }
    .voucher-print-header {
        display: block !important;
        text-align: center;
        border-bottom: 2px solid #333;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
}
.voucher-print-header {
    display: none;
}
</style>
@endpush

@section('content')
    <!-- Print-only voucher title -->
    <div class="voucher-print-header">
        <h2 class="fw-bold mb-1">🏋️ GYM INVENTORY MANAGEMENT SYSTEM</h2>
        <h4 class="text-uppercase text-secondary mb-1">Official Stock Movement Audit Voucher</h4>
        <p class="small text-muted mb-0">Record #{{ $transaction->id }} | Reference: {{ $transaction->reference_number ?: 'N/A' }} | Date: {{ $transaction->created_at ? $transaction->created_at->format('F d, Y h:i A') : '—' }}</p>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-journal-text text-primary me-1"></i>Audit Trail Detail
            </p>
            <h1 class="h2 fw-bold mb-0">Audit Record #{{ $transaction->id }}</h1>
            <p class="text-muted mb-0">Recorded on {{ $transaction->created_at ? $transaction->created_at->format('F d, Y \a\t h:i:s A') : '—' }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Audit Ledger
            </a>
            <!-- Print Slip -->
            <a href="{{ route('transactions.print.show', $transaction) }}" target="_blank" class="btn btn-outline-dark" title="Open printable voucher">
                <i class="bi bi-printer me-1"></i>Print Voucher
            </a>
            <!-- Edit Record -->
            <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-outline-primary" title="Edit this audit record">
                <i class="bi bi-pencil me-1"></i>Edit Record
            </a>
            <!-- Delete (Admin Only) -->
            @if(auth()->user()->isAdmin())
                <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Warning: Deleting this audit record will reverse its stock effect on inventory. Proceed?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>Delete & Reverse
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Transaction Movement Details -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-arrow-left-right text-primary me-2"></i>Movement Summary</h5>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Movement Type</span>
                            <span class="badge bg-{{ $transaction->type_badge_color }} fs-6">
                                {{ $transaction->type_display }}
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Quantity Moved</span>
                            @php
                                $isStockIn = in_array(strtolower($transaction->type), ['stock_in', 'in', 'inbound', 'purchase']);
                                $isStockOut = in_array(strtolower($transaction->type), ['stock_out', 'out', 'outbound', 'sale']);
                                $sign = $isStockIn ? '+' : ($isStockOut ? '-' : ($transaction->quantity >= 0 ? '+' : ''));
                                $colorClass = $isStockIn ? 'text-success' : ($isStockOut ? 'text-danger' : 'text-warning');
                                $unitPrice = $transaction->unit_price ?? $transaction->product?->unit_price ?? 0;
                                $totalValue = abs($transaction->quantity) * $unitPrice;
                            @endphp
                            <strong class="{{ $colorClass }} fs-5">
                                {{ $sign }}{{ abs($transaction->quantity) }} units
                            </strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Unit Price</span>
                            <strong>{{ $unitPrice ? '₱' . number_format($unitPrice, 2) : '—' }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Total Movement Value</span>
                            <strong class="text-success fs-5">
                                ₱{{ number_format($totalValue, 2) }}
                            </strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Reference Number</span>
                            <code>{{ $transaction->reference_number ?: 'N/A' }}</code>
                        </div>
                        <div class="list-group-item px-0 pt-3">
                            <span class="text-muted d-block mb-1">Notes / Reason</span>
                            <p class="mb-0 bg-light p-2 rounded small text-secondary">
                                {{ $transaction->notes ?: 'No additional notes provided for this transaction.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product & Authorization Details -->
        <div class="col-lg-6">
            <div class="row g-4">
                <!-- Related Product Card -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-box-seam text-success me-2"></i>Product Involved</h5>
                            @if($transaction->product)
                                <a href="{{ route('products.show', $transaction->product) }}" class="btn btn-sm btn-outline-primary no-print">
                                    View Product Details
                                </a>
                            @endif
                        </div>
                        <div class="card-body p-4">
                            @if($transaction->product)
                                <h5 class="fw-bold text-dark">{{ $transaction->product->name }}</h5>
                                <div class="row g-2 mt-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">SKU Code</small>
                                        <strong class="font-monospace">{{ $transaction->product->sku }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Category</small>
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $transaction->product->category_name }}</span>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="text-muted d-block">Current Stock on Hand</small>
                                        <strong class="text-{{ $transaction->product->stock_status_color }}">{{ $transaction->product->quantity }} units</strong>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="text-muted d-block">Reorder Level</small>
                                        <strong>{{ $transaction->product->reorder_level }} units</strong>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted fst-italic">
                                    Product associated with this transaction has been archived or deleted.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- User / Authorization Card -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-person-check text-info me-2"></i>Encoded By</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary text-white rounded-circle p-3 fs-4">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">{{ $transaction->user?->name ?? 'System Automated' }}</h6>
                                    <p class="text-muted small mb-1">{{ $transaction->user?->email ?? 'N/A' }}</p>
                                    <span class="badge bg-secondary text-uppercase">{{ $transaction->user?->role ?? 'Staff' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
