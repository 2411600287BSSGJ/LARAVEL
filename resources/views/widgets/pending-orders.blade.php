<div class="card shadow-sm border-0 h-100">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-hourglass-split text-warning me-2"></i>Pending Orders Awaiting Approval</h6>
            <small class="text-muted">Procurement requests awaiting review</small>
        </div>
        <span class="badge bg-warning text-dark fs-6">{{ $pendingOrdersCount }}</span>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush" id="widget-pending-orders-list">
            @forelse($pendingOrders as $po)
                <a href="{{ route('purchase-orders.show', $po) }}" class="list-group-item list-group-item-action py-3 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-dark d-block font-monospace">{{ $po->reference }}</strong>
                            <small class="text-secondary">{{ $po->items->map(fn ($item) => $item->product?->name)->filter()->join(', ') }} ({{ $po->items->sum('quantity') }} units)</small>
                        </div>
                        <span class="badge bg-warning-subtle text-warning border">Pending</span>
                    </div>
                </a>
            @empty
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                    No pending orders awaiting review.
                </div>
            @endforelse
        </div>
    </div>
</div>
