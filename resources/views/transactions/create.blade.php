@extends('layouts.app')
@section('title', 'Record Transaction')
@section('content')
    <div class="mb-4"><p class="eyebrow mb-1">Stock control</p><h1 class="fw-bold">Record Transaction</h1><p class="text-muted">Stock-outs below the product reorder level automatically create a purchase order.</p></div>
    <form class="panel" method="POST" action="{{ route('transactions.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Product</label><select class="form-select" name="product_id" required><option value="">Select product</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }} ({{ $product->sku }}) — {{ $product->quantity }} in stock</option>@endforeach</select>@error('product_id')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            <div class="col-md-3"><label class="form-label">Transaction type</label><select class="form-select" name="type" required><option value="inbound" @selected(old('type') === 'inbound')>Stock in</option><option value="outbound" @selected(old('type') === 'outbound')>Stock out</option></select></div>
            <div class="col-md-3"><label class="form-label">Quantity</label><input class="form-control" type="number" min="1" name="quantity" required value="{{ old('quantity', 1) }}">@error('quantity')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            <div class="col-md-4"><label class="form-label">Unit price (optional)</label><input class="form-control" type="number" min="0" step="0.01" name="unit_price" value="{{ old('unit_price') }}"></div>
            <div class="col-md-8"><label class="form-label">Reference</label><input class="form-control" name="reference" value="{{ old('reference') }}"></div>
            <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="3">{{ old('notes') }}</textarea></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-primary">Save Transaction</button><a class="btn btn-outline-secondary" href="{{ route('transactions.index') }}">Cancel</a></div>
        </div>
    </form>
@endsection
