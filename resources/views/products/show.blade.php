@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Product · {{ $product->sku }}</p>
            <h1 class="fw-bold mb-1">{{ $product->name }}</h1>
            <p class="text-muted mb-0">{{ $product->description ?: 'No description provided.' }}</p>
        </div>
        <div class="d-flex gap-2">
            @can('update', $product)<a class="btn btn-outline-primary" href="{{ route('products.edit', $product) }}">Edit</a>@endcan
            <a class="btn btn-outline-secondary" href="{{ route('products.index') }}">Back to products</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><span class="metric-label">Quantity</span><strong>{{ $product->quantity }}</strong><span class="metric-meta">Reorder at {{ $product->reorder_level }}</span></div></div>
        <div class="col-md-3"><div class="metric-card"><span class="metric-label">Unit price</span><strong>${{ number_format((float) $product->unit_price, 2) }}</strong></div></div>
        <div class="col-md-3"><div class="metric-card"><span class="metric-label">Manufacturer</span><strong class="fs-5">{{ $product->brand?->name ?? '—' }}</strong></div></div>
        <div class="col-md-3"><div class="metric-card"><span class="metric-label">Equipment type</span><strong class="fs-5">{{ $product->category?->name ?? '—' }}</strong></div></div>
    </div>

    <section class="panel">
        <div class="panel-heading"><div><p class="eyebrow mb-1">Stock control</p><h2>Transaction history</h2></div></div>
        <div class="table-responsive mt-3">
            <table class="table inventory-table align-middle mb-0">
                <thead><tr><th>Date</th><th>Type</th><th>Quantity</th><th>User</th><th>Reference</th></tr></thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td><a href="{{ route('transactions.show', $transaction) }}">{{ $transaction->created_at->format('M j, Y H:i') }}</a></td>
                            <td><span class="badge {{ $transaction->type === 'in' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $transaction->type === 'in' ? 'In' : 'Out' }}</span></td>
                            <td>{{ $transaction->quantity }}</td>
                            <td>{{ $transaction->user?->name ?? 'System' }}</td>
                            <td>{{ $transaction->reference ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No transactions recorded for this product.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $transactions->links() }}</div>
        </div>
    </section>
@endsection
