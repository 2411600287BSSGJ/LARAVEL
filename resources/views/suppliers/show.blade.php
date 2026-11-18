@extends('layouts.app')

@section('title', $supplier->name . ' — Supplier Profile')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-truck text-primary me-1"></i>Vendor Profile
            </p>
            <h1 class="h2 fw-bold mb-0">{{ $supplier->name }}</h1>
            <small class="text-muted">Registered supplier partner for gym equipment and consumables.</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('suppliers.index') }}">
                <i class="bi bi-arrow-left me-1"></i>Back to Suppliers
            </a>
            @can('create', \App\Models\PurchaseOrder::class)
                <a class="btn btn-outline-success" href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}">
                    <i class="bi bi-cart-plus me-1"></i>New Purchase Order
                </a>
            @endcan
            @can('update', $supplier)
                <a class="btn btn-primary" href="{{ route('suppliers.edit', $supplier) }}">
                    <i class="bi bi-pencil me-1"></i>Edit Supplier
                </a>
            @endcan
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <small class="text-muted text-uppercase fw-semibold d-block">Expected Lead Time</small>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="fs-2 fw-bold text-dark">{{ $supplier->lead_time_days }}</span>
                        <span class="text-muted">days</span>
                    </div>
                    <small class="text-muted">Standard turnaround</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <small class="text-muted text-uppercase fw-semibold d-block">Fulfillment Rate</small>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="fs-2 fw-bold text-success">{{ number_format($performance, 1) }}%</span>
                    </div>
                    <small class="text-muted">Orders successfully received</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <small class="text-muted text-uppercase fw-semibold d-block">Products Supplied</small>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="fs-2 fw-bold text-primary">{{ $supplier->products->count() }}</span>
                        <span class="text-muted">items</span>
                    </div>
                    <small class="text-muted">In product catalog</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <small class="text-muted text-uppercase fw-semibold d-block">Total Purchase Orders</small>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="fs-2 fw-bold text-secondary">{{ $supplier->purchaseOrders()->count() }}</span>
                        <span class="text-muted">orders</span>
                    </div>
                    <small class="text-muted">Lifetime transactions</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Supplier Contact Information -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill text-primary me-2"></i>Contact Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold d-block">Primary Contact</small>
                        <strong class="fs-6">{{ $supplier->contact_person ?: 'No specific contact person named' }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold d-block">Email Address</small>
                        @if($supplier->email)
                            <a href="mailto:{{ $supplier->email }}" class="text-decoration-none">
                                <i class="bi bi-envelope me-1"></i>{{ $supplier->email }}
                            </a>
                        @else
                            <span class="text-muted">Not specified</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold d-block">Phone Number</small>
                        <span>{{ $supplier->phone ?: 'Not specified' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold d-block">Physical / Warehouse Address</small>
                        <p class="text-secondary small mb-0">{{ $supplier->address ?: 'No address specified' }}</p>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold d-block">Payment Terms</small>
                        <span class="badge bg-light text-dark border">{{ $supplier->payment_terms ?? 'Net 30' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catalog Products Supplied -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-boxes text-danger me-2"></i>Products Sourced From This Supplier</h5>
                    <span class="badge bg-secondary">{{ $supplier->products->count() }} catalog items</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th class="text-center">Stock</th>
                                <th class="text-end">Unit Price (₱)</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supplier->products as $prod)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $prod) }}" class="fw-semibold text-decoration-none text-dark d-block">
                                            {{ $prod->name }}
                                        </a>
                                        <small class="text-muted font-monospace">{{ $prod->sku }}</small>
                                    </td>
                                    <td><span class="badge bg-light text-secondary border">{{ $prod->category_name }}</span></td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $prod->stock_status_color }}">{{ $prod->quantity }} units</span>
                                    </td>
                                    <td class="text-end fw-semibold">₱{{ number_format($prod->unit_price, 2) }}</td>
                                    <td class="text-end">
                                        @can('create', \App\Models\PurchaseOrder::class)
                                            <a href="{{ route('purchase-orders.create', ['product_id' => $prod->id, 'supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-outline-primary" title="Reorder product">
                                                <i class="bi bi-cart-plus"></i> Order
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No products currently assigned to this supplier.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Orders History -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-receipt-cutoff text-success me-2"></i>Purchase Order History</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Reference</th>
                        <th>Product</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Status</th>
                        <th>Order Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                        <tr>
                            <td><code>{{ $po->reference }}</code></td>
                            <td>{{ $po->product?->name ?? 'Archived Product' }}</td>
                            <td class="text-center fw-bold">{{ $po->quantity }}</td>
                            <td class="text-center">
                                <span class="badge {{ $po->status === 'received' ? 'bg-success' : ($po->status === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') }} text-uppercase">
                                    {{ $po->status }}
                                </span>
                            </td>
                            <td>{{ $po->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-info">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No purchase orders recorded with this supplier.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>
@endsection
