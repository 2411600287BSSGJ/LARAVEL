@extends('layouts.app')
@section('title', 'Purchase Orders')
@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><p class="eyebrow mb-1">Procurement</p><h1 class="fw-bold mb-0">Purchase Orders</h1></div><a class="btn btn-outline-success" href="{{ route('exports.download', 'purchase-orders') }}">Export CSV</a></div>
    <div class="panel table-responsive"><table class="table inventory-table align-middle mb-0"><thead><tr><th>Reference</th><th>Product</th><th>Supplier</th><th>Quantity</th><th>Status</th><th>Created</th><th></th></tr></thead><tbody>
        @forelse($purchaseOrders as $order)<tr><td><strong>{{ $order->reference }}</strong></td><td>{{ $order->product?->name ?? 'Archived product' }}</td><td>{{ $order->supplier?->name ?? 'Unassigned' }}</td><td>{{ $order->quantity }}</td><td><span class="badge {{ $order->status === 'received' ? 'text-bg-success' : 'text-bg-warning' }}">{{ ucfirst($order->status) }}</span></td><td>{{ $order->created_at->format('M j, Y') }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('purchase-orders.show', $order) }}">View</a></td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-4">No purchase orders.</td></tr>@endforelse
    </tbody></table><div class="mt-3">{{ $purchaseOrders->links() }}</div></div>
@endsection
