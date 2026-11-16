@extends('layouts.app')
@section('title', 'Purchase Order')
@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><p class="eyebrow mb-1">Procurement</p><h1 class="fw-bold mb-0">{{ $purchaseOrder->reference }}</h1></div><a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}">Back to orders</a></div>
    <section class="panel">
        <dl class="row mb-0">
            <dt class="col-sm-3">Product</dt><dd class="col-sm-9">{{ $purchaseOrder->product?->name ?? 'Archived product' }}</dd>
            <dt class="col-sm-3">Supplier</dt><dd class="col-sm-9">{{ $purchaseOrder->supplier?->name ?? 'Not assigned' }}</dd>
            <dt class="col-sm-3">Quantity</dt><dd class="col-sm-9">{{ $purchaseOrder->quantity }}</dd>
            <dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ ucfirst($purchaseOrder->status) }}</dd>
            <dt class="col-sm-3">Created</dt><dd class="col-sm-9">{{ $purchaseOrder->created_at->format('M j, Y H:i') }}</dd>
            <dt class="col-sm-3">Received</dt><dd class="col-sm-9">{{ $purchaseOrder->received_at?->format('M j, Y H:i') ?? '—' }}</dd>
            <dt class="col-sm-3">Notes</dt><dd class="col-sm-9">{{ $purchaseOrder->notes ?? '—' }}</dd>
        </dl>
        @can('receive', $purchaseOrder)
            <form class="mt-4" method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}">@csrf<button class="btn btn-success">Mark Received &amp; Add Stock</button></form>
        @endcan
    </section>
@endsection
