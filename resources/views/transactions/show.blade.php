@extends('layouts.app')

@section('title', 'Transaction #' . $transaction->id)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-receipt text-primary me-1"></i>Audit Trail
            </p>
            <h1 class="h2 fw-bold mb-0">Transaction Record #{{ $transaction->id }}</h1>
            <p class="text-muted mb-0">Recorded on {{ $transaction->created_at->format('F d, Y \a\t h:i:s A') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Transactions
            </a>
            @if(auth()->user()->isAdmin())
                <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Warning: Deleting this transaction will reverse its stock effect. Proceed?');">
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
                            @endphp
                            <strong class="{{ $colorClass }} fs-5">
                                {{ $sign }}{{ abs($transaction->quantity) }} units
                            </strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Unit Price</span>
                            <strong>{{ $transaction->unit_price ? '$' . number_format($transaction->unit_price, 2) : '—' }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Total Movement Value</span>
                            <strong class="text-success">
                                ${{ number_format(abs($transaction->quantity) * ($transaction->unit_price ?? $transaction->product?->unit_price ?? 0), 2) }}
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
                                <a href="{{ route('products.show', $transaction->product) }}" class="btn btn-sm btn-outline-primary">
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
                            <h5 class="mb-0 fw-bold"><i class="bi bi-person-check text-info me-2"></i>Authorized Agent</h5>
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
