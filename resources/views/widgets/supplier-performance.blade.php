<div class="card shadow-sm border-0 h-100">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-trophy text-success me-2"></i>Top Supplier Fulfillment</h6>
            <small class="text-muted">On-time and completed delivery ratings</small>
        </div>
        <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-outline-success">Suppliers</a>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush" id="widget-supplier-performance-list">
            @forelse($topSuppliers as $sup)
                <a href="{{ route('suppliers.show', $sup) }}" class="list-group-item list-group-item-action py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-dark d-block">{{ $sup->name }}</strong>
                            <small class="text-muted">Lead time: {{ $sup->lead_time_days }} days &bull; {{ $sup->purchase_orders_count }} orders</small>
                        </div>
                        <span class="badge {{ $sup->performance_score >= 80 ? 'bg-success' : ($sup->performance_score >= 50 ? 'bg-warning text-dark' : 'bg-secondary') }} fs-6">
                            {{ number_format($sup->performance_score, 1) }}%
                        </span>
                    </div>
                </a>
            @empty
                <div class="p-4 text-center text-muted small">
                    No supplier performance metrics available yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
