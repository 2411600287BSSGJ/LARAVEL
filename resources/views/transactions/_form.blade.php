    <div class="mb-4">
        <p class="eyebrow mb-1">Stock control</p>
        <h1 class="fw-bold">{{ $transaction->exists ? 'Edit Transaction' : 'Record Transaction' }}</h1>
        <p class="text-muted">Stock changes are applied to product quantities and checked against reorder levels.</p>
    </div>

    <form class="panel" method="POST" action="{{ $transaction->exists ? route('transactions.update', $transaction) : route('transactions.store') }}">
        @csrf
        @if($transaction->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="product_id">Product</label>
                <select id="product_id" class="form-select" name="product_id" required>
                    <option value="">Select product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(old('product_id', $transaction->product_id) == $product->id)>{{ $product->name }} ({{ $product->sku }}) — {{ $product->quantity }} in stock</option>
                    @endforeach
                </select>
                @error('product_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label" for="type">Transaction type</label>
                <select id="type" class="form-select" name="type" required>
                    <option value="in" @selected(old('type', $transaction->type) === 'in')>Stock in</option>
                    <option value="out" @selected(old('type', $transaction->type) === 'out')>Stock out</option>
                </select>
                @error('type')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label" for="quantity">Quantity</label>
                <input id="quantity" class="form-control" type="number" min="1" name="quantity" required value="{{ old('quantity', $transaction->quantity ?? 1) }}">
                @error('quantity')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="unit_price">Unit price (optional)</label>
                <input id="unit_price" class="form-control" type="number" min="0" step="0.01" name="unit_price" value="{{ old('unit_price', $transaction->unit_price) }}">
                @error('unit_price')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-8">
                <label class="form-label" for="reference">Reference</label>
                <input id="reference" class="form-control" name="reference" maxlength="255" value="{{ old('reference', $transaction->reference) }}">
                @error('reference')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="notes">Notes</label>
                <textarea id="notes" class="form-control" name="notes" rows="3">{{ old('notes', $transaction->notes) }}</textarea>
                @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">{{ $transaction->exists ? 'Update Transaction' : 'Save Transaction' }}</button>
                <a class="btn btn-outline-secondary" href="{{ $transaction->exists ? route('transactions.show', $transaction) : route('transactions.index') }}">Cancel</a>
            </div>
        </div>
    </form>
