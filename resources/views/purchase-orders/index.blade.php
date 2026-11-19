@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-cart-check text-primary me-1"></i>Procurement & Supply Chain
            </p>
            <h1 class="h2 fw-bold mb-1">Purchase Orders</h1>
            <p class="text-muted mb-0">Manage replenishments, track pending orders from suppliers, and receive inbound inventory.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-success" href="{{ route('exports.download', 'purchase-orders') }}">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
            </a>
            @can('create', \App\Models\PurchaseOrder::class)
                <a class="btn btn-primary" href="{{ route('purchase-orders.create') }}">
                    <i class="bi bi-plus-lg me-1"></i>New Purchase Order
                </a>
            @endcan
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('purchase-orders.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Search PO / Product</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by PO reference or product...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Order Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending (Awaiting Delivery)</option>
                        <option value="received" @selected(request('status') === 'received')>Received (Stock Added)</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Supplier</label>
                    <select class="form-select" name="supplier_id">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" @selected(request('supplier_id') == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-primary flex-fill" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Reference #</th>
                        <th style="width: 25%;">Product Ordered</th>
                        <th>Supplier</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Est. Value (₱)</th>
                        <th class="text-center">Status</th>
                        <th>Order Date</th>
                        <th>Created By</th>
                        <th class="text-end" style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $order)
                        @php
                            $statusBadge = match($order->status) {
                                'received' => 'success',
                                'pending' => 'warning',
                                'cancelled' => 'danger',
                                default => 'secondary',
                            };
                            $totalVal = $order->calculateTotal();
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('purchase-orders.show', $order) }}" class="fw-bold font-monospace text-decoration-none">
                                    {{ $order->reference }}
                                </a>
                            </td>
                            <td>
                                @forelse($order->items as $item)
                                    @if($item->product)
                                        <a href="{{ route('products.show', $item->product) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                            {{ $item->product->name }}
                                        </a>
                                        <small class="text-muted font-monospace">{{ $item->product->sku }} · {{ $item->quantity }} units</small>
                                    @else
                                        <span class="text-muted fst-italic d-block">Archived Product</span>
                                    @endif
                                @empty
                                    <span class="text-muted fst-italic">No items</span>
                                @endforelse
                            </td>
                            <td>
                                @if($order->supplier)
                                    <a href="{{ route('suppliers.show', $order->supplier) }}" class="text-decoration-none text-secondary">
                                        {{ $order->supplier->name }}
                                    </a>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold fs-6">
                                {{ $order->items->sum('quantity') }} <small class="text-muted fw-normal">units</small>
                            </td>
                            <td class="text-end fw-semibold">
                                ₱{{ number_format($totalVal, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $statusBadge }} text-uppercase" style="font-size: 0.75rem;">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted d-block">{{ $order->created_at->format('M d, Y') }}</small>
                                <small class="text-secondary" style="font-size: 0.75rem;">{{ $order->created_at->diffForHumans() }}</small>
                            </td>
                            <td>{{ $order->creator?->name ?? 'Automated Reorder' }}</td>
                            <td class="text-end text-nowrap">
                                @can('approve', $order)
                                    @if(in_array($order->status, ['draft', 'pending']))
                                        <form class="d-inline" method="POST" action="{{ route('purchase-orders.approve', $order) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-primary" type="submit">
                                                <i class="bi bi-check2-circle me-1"></i>Approve
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                                <a class="btn btn-sm btn-outline-info" href="{{ route('purchase-orders.show', $order) }}" title="View Order Details">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('purchase-orders.print', $order) }}" target="_blank" title="Print PO">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @can('receive', $order)
                                    <form class="d-inline" method="POST" action="{{ route('purchase-orders.receive', $order) }}" onsubmit="return confirm('Mark this purchase order as received and add {{ $order->items->sum('quantity') }} units to inventory?');">
                                        @csrf
                                        <button class="btn btn-sm btn-success" type="submit" title="Receive & Add Stock">
                                            <i class="bi bi-box-seam me-1"></i>Receive
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary"></i>
                                <h5>No Purchase Orders Found</h5>
                                <p class="small mb-3">No orders matched the selected filter criteria.</p>
                                @can('create', \App\Models\PurchaseOrder::class)
                                    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary btn-sm">Create New Purchase Order</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted">
                    Showing {{ $purchaseOrders->firstItem() }} to {{ $purchaseOrders->lastItem() }} of {{ $purchaseOrders->total() }} purchase orders
                </span>
                <div>
                    {{ $purchaseOrders->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
