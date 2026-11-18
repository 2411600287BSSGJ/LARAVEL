<div class="card shadow-sm border-0 h-100">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Automated Reorder Alerts</h6>
            <small class="text-muted">Stock at or below reorder threshold</small>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-danger">Reports</a>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush" id="widget-reorder-alerts-list">
            @forelse($reorderAlerts as $prod)
                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                    <div>
                        <a href="{{ route('products.show', $prod) }}" class="fw-semibold text-dark text-decoration-none d-block">
                            {{ $prod->name }}
                        </a>
                        <small class="text-muted font-monospace">{{ $prod->sku }} &bull; Stock: <strong class="text-danger">{{ $prod->quantity }}</strong> / Reorder: {{ $prod->reorder_level }}</small>
                    </div>
                    <a href="{{ route('purchase-orders.create', ['product_id' => $prod->id]) }}" class="btn btn-sm btn-outline-primary" title="Reorder product">
                        <i class="bi bi-cart-plus me-1"></i>Order
                    </a>
                </div>
            @empty
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                    All stock counts exceed reorder thresholds.
                </div>
            @endforelse
        </div>
    </div>
</div>
