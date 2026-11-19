@extends('layouts.app')

@section('title', 'New Sales Order')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                        <i class="bi bi-arrow-left me-1"></i>Back to Sales Orders
                    </a>
                    <h1 class="h2 fw-bold mb-1">Create Sales Order</h1>
                    <p class="text-muted mb-0">Record a customer or gym member equipment/supplement purchase. All orders require administrator approval.</p>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger shadow-sm border-0 alert-dismissible fade show" role="alert">
                    <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Order submission failed:</h6>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('sales-orders.store') }}" id="salesOrderForm">
                        @csrf

                        <h5 class="fw-bold text-primary mb-3">
                            <i class="bi bi-person-badge me-2"></i>Customer / Member Information
                        </h5>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Customer / Member Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" placeholder="e.g. John Doe / Walk-in Member" required>
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="text" name="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}" placeholder="e.g. 0917-123-4567">
                                @error('customer_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Email Address (Optional)</label>
                                <input type="email" name="customer_email" class="form-control @error('customer_email') is-invalid @enderror" value="{{ old('customer_email') }}" placeholder="customer@example.com">
                                @error('customer_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold text-primary mb-3">
                            <i class="bi bi-box-seam me-2"></i>Product Selection & Pricing
                        </h5>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Select Product <span class="text-danger">*</span></label>
                                <select name="product_id" id="product_select" class="form-select @error('product_id') is-invalid @enderror" required>
                                    <option value="" data-stock="0" data-price="0">-- Select an inventory item --</option>
                                    @foreach($products as $p)
                                        @php
                                            $isOutOfStock = $p->quantity <= 0;
                                        @endphp
                                        <option value="{{ $p->id }}" 
                                                data-stock="{{ $p->quantity }}" 
                                                data-price="{{ $p->unit_price }}"
                                                data-name="{{ $p->name }}"
                                                @selected(old('product_id', $selectedProductId) == $p->id)
                                                @disabled($isOutOfStock)>
                                            {{ $p->name }} ({{ $p->sku }}) — 
                                            @if($isOutOfStock)
                                                [OUT OF STOCK - 0 available]
                                            @else
                                                [{{ $p->quantity }} in stock] — ₱{{ number_format($p->unit_price, 2) }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted">
                                    <i class="bi bi-info-circle me-1"></i>Products currently out of stock cannot be processed in sales orders.
                                </div>
                                @error('product_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Available Stock</label>
                                <div class="input-group">
                                    <input type="text" id="available_stock_display" class="form-control bg-light fw-bold" readonly value="—">
                                    <span class="input-group-text bg-light">units</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Quantity to Order <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity_input" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Unit Price (₱)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" name="unit_price" id="unit_price_input" class="form-control @error('unit_price') is-invalid @enderror" value="{{ old('unit_price') }}" placeholder="0.00">
                                </div>
                                <div class="form-text small">Leave blank to use default catalog price.</div>
                                @error('unit_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold text-muted">Estimated Total Amount:</span>
                                    <span class="h3 fw-bold text-success mb-0" id="total_amount_display">₱0.00</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Order Notes / Instructions</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="e.g. Member locker delivery, special discount code applied, payment reference...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                                <i class="bi bi-send-check me-1"></i>Submit for Admin Approval
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const productSelect = document.getElementById('product_select');
        const stockDisplay = document.getElementById('available_stock_display');
        const qtyInput = document.getElementById('quantity_input');
        const unitPriceInput = document.getElementById('unit_price_input');
        const totalDisplay = document.getElementById('total_amount_display');
        const submitBtn = document.getElementById('submitBtn');

        function updateCalculations() {
            const selected = productSelect.options[productSelect.selectedIndex];
            if (!selected || !selected.value) {
                stockDisplay.value = '—';
                totalDisplay.textContent = '₱0.00';
                return;
            }

            const stock = parseInt(selected.dataset.stock || 0, 10);
            const defaultPrice = parseFloat(selected.dataset.price || 0);

            stockDisplay.value = stock;
            qtyInput.max = stock > 0 ? stock : 1;

            if (stock <= 0) {
                stockDisplay.classList.add('text-danger');
                stockDisplay.classList.remove('text-success');
                submitBtn.disabled = true;
            } else {
                stockDisplay.classList.remove('text-danger');
                stockDisplay.classList.add('text-success');
                submitBtn.disabled = false;
            }

            if (!unitPriceInput.value) {
                unitPriceInput.placeholder = defaultPrice.toFixed(2);
            }

            const price = parseFloat(unitPriceInput.value) || defaultPrice;
            const qty = parseInt(qtyInput.value || 1, 10);
            const total = qty * price;

            totalDisplay.textContent = '₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        productSelect.addEventListener('change', function () {
            const selected = productSelect.options[productSelect.selectedIndex];
            if (selected && selected.value) {
                unitPriceInput.value = parseFloat(selected.dataset.price || 0).toFixed(2);
            }
            updateCalculations();
        });

        qtyInput.addEventListener('input', updateCalculations);
        unitPriceInput.addEventListener('input', updateCalculations);

        updateCalculations();
    });
</script>
@endpush
