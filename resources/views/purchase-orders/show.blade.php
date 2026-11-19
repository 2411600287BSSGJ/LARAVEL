@extends('layouts.app')

@section('title', 'Purchase Order ' . $purchaseOrder->reference)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-cart-check text-primary me-1"></i>Procurement Order
            </p>
            <h1 class="h2 fw-bold mb-0 font-monospace">{{ $purchaseOrder->reference }}</h1>
            <small class="text-muted">Generated on {{ $purchaseOrder->created_at->format('F d, Y \a\t h:i A') }}</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}">
                <i class="bi bi-arrow-left me-1"></i>Back to Orders
            </a>
            <!-- Print Order -->
            <a class="btn btn-outline-dark" href="{{ route('purchase-orders.print', $purchaseOrder) }}" target="_blank">
                <i class="bi bi-printer me-1"></i>Print Order
            </a>

            <!-- Approve Button -->
            @can('approve', $purchaseOrder)
                @if($purchaseOrder->status === 'pending')
                    <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i>Approve Order
                        </button>
                    </form>
                @endif
            @endcan

            <!-- Send to Supplier Button -->
            @if(in_array($purchaseOrder->status, ['pending', 'approved']) && auth()->user()->isAdmin())
                <form method="POST" action="{{ route('purchase-orders.send', $purchaseOrder) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-send me-1"></i>Mark as Sent
                    </button>
                </form>
            @endif

            <!-- Cancel Button -->
            @if(in_array($purchaseOrder->status, ['pending', 'draft']) && auth()->user()->isAdmin())
                <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" onsubmit="return confirm('Cancel this purchase order?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle me-1"></i>Cancel Order
                    </button>
                </form>
            @endif

            <!-- Receive Button -->
            @can('receive', $purchaseOrder)
                <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}" onsubmit="return confirm('Receive this purchase order? This will immediately add {{ $purchaseOrder->items->sum('quantity') }} units to inventory.');">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-box-seam me-1"></i>Mark Received &amp; Add Stock
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <!-- Status Banner -->
    @php
        $statusConfig = match($purchaseOrder->status) {
            'received' => ['class' => 'success', 'icon' => 'check-circle-fill', 'title' => 'Order Received & Fulfilled', 'desc' => 'Stock was added to inventory on ' . ($purchaseOrder->received_at ? $purchaseOrder->received_at->format('F d, Y \a\t h:i A') : '—')],
            'pending' => ['class' => 'warning', 'icon' => 'hourglass-split', 'title' => 'Awaiting Delivery / In Transit', 'desc' => 'This purchase order is pending delivery from the supplier. An administrator can mark it as received once delivered.'],
            'cancelled' => ['class' => 'danger', 'icon' => 'x-octagon-fill', 'title' => 'Purchase Order Cancelled', 'desc' => 'This procurement order was cancelled. No inventory stock was affected.'],
            default => ['class' => 'secondary', 'icon' => 'info-circle', 'title' => 'Status: ' . ucfirst($purchaseOrder->status), 'desc' => ''],
        };
    @endphp

    <div class="alert alert-{{ $statusConfig['class'] }} d-flex align-items-center gap-3 mb-4 shadow-sm border-0">
        <i class="bi bi-{{ $statusConfig['icon'] }} fs-2"></i>
        <div>
            <h5 class="mb-0 fw-bold">{{ $statusConfig['title'] }}</h5>
            <small class="mb-0 d-block">{{ $statusConfig['desc'] }}</small>
        </div>
    </div>

    @php
        $totalCost = $purchaseOrder->calculateTotal();
    @endphp

    <div class="row g-4">
        <!-- Order & Financial Breakdown -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-calculator text-primary me-2"></i>Procurement Financials</h5>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">PO Reference Number</span>
                            <code class="fs-6">{{ $purchaseOrder->reference }}</code>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Products Ordered</span>
                            <strong class="fs-5 text-dark">{{ $purchaseOrder->items->count() }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Total Quantity</span>
                            <strong>{{ $purchaseOrder->items->sum('quantity') }} units</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Payment Terms</span>
                            <strong>{{ $purchaseOrder->supplier?->payment_terms ?? 'Not specified' }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Estimated Payment</span>
                            <strong class="text-success fs-4">₱{{ number_format($totalCost, 2) }}</strong>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Created By</span>
                            <span>{{ $purchaseOrder->creator?->name ?? 'Automated Reorder Service' }}</span>
                        </div>
                        <div class="list-group-item px-0 pt-3">
                            <span class="text-muted d-block mb-1">Notes / Instructions</span>
                            <div class="p-3 bg-light rounded text-secondary small">
                                {{ $purchaseOrder->notes ?: 'No additional delivery instructions specified.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product & Supplier Details -->
        <div class="col-lg-6">
            <div class="row g-4">
                <!-- Product Card -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-box-seam text-danger me-2"></i>Products to Restock</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead><tr><th>Product</th><th>SKU</th><th class="text-end">Qty</th><th class="text-end">Unit Cost</th><th class="text-end">Line Total</th></tr></thead>
                                    <tbody>
                                        @foreach($purchaseOrder->items as $item)
                                            <tr>
                                                <td>
                                                    @if($item->product)
                                                        <a href="{{ route('products.show', $item->product) }}" class="fw-semibold text-decoration-none">{{ $item->product->name }}</a>
                                                    @else
                                                        <span class="text-muted fst-italic">Archived product</span>
                                                    @endif
                                                </td>
                                                <td class="font-monospace">{{ $item->product?->sku ?? '—' }}</td>
                                                <td class="text-end">{{ $item->quantity }}</td>
                                                <td class="text-end">₱{{ number_format($item->unit_price, 2) }}</td>
                                                <td class="text-end fw-semibold">₱{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supplier Card -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-truck text-info me-2"></i>Supplier Details</h5>
                            @if($purchaseOrder->supplier)
                                <a href="{{ route('suppliers.show', $purchaseOrder->supplier) }}" class="btn btn-sm btn-outline-info">
                                    Supplier Profile
                                </a>
                            @endif
                        </div>
                        <div class="card-body p-4">
                            @if($purchaseOrder->supplier)
                                <h6 class="fw-bold mb-1">{{ $purchaseOrder->supplier->name }}</h6>
                                <p class="text-muted small mb-2">Contact: {{ $purchaseOrder->supplier->contact_person ?: 'N/A' }}</p>
                                <div class="row g-2 small">
                                    <div class="col-6">
                                        <span class="text-muted d-block">Email:</span>
                                        <strong>{{ $purchaseOrder->supplier->email ?: 'N/A' }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted d-block">Phone:</span>
                                        <strong>{{ $purchaseOrder->supplier->phone ?: 'N/A' }}</strong>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <span class="text-muted d-block">Expected Lead Time:</span>
                                        <span class="badge bg-light text-dark border">{{ $purchaseOrder->supplier->lead_time_days }} business days</span>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted fst-italic">No supplier assigned to this order.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
