@extends('layouts.app')
@section('title', 'Transactions')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Inventory</p>
            <h1 class="fw-bold mb-0">Stock Transactions</h1>
        </div>
        <a class="btn btn-primary btn-sm" href="{{ route('transactions.create') }}">Record Transaction</a>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table inventory-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>User</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td>{{ $tx->created_at->format('M d, Y h:i A') }}</td>
                            <td><strong>{{ $tx->product->name ?? 'Deleted Product' }}</strong></td>
                            <td>
                                <span class="badge {{ $tx->type === 'in' ? 'bg-success' : 'bg-danger' }}">
                                    Stock {{ ucfirst($tx->type) }}
                                </span>
                            </td>
                            <td>{{ $tx->type === 'in' ? '+' : '-' }}{{ $tx->quantity }}</td>
                            <td>{{ $tx->user->name ?? 'System' }}</td>
                            <td>
                                <a class="btn btn-sm btn-outline-secondary"
                                    href="{{ route('transactions.show', $tx) }}">View</a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('transactions.edit', $tx) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('transactions.destroy', $tx) }}"
                                    onsubmit="return confirm('Delete this transaction? This will adjust the stock.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $transactions->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection