@extends('layouts.app')

@section('title', 'Record Transaction')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-arrow-left-right text-primary me-1"></i>Stock Control
            </p>
            <h1 class="h2 fw-bold mb-0">Record Inventory Movement</h1>
            <p class="text-muted mb-0">Record physical stock intake, customer dispatches, or inventory adjustments.</p>
        </div>
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Transactions
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('transactions.store') }}" id="transaction-form">
                @csrf

                <div class="row g-3">
                    <!-- Product Selection -->
                    <div class="col-md-7">
                        <label for="product_id" class="form-label fw-semibold">
                            Select Product <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('product_id') is-invalid @enderror"
                                id="product_id"
                                name="product_id"
                                required>
                            <option value="">-- Choose a Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                        data-stock="{{ $product->quantity }}"
                                        data-price="{{ $product->unit_price }}"
                                        data-sku="{{ $product->sku }}"
                                        data-reorder="{{ $product->reorder_level }}"
                                        @selected(old('product_id', request('product_id')) == $product->id)>
                                    {{ $product->name }} ({{ $product->sku }}) — Current: {{ $product->quantity }} in stock
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Current Stock Display Card (JavaScript driven) -->
                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-muted">Stock Level Monitor</label>
                        <div class="p-3 bg-light border rounded d-flex justify-content-between align-items-center" id="stock-monitor-box">
                            <div>
                                <small class="text-muted d-block" id="monitor-sku">Select a product to view stock</small>
                                <strong class="fs-4" id="monitor-quantity">—</strong>
                                <span class="text-muted small">units on hand</span>
                            </div>
                            <span class="badge bg-secondary fs-6" id="monitor-status">No selection</span>
                        </div>
                    </div>

                    <!-- Transaction Type -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Movement Type <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-3 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="type_in" value="stock_in"
                                       @checked(old('type', request('type', 'stock_in')) === 'stock_in')>
                                <label class="form-check-label fw-semibold text-success" for="type_in">
                                    <i class="bi bi-box-arrow-in-down me-1"></i>Stock In (Intake)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="type_out" value="stock_out"
                                       @checked(old('type', request('type')) === 'stock_out')>
                                <label class="form-check-label fw-semibold text-danger" for="type_out">
                                    <i class="bi bi-box-arrow-up me-1"></i>Stock Out (Dispatch)
                                </label>
                            </div>
                        </div>
                        @error('type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Quantity Input -->
                    <div class="col-md-6">
                        <label for="quantity" class="form-label fw-semibold">
                            Movement Quantity <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               min="1"
                               class="form-control @error('quantity') is-invalid @enderror"
                               id="quantity"
                               name="quantity"
                               value="{{ old('quantity', 1) }}"
                               required>
                        <small class="text-muted" id="quantity-help">Enter number of units to move</small>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Reference Number -->
                    <div class="col-md-6">
                        <label for="reference_number" class="form-label fw-semibold">Reference Document / PO Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                            <input type="text"
                                   class="form-control @error('reference_number') is-invalid @enderror"
                                   id="reference_number"
                                   name="reference_number"
                                   placeholder="e.g. PO-8921 or REC-0045"
                                   value="{{ old('reference_number') }}">
                        </div>
                        <small class="text-muted">Purchase order, delivery receipt, or invoice number</small>
                        @error('reference_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Unit Cost / Price -->
                    <div class="col-md-6">
                        <label for="unit_price" class="form-label fw-semibold">Unit Price ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control @error('unit_price') is-invalid @enderror"
                                   id="unit_price"
                                   name="unit_price"
                                   placeholder="0.00"
                                   value="{{ old('unit_price') }}">
                        </div>
                        <small class="text-muted">Defaults to product catalog price if left blank</small>
                        @error('unit_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="col-12">
                        <label for="notes" class="form-label fw-semibold">Notes & Additional Details</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes"
                                  name="notes"
                                  rows="3"
                                  placeholder="Specify destination, customer, supplier batch, or reason...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit & Cancel -->
                    <div class="col-12 d-flex gap-2 pt-3 border-top mt-4">
                        <button type="submit" class="btn btn-primary px-4" id="submit-btn">
                            <i class="bi bi-check2-circle me-1"></i>Save Transaction
                        </button>
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary px-4">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const productSelect = document.getElementById('product_id');
        const monitorSku = document.getElementById('monitor-sku');
        const monitorQty = document.getElementById('monitor-quantity');
        const monitorStatus = document.getElementById('monitor-status');
        const quantityInput = document.getElementById('quantity');
        const quantityHelp = document.getElementById('quantity-help');
        const unitPriceInput = document.getElementById('unit_price');
        const radioIn = document.getElementById('type_in');
        const radioOut = document.getElementById('type_out');

        function updateStockInfo() {
            const selected = productSelect.options[productSelect.selectedIndex];
            if (!selected || !selected.value) {
                monitorSku.textContent = 'Select a product to view stock';
                monitorQty.textContent = '—';
                monitorStatus.textContent = 'No selection';
                monitorStatus.className = 'badge bg-secondary fs-6';
                quantityInput.removeAttribute('max');
                quantityHelp.textContent = 'Enter number of units to move';
                return;
            }

            const stock = parseInt(selected.getAttribute('data-stock'), 10) || 0;
            const price = selected.getAttribute('data-price') || '0.00';
            const sku = selected.getAttribute('data-sku') || '';
            const reorder = parseInt(selected.getAttribute('data-reorder'), 10) || 0;

            monitorSku.textContent = `SKU: ${sku} | Reorder: ${reorder}`;
            monitorQty.textContent = stock;

            if (stock <= 0) {
                monitorStatus.textContent = 'Out of Stock';
                monitorStatus.className = 'badge bg-danger fs-6';
            } else if (stock <= reorder) {
                monitorStatus.textContent = 'Low Stock';
                monitorStatus.className = 'badge bg-warning text-dark fs-6';
            } else {
                monitorStatus.textContent = 'In Stock';
                monitorStatus.className = 'badge bg-success fs-6';
            }

            if (!unitPriceInput.value) {
                unitPriceInput.value = price;
            }

            // Adjust validation based on type
            if (radioOut.checked) {
                quantityInput.max = stock;
                quantityHelp.innerHTML = `<span class="text-danger fw-bold">Stock Out Limit: ${stock} unit(s) available.</span>`;
            } else {
                quantityInput.removeAttribute('max');
                quantityHelp.innerHTML = `<span class="text-success fw-bold">Stock In: Adds to existing ${stock} units.</span>`;
            }
        }

        productSelect.addEventListener('change', updateStockInfo);
        radioIn.addEventListener('change', updateStockInfo);
        radioOut.addEventListener('change', updateStockInfo);

        // Initial trigger
        updateStockInfo();
    });
</script>
@endpush
