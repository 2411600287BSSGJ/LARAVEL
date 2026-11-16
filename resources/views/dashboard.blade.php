@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Gym inventory / Overview</p>
            <h1 class="fw-bold mb-1">Inventory Dashboard</h1>
            <p class="text-muted mb-0">Monitor stock, value, and purchasing activity.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('transactions.create') }}"><i class="bi bi-plus-lg me-1"></i>Record Transaction</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="metric-card"><span class="metric-label">Total Products</span><strong>{{ number_format($totalProducts) }}</strong><span class="metric-meta">Active SKUs</span></div></div>
        <div class="col-sm-6 col-xl-3"><div class="metric-card metric-warning"><span class="metric-label">Low Stock</span><strong>{{ number_format($lowStock) }}</strong><span class="metric-meta">At or below reorder level</span></div></div>
        <div class="col-sm-6 col-xl-3"><div class="metric-card metric-danger"><span class="metric-label">Out of Stock</span><strong>{{ number_format($outOfStock) }}</strong><span class="metric-meta">No units available</span></div></div>
        <div class="col-sm-6 col-xl-3"><div class="metric-card"><span class="metric-label">Total Inventory Value</span><strong>${{ number_format((float) $inventoryValue, 2) }}</strong><span class="metric-meta">Quantity × unit price</span></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <section class="panel h-100">
                <div class="panel-heading d-flex justify-content-between align-items-center">
                    <div><p class="eyebrow mb-1">Stock control</p><h2>Recent products</h2></div>
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-primary">All products</a>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table inventory-table mb-0">
                        <thead><tr><th>Product / SKU</th><th>Brand</th><th>Category</th><th>Qty</th><th>Reorder</th></tr></thead>
                        <tbody>
                            @forelse ($recentProducts as $product)
                                <tr>
                                    <td><strong>{{ $product->name }}</strong><div class="small text-muted">{{ $product->sku }}</div></td>
                                    <td>{{ $product->brand?->name ?? '—' }}</td>
                                    <td>{{ $product->category?->name ?? '—' }}</td>
                                    <td>{{ $product->quantity }}</td>
                                    <td>{{ $product->reorder_level }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No products in inventory yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="panel h-100">
                <div class="panel-heading d-flex justify-content-between align-items-center">
                    <div><p class="eyebrow mb-1">Procurement</p><h2>Pending purchase orders</h2></div>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table inventory-table mb-0">
                        <thead><tr><th>Order</th><th>Product</th><th>Qty</th></tr></thead>
                        <tbody>
                            @forelse ($pendingOrders as $order)
                                <tr>
                                    <td><a href="{{ route('purchase-orders.show', $order) }}">{{ $order->reference }}</a></td>
                                    <td>{{ $order->product?->name ?? 'Archived product' }}</td>
                                    <td>{{ $order->quantity }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">No pending orders.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <section class="panel">
        <div class="panel-heading d-flex justify-content-between align-items-center">
            <div><p class="eyebrow mb-1">Stock movement</p><h2>Recent transactions</h2></div>
            <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary">All transactions</a>
        </div>
        <div class="table-responsive mt-3">
            <table class="table inventory-table mb-0">
                <thead><tr><th>Date</th><th>Product</th><th>Type</th><th>Quantity</th><th>Recorded by</th><th>Reference</th></tr></thead>
                <tbody>
                    @forelse ($recentTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('M j, Y H:i') }}</td>
                            <td>{{ $transaction->product?->name ?? 'Archived product' }}</td>
                            <td><span class="badge {{ $transaction->type === 'inbound' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ ucfirst($transaction->type) }}</span></td>
                            <td>{{ $transaction->quantity }}</td>
                            <td>{{ $transaction->user?->name ?? 'System' }}</td>
                            <td>{{ $transaction->reference ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No stock movements recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
