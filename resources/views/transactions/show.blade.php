@extends('layouts.app')
@section('title', 'Transaction Details')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="eyebrow mb-1">Transaction Details</p>
            <h1 class="fw-bold mb-0">#{{ $transaction->id }}</h1>
        </div>
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">Back to List</a>
    </div>

    <div class="panel">
        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase fw-bold small mb-3">Details</h6>
                <p><strong>Product:</strong> {{ $transaction->product->name ?? 'Deleted' }}</p>
                <p><strong>Type:</strong> <span
                        class="badge {{ $transaction->type === 'in' ? 'bg-success' : 'bg-danger' }}">{{ strtoupper($transaction->type) }}</span>
                </p>
                <p><strong>Quantity:</strong> {{ $transaction->quantity }}</p>
                <p><strong>Recorded By:</strong> {{ $transaction->user->name ?? 'System' }}</p>
                <p><strong>Date:</strong> {{ $transaction->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase fw-bold small mb-3">Additional Info</h6>
                <p><strong>Unit Price:</strong> ${{ number_format((float) $transaction->unit_price, 2) }}</p>
                <p><strong>Reference:</strong> {{ $transaction->reference ?? '—' }}</p>
                <p><strong>Notes:</strong> {{ $transaction->notes ?? 'No notes.' }}</p>
            </div>
        </div>
    </div>
@endsection