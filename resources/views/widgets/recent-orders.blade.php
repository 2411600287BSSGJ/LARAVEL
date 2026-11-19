<div class="card shadow-sm border-0 h-100">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-receipt text-primary me-2"></i>Recent Procurement Orders</h6>
            <small class="text-muted">Latest vendor purchase orders</small>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush" id="widget-recent-orders-list">
            @forelse($recentOrders as $ro)
                <a href="{{ route('purchase-orders.show', $ro) }}" class="list-group-item list-group-item-action py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-semibold text-dark font-monospace">{{ $ro->reference }}</span>
                            <small class="text-muted d-block">{{ $ro->supplier?->name ?? 'Vendor' }} &bull; {{ $ro->items->sum('quantity') }} units</small>
                        </div>
                        <span class="badge {{ $ro->status === 'received' ? 'bg-success' : ($ro->status === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') }} text-uppercase" style="font-size: 0.7rem;">
                            {{ $ro->status }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="p-4 text-center text-muted small">
                    No recent purchase orders.
                </div>
            @endforelse
        </div>
    </div>
</div>
