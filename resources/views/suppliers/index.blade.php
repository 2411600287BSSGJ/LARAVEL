@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-truck text-primary me-1"></i>Procurement & Vendors
            </p>
            <h1 class="h2 fw-bold mb-1">Suppliers & Manufacturers</h1>
            <p class="text-muted mb-0">Manage equipment vendors, nutrition distributors, fulfillment times, and order performance.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-success" href="{{ route('exports.download', 'suppliers') }}">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
            </a>
            @can('create', \App\Models\Supplier::class)
                <a class="btn btn-primary" href="{{ route('suppliers.create') }}">
                    <i class="bi bi-plus-lg me-1"></i>Add Supplier
                </a>
            @endcan
        </div>
    </div>

    <!-- Suppliers Table Card -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 25%;">Supplier / Company</th>
                        <th>Primary Contact</th>
                        <th>Contact Email & Phone</th>
                        <th class="text-center">Lead Time</th>
                        <th class="text-center">Orders</th>
                        <th class="text-center">Fulfillment</th>
                        <th class="text-end" style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>
                                <a href="{{ route('suppliers.show', $supplier) }}" class="fw-bold text-dark text-decoration-none d-block">
                                    {{ $supplier->name }}
                                </a>
                                @if($supplier->address)
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 220px;">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $supplier->address }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $supplier->contact_person ?: '—' }}</strong>
                            </td>
                            <td>
                                @if($supplier->email)
                                    <div class="small"><i class="bi bi-envelope me-1 text-muted"></i>{{ $supplier->email }}</div>
                                @endif
                                @if($supplier->phone)
                                    <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $supplier->phone }}</div>
                                @endif
                                @if(!$supplier->email && !$supplier->phone)
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $supplier->lead_time_days }} days</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary">{{ $supplier->purchase_orders_count }} POs</span>
                            </td>
                            <td class="text-center">
                                @php $perf = $supplier->calculatePerformance(); @endphp
                                <span class="badge {{ $perf >= 80 ? 'bg-success' : ($perf >= 50 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                    {{ number_format($perf, 1) }}%
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-info" href="{{ route('suppliers.show', $supplier) }}" title="View Profile">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('create', \App\Models\PurchaseOrder::class)
                                    <a class="btn btn-sm btn-outline-success" href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}" title="Create Purchase Order">
                                        <i class="bi bi-cart-plus"></i>
                                    </a>
                                @endcan
                                @can('update', $supplier)
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('suppliers.edit', $supplier) }}" title="Edit Supplier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('delete', $supplier)
                                    <form class="d-inline" method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Archive this supplier?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Archive Supplier">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-truck fs-1 d-block mb-2 text-secondary"></i>
                                <h5>No Suppliers Found</h5>
                                <p class="small mb-3">Add vendor partners to organize inventory procurement and automatic restocking.</p>
                                @can('create', \App\Models\Supplier::class)
                                    <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">Add First Supplier</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted">
                    Showing {{ $suppliers->firstItem() }} to {{ $suppliers->lastItem() }} of {{ $suppliers->total() }} suppliers
                </span>
                <div>
                    {{ $suppliers->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
