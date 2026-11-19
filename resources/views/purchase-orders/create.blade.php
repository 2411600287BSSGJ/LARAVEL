@extends('layouts.app')

@section('title', 'New Purchase Order')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="text-uppercase text-muted small fw-bold mb-1">
                        <i class="bi bi-cart-plus text-primary me-1"></i>Procurement & Replenishment
                    </p>
                    <h1 class="h2 fw-bold mb-0">Create Purchase Order</h1>
                    <small class="text-muted">Add multiple products to one supplier order.</small>
                </div>
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Orders
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-receipt text-primary me-2"></i>Purchase Order Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('purchase-orders.store') }}" id="purchase-order-form">
                        @csrf

                        <div class="mb-4">
                            <label for="supplier_id" class="form-label fw-semibold">Target Supplier</label>
                            <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                                <option value="">-- Use product supplier when possible --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                            data-payment-terms="{{ $supplier->payment_terms }}"
                                            @selected(old('supplier_id', $selectedSupplierId) == $supplier->id)>
                                        {{ $supplier->name }} (Lead time: {{ $supplier->lead_time_days }} days)
                                    </option>
                                @endforeach
                            </select>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Choose a supplier for all items. If blank, one shared product supplier is detected.</small>
                                <small class="fw-semibold" id="payment-terms">Payment terms: Not selected</small>
                            </div>
                            @error('supplier_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0">Products <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-order-item">
                                <i class="bi bi-plus-lg me-1"></i>Add Product
                            </button>
                        </div>
                        <div id="order-items">
                            @php($oldItems = old('items', [['product_id' => $selectedProductId, 'quantity' => 10]]))
                            @foreach($oldItems as $index => $item)
                                <div class="row g-2 align-items-start mb-3 order-item-row">
                                    <div class="col-md-8">
                                        <label class="form-label small">Product</label>
                                        <select class="form-select product-select" name="items[{{ $index }}][product_id]" required>
                                            <option value="">-- Choose product --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        data-price="{{ $product->unit_price }}"
                                                        data-supplier="{{ $product->supplier_id }}"
                                                        @selected(($item['product_id'] ?? null) == $product->id)>
                                                    {{ $product->name }} ({{ $product->sku }}) — ₱{{ number_format($product->unit_price, 2) }} per unit
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("items.$index.product_id")
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Quantity</label>
                                        <input type="number" class="form-control quantity-input" name="items[{{ $index }}][quantity]"
                                               value="{{ $item['quantity'] ?? 10 }}" min="1" required>
                                        @error("items.$index.quantity")
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger remove-order-item" aria-label="Remove product">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <div class="col-12 text-end small text-muted line-total">Line total: ₱0.00</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <span class="text-muted">Estimated payment</span>
                                <strong class="fs-4 text-success" id="estimated-total">₱0.00</strong>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Delivery Notes / Special Instructions</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                                      placeholder="e.g. Expedited shipping requested; please deliver to main gym loading bay.">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-send-check me-1"></i>Generate Purchase Order
                            </button>
                        </div>
                    </form>

                    <template id="order-item-template">
                        <div class="row g-2 align-items-start mb-3 order-item-row">
                            <div class="col-md-8">
                                <label class="form-label small">Product</label>
                                <select class="form-select product-select" required>
                                    <option value="">-- Choose product --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->unit_price }}" data-supplier="{{ $product->supplier_id }}">
                                            {{ $product->name }} ({{ $product->sku }}) — ₱{{ number_format($product->unit_price, 2) }} per unit
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Quantity</label>
                                <input type="number" class="form-control quantity-input" value="1" min="1" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger remove-order-item" aria-label="Remove product">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="col-12 text-end small text-muted line-total">Line total: ₱0.00</div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const rows = document.getElementById('order-items');
            const template = document.getElementById('order-item-template');
            const supplier = document.getElementById('supplier_id');
            const paymentTerms = document.getElementById('payment-terms');
            const addItemButton = document.getElementById('add-order-item');
            let nextIndex = rows.querySelectorAll('.order-item-row').length;

            const formatMoney = (amount) => new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
                minimumFractionDigits: 2
            }).format(amount);

            const refreshTotals = () => {
                let total = 0;
                let inferredSupplierTerms = null;
                const inferredSupplierIds = [];
                const orderRows = Array.from(rows.querySelectorAll('.order-item-row'));
                const selectedProductIds = orderRows.map((row) => row.querySelector('.product-select').value);
                orderRows.forEach((row, rowIndex) => {
                    const selected = row.querySelector('.product-select').selectedOptions[0];
                    const price = Number(selected?.dataset.price || 0);
                    const quantity = Number(row.querySelector('.quantity-input').value || 0);
                    const lineTotal = price * quantity;
                    row.querySelector('.line-total').textContent = `Line total: ${formatMoney(lineTotal)}`;
                    row.querySelector('.remove-order-item').disabled = orderRows.length === 1;
                    row.querySelectorAll('.product-select option').forEach((option) => {
                        option.disabled = option.value !== '' && selectedProductIds.some((id, index) => id === option.value && index !== rowIndex);
                    });
                    total += lineTotal;
                    if (selected?.dataset.supplier) {
                        inferredSupplierIds.push(selected.dataset.supplier);
                    }
                });
                document.getElementById('estimated-total').textContent = formatMoney(total);
                const selectedSupplier = supplier.selectedOptions[0];
                if (!selectedSupplier?.dataset.paymentTerms) {
                    const uniqueSupplierIds = [...new Set(inferredSupplierIds)];
                    if (uniqueSupplierIds.length === 1) {
                        const inferredSupplier = Array.from(supplier.options).find((option) => option.value === uniqueSupplierIds[0]);
                        inferredSupplierTerms = inferredSupplier?.dataset.paymentTerms;
                    }
                }
                const terms = selectedSupplier?.dataset.paymentTerms || inferredSupplierTerms;
                paymentTerms.textContent = terms
                    ? `Payment terms: ${terms}${selectedSupplier?.value ? '' : ' (product supplier)'}`
                    : 'Payment terms: ' + (inferredSupplierIds.length > 1 ? 'choose one supplier' : 'not selected');
                addItemButton.disabled = orderRows.length >= 50;
            };

            addItemButton.addEventListener('click', () => {
                const fragment = template.content.cloneNode(true);
                fragment.querySelector('.product-select').name = `items[${nextIndex}][product_id]`;
                fragment.querySelector('.quantity-input').name = `items[${nextIndex}][quantity]`;
                rows.appendChild(fragment);
                nextIndex += 1;
                refreshTotals();
            });

            rows.addEventListener('input', refreshTotals);
            rows.addEventListener('change', refreshTotals);
            rows.addEventListener('click', (event) => {
                const removeButton = event.target.closest('.remove-order-item');
                if (!removeButton || rows.querySelectorAll('.order-item-row').length <= 1) {
                    return;
                }
                removeButton.closest('.order-item-row').remove();
                rows.querySelectorAll('.order-item-row').forEach((row, index) => {
                    row.querySelector('.product-select').name = `items[${index}][product_id]`;
                    row.querySelector('.quantity-input').name = `items[${index}][quantity]`;
                });
                nextIndex = rows.querySelectorAll('.order-item-row').length;
                refreshTotals();
            });
            supplier.addEventListener('change', refreshTotals);
            refreshTotals();
        })();
    </script>
@endpush
