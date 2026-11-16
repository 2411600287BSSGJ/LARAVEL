@extends('layouts.app')
@section('title', 'Record Transaction')

@section('content')
    <div class="mb-4">
        <p class="eyebrow mb-1">Inventory</p>
        <h1 class="fw-bold mb-0">Record Transaction</h1>
    </div>

    <div class="panel">
        <form method="POST" action="{{ route('transactions.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Product</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">Select a product...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} (SKU: {{ $product->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Transaction Type</label>
                    <select name="type" class="form-select" required>
                        <option value="in">Stock In (+)</option>
                        <option value="out">Stock Out (-)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="1" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Save Transaction</button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
@endsection