@extends('layouts.app')

@section('title', 'Sales Orders')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-cart-check text-success me-1"></i>Sales & Dispatch
            </p>
            <h1 class="h2 fw-bold mb-1">Sales Orders</h1>
            <p class="text-muted mb-0">Record member and customer purchases. All sales orders require administrator approval before inventory deduction.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-primary" href="{{ route('sales-orders.create') }}">
                <i class="bi bi-plus-lg me-1"></i>New Sales Order
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3">
                        <i class="bi bi-receipt fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Orders</div>
                        <div class="h4 fw-bold mb-0">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 me-3">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Awaiting Approval</div>
                        <div class="h4 fw-bold mb-0 text-warning">{{ $stats['pending'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Approved & Fulfilled</div>
                        <div class="h4 fw-bold mb-0 text-success">{{ $stats['approved'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 me-3">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Approved Revenue</div>
                        <div class="h4 fw-bold mb-0 text-info">₱{{ number_format($stats['revenue'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('sales-orders.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Search Order / Customer / SKU</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by order #, customer, or product...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Order Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending (Awaiting Approval)</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approved (Stock Deducted)</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Product</label>
                    <select class="form-select" name="product_id">
                        <option value="">All Products</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-primary flex-fill" type="submit">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a class="btn btn-outline-secondary" href="{{ route('sales-orders.index') }}" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Orders Table -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th style="width: 25%;">Product Ordered</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Unit Price (₱)</th>
                        <th class="text-end">Total (₱)</th>
                        <th class="text-center">Status</th>
                        <th>Date</th>
                        <th>Created By</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesOrders as $so)
                        <tr>
                            <td>
                                <a href="{{ route('sales-orders.show', $so) }}" class="fw-bold text-decoration-none">
                                    {{ $so->order_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $so->customer_name }}</div>
                                @if($so->customer_phone)
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $so->customer_phone }}</small>
                                @endif
                            </td>
                            <td>
                                @if($so->product)
                                    <a href="{{ route('products.show', $so->product) }}" class="text-dark text-decoration-none fw-semibold">
                                        {{ $so->product->name }}
                                    </a>
                                    <span class="badge bg-light text-secondary border ms-1">{{ $so->product->sku }}</span>
                                @else
                                    <span class="text-muted">Product Removed</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $so->quantity }}</td>
                            <td class="text-end">₱{{ number_format($so->unit_price, 2) }}</td>
                            <td class="text-end fw-bold text-success">₱{{ number_format($so->total_amount, 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $so->status_badge_color }} text-uppercase">
                                    {{ ucfirst($so->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="small">{{ $so->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $so->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $so->creator?->name ?? 'System' }} ({{ ucfirst($so->creator?->role ?? 'Staff') }})
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-outline-secondary" href="{{ route('sales-orders.show', $so) }}" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a class="btn btn-outline-secondary" href="{{ route('sales-orders.print', $so) }}" target="_blank" title="Print Invoice">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    @if($so->status === 'pending' && auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('sales-orders.approve', $so) }}" class="d-inline" onsubmit="return confirm('Approve sales order [{{ $so->order_number }}]? This will deduct {{ $so->quantity }} units from live stock.');">
                                            @csrf
                                            <button class="btn btn-outline-success" type="submit" title="Approve & Deduct Stock">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <button class="btn btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $so->id }}" title="Reject Order">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>

                                @if($so->status === 'pending' && auth()->user()->isAdmin())
                                    <!-- Reject Modal -->
                                    <div class="modal fade text-start" id="rejectModal{{ $so->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('sales-orders.reject', $so) }}">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reject Sales Order #{{ $so->order_number }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="text-muted small">Specify a reason for rejecting this order. The staff member will be notified.</p>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Reason for Rejection</label>
                                                            <textarea name="rejection_reason" class="form-control" rows="3" placeholder="e.g. Member cancelled request, pricing dispute, etc." required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject Order</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary"></i>
                                No sales orders found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($salesOrders->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $salesOrders->links() }}
            </div>
        @endif
    </div>
@endsection
