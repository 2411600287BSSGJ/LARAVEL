@extends('layouts.app')
@section('title', 'Transactions')
@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><p class="eyebrow mb-1">Stock control</p><h1 class="fw-bold mb-0">Transactions</h1></div><div class="d-flex gap-2"><a class="btn btn-outline-success" href="{{ route('exports.download', 'transactions') }}">Export CSV</a><a class="btn btn-primary" href="{{ route('transactions.create') }}">Record Transaction</a></div></div>
    <div class="panel table-responsive"><table class="table inventory-table align-middle mb-0"><thead><tr><th>Date</th><th>Product</th><th>Type</th><th>Quantity</th><th>Unit price</th><th>User</th><th>Reference</th><th>Notes</th></tr></thead><tbody>
        @forelse($transactions as $transaction)<tr><td>{{ $transaction->created_at->format('M j, Y H:i') }}</td><td>{{ $transaction->product?->name ?? 'Archived product' }}</td><td><span class="badge {{ $transaction->type === 'inbound' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ ucfirst($transaction->type) }}</span></td><td>{{ $transaction->quantity }}</td><td>{{ $transaction->unit_price === null ? '—' : '$'.number_format((float) $transaction->unit_price, 2) }}</td><td>{{ $transaction->user?->name ?? 'System' }}</td><td>{{ $transaction->reference ?? '—' }}</td><td>{{ $transaction->notes ?? '—' }}</td></tr>@empty<tr><td colspan="8" class="text-center text-muted py-4">No transactions have been recorded.</td></tr>@endforelse
    </tbody></table><div class="mt-3">{{ $transactions->links() }}</div></div>
@endsection
