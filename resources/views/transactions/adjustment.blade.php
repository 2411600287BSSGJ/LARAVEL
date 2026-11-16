@extends('layouts.app')

@section('title', 'Physical Stock Adjustment')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-sliders text-primary me-1"></i>Inventory Audit
            </p>
            <h1 class="h2 fw-bold mb-0">Stock Count Adjustment</h1>
            <p class="text-muted mb-0">Reconcile physical inventory counts against system records with audit tracking.</p>
        </div>
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Transactions
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('adjustments.store') }}" id="adjustment-form">
                @csrf

                <div class="row g-3">
                    <!-- Product Selection -->
                    <div class="col-md-6">
                        <label for="product_id" class="form-label fw-semibold">
                            Select Product to Audit <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('product_id') is-invalid @enderror"
                                id="product_id"
                                name="product_id"
                                required>
                            <option value="">-- Choose Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                        data-stock="{{ $product->quantity }}"
                                        data-sku="{{ $product->sku }}"
                                        @selected(old('product_id', request('product_id')) == $product->id)>
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Current System Quantity (Read-only) -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-muted">Current System Stock</label>
                        <input type="text"
                               class="form-control bg-light fw-bold text-center fs-5"
                               id="system_quantity"
                               readonly
                               value="0">
                        <small class="text-muted">Recorded in database</small>
                    </div>

                    <!-- Physical Count Input -->
                    <div class="col-md-3">
                        <label for="physical_count" class="form-label fw-semibold">
                            Physical Count <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               min="0"
                               class="form-control fw-bold text-center fs-5 @error('physical_count') is-invalid @enderror"
                               id="physical_count"
                               name="physical_count"
                               value="{{ old('physical_count', '') }}"
                               placeholder="Counted"
                               required>
                        <small class="text-muted">Actual physical inventory</small>
                        @error('physical_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Calculated Discrepancy Indicator Box -->
                    <div class="col-12">
                        <div class="p-3 border rounded bg-light d-flex justify-content-between align-items-center" id="difference-box">
                            <div>
                                <small class="text-muted text-uppercase fw-bold d-block">Inventory Discrepancy (Physical - System)</small>
                                <span class="fs-4 fw-bold" id="difference-display">—</span>
                            </div>
                            <span class="badge bg-secondary fs-6" id="difference-badge">Select a product and enter count</span>
                        </div>
                    </div>

                    <!-- Reason for Adjustment -->
                    <div class="col-md-6">
                        <label for="reason" class="form-label fw-semibold">
                            Adjustment Reason <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('reason') is-invalid @enderror"
                                id="reason"
                                name="reason"
                                required>
                            <option value="">-- Select Reason --</option>
                            @foreach($reasons as $key => $label)
                                <option value="{{ $key }}" @selected(old('reason') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Additional Notes -->
                    <div class="col-md-6">
                        <label for="notes" class="form-label fw-semibold">Additional Context / Audit Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes"
                                  name="notes"
                                  rows="2"
                                  placeholder="Location of count, condition of damaged packaging, or supervisor signoff...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit / Cancel -->
                    <div class="col-12 d-flex gap-2 pt-3 border-top mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i>Process Adjustment
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
        const systemQtyInput = document.getElementById('system_quantity');
        const physicalInput = document.getElementById('physical_count');
        const diffDisplay = document.getElementById('difference-display');
        const diffBadge = document.getElementById('difference-badge');

        function calculateDifference() {
            const selected = productSelect.options[productSelect.selectedIndex];
            if (!selected || !selected.value) {
                systemQtyInput.value = '—';
                diffDisplay.textContent = '—';
                diffBadge.textContent = 'Select product first';
                diffBadge.className = 'badge bg-secondary fs-6';
                return;
            }

            const systemStock = parseInt(selected.getAttribute('data-stock'), 10) || 0;
            systemQtyInput.value = systemStock;

            if (physicalInput.value === '') {
                diffDisplay.textContent = '—';
                diffBadge.textContent = 'Enter physical count';
                diffBadge.className = 'badge bg-secondary fs-6';
                return;
            }

            const physical = parseInt(physicalInput.value, 10);
            const diff = physical - systemStock;

            if (diff === 0) {
                diffDisplay.innerHTML = `<span class="text-success">0 units (Accurate)</span>`;
                diffBadge.textContent = 'Counts Match (No Change)';
                diffBadge.className = 'badge bg-success fs-6';
            } else if (diff > 0) {
                diffDisplay.innerHTML = `<span class="text-info">+${diff} units surplus</span>`;
                diffBadge.textContent = `Surplus (+${diff} to stock)`;
                diffBadge.className = 'badge bg-info text-dark fs-6';
            } else {
                diffDisplay.innerHTML = `<span class="text-danger">${diff} units deficit</span>`;
                diffBadge.textContent = `Deficit (${diff} from stock)`;
                diffBadge.className = 'badge bg-danger fs-6';
            }
        }

        productSelect.addEventListener('change', calculateDifference);
        physicalInput.addEventListener('input', calculateDifference);

        calculateDifference();
    });
</script>
@endpush
