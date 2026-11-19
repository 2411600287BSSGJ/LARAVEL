@extends('layouts.app')

@section('title', 'Sales Order #' . $salesOrder->order_number)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left me-1"></i>Back to Sales Orders
            </a>
            <div class="d-flex align-items-center gap-2">
                <h1 class="h2 fw-bold mb-0">Sales Order {{ $salesOrder->order_number }}</h1>
                <span class="badge bg-{{ $salesOrder->status_badge_color }} text-uppercase px-3 py-2">
                    {{ ucfirst($salesOrder->status) }}
                </span>
            </div>
            <p class="text-muted mb-0">Placed on {{ $salesOrder->created_at->format('F d, Y \a\t h:i A') }} by {{ $salesOrder->creator?->name ?? 'System' }}</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('sales-orders.print', $salesOrder) }}" target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>Print Invoice
            </a>

            @if($salesOrder->status === 'pending' && auth()->user()->isAdmin())
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle me-1"></i>Reject Order
                </button>

                <form method="POST" action="{{ route('sales-orders.approve', $salesOrder) }}" onsubmit="return confirm('Approve sales order [{{ $salesOrder->order_number }}]?\nThis will deduct {{ $salesOrder->quantity }} units from live inventory.');">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Approve & Deduct Stock
                    </button>
                </form>
            @endif

            @if($salesOrder->status === 'pending' && (auth()->user()->isAdmin() || $salesOrder->created_by === auth()->id()))
                <form method="POST" action="{{ route('sales-orders.cancel', $salesOrder) }}" onsubmit="return confirm('Cancel this sales order?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-dark">
                        <i class="bi bi-slash-circle me-1"></i>Cancel Order
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger shadow-sm border-0 alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($salesOrder->status === 'rejected')
        <div class="alert alert-danger shadow-sm border-0 mb-4">
            <h6 class="fw-bold mb-1"><i class="bi bi-x-octagon-fill me-2"></i>Order Rejected by Administrator</h6>
            <p class="mb-0">{{ $salesOrder->rejection_reason ?? 'No detailed rejection reason provided.' }}</p>
        </div>
    @endif

    <div class="row g-4">
        <!-- Customer & Order Information -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-person-fill text-primary me-2"></i>Customer Information</h5>
                </div>
                <div class="card-body pt-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                            <span class="text-muted">Customer Name:</span>
                            <span class="fw-semibold text-dark">{{ $salesOrder->customer_name }}</span>
                        </li>
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                            <span class="text-muted">Phone:</span>
                            <span>{{ $salesOrder->customer_phone ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                            <span class="text-muted">Email:</span>
                            <span>{{ $salesOrder->customer_email ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                            <span class="text-muted">Created By:</span>
                            <span class="badge bg-light text-dark border">{{ $salesOrder->creator?->name ?? 'System' }} ({{ ucfirst($salesOrder->creator?->role ?? 'Staff') }})</span>
                        </li>
                        @if($salesOrder->approved_by)
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                <span class="text-muted">Approved By:</span>
                                <span class="badge bg-success-subtle text-success border border-success">{{ $salesOrder->approvedBy?->name ?? 'Admin' }}</span>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                <span class="text-muted">Approved At:</span>
                                <span>{{ $salesOrder->approved_at?->format('M d, Y h:i A') }}</span>
                            </li>
                        @endif
                    </ul>

                    @if($salesOrder->notes)
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="small fw-bold text-muted mb-1">Notes / Instructions:</div>
                            <div class="small text-secondary">{{ $salesOrder->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ordered Items & Financials -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-box-seam-fill text-success me-2"></i>Ordered Item Summary</h5>
                    <span class="badge bg-light text-dark border">1 Item</span>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Details</th>
                                    <th class="text-center">Current Live Stock</th>
                                    <th class="text-center">Qty Ordered</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($salesOrder->product)
                                    <tr>
                                        <td>
                                            <a href="{{ route('products.show', $salesOrder->product) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $salesOrder->product->name }}
                                            </a>
                                            <div class="small text-muted">
                                                SKU: <span class="badge bg-light text-secondary border">{{ $salesOrder->product->sku }}</span>
                                                | Category: {{ $salesOrder->product->category_name }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($salesOrder->product->quantity <= 0)
                                                <span class="badge bg-danger">0 (Out of Stock)</span>
                                            @else
                                                <span class="badge bg-info text-dark">{{ $salesOrder->product->quantity }} available</span>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold fs-5">{{ $salesOrder->quantity }}</td>
                                        <td class="text-end">₱{{ number_format($salesOrder->unit_price, 2) }}</td>
                                        <td class="text-end fw-bold text-success fs-5">₱{{ number_format($salesOrder->total_amount, 2) }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="5" class="text-muted text-center py-4">Associated product record was removed from inventory.</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Grand Total (PHP):</td>
                                    <td class="text-end fw-bold fs-4 text-success">₱{{ number_format($salesOrder->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($salesOrder->status === 'pending')
                        <div class="alert alert-warning border-0 d-flex align-items-center mt-3">
                            <i class="bi bi-shield-exclamation fs-3 me-3 text-warning"></i>
                            <div>
                                <strong class="d-block">Approval Required</strong>
                                <span class="small">This sales order has not yet been processed or deducted from stock. Only an administrator can officially approve and execute the inventory dispatch.</span>
                            </div>
                        </div>
                    @elseif($salesOrder->status === 'approved')
                        <div class="alert alert-success border-0 d-flex align-items-center mt-3">
                            <i class="bi bi-shield-check fs-3 me-3 text-success"></i>
                            <div>
                                <strong class="d-block">Order Approved & Inventory Dispatched</strong>
                                <span class="small">Stock deduction was executed and recorded in the audit trail ledger with reference code <code>{{ $salesOrder->order_number }}</code>.</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($salesOrder->status === 'pending' && auth()->user()->isAdmin())
        <!-- Rejection Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('sales-orders.reject', $salesOrder) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Reject Sales Order #{{ $salesOrder->order_number }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small">State the reason for rejecting this order. The encoding staff member will be alerted.</p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" class="form-control" rows="3" placeholder="e.g. Member withdrew request, out of stock, payment not received..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
