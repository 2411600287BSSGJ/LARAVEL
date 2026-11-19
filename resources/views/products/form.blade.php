<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif

            <div class="row g-3">
                <!-- Product Name -->
                <div class="col-md-8">
                    <label for="name" class="form-label fw-semibold">
                        Product Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           class="form-control @error('name') is-invalid @enderror"
                           id="name"
                           name="name"
                           placeholder="e.g. Rubber Hex Dumbbell Set (5 - 50 lbs)"
                           value="{{ old('name', $product->name ?? '') }}"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- SKU -->
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="sku" class="form-label fw-semibold mb-0">
                            SKU Code <span class="text-danger">*</span>
                        </label>
                        <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-semibold" id="btnGenerateSku" title="Generate SKU">
                            <i class="bi bi-magic me-1"></i>Auto-Generate
                        </button>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted font-monospace"><i class="bi bi-upc-scan"></i></span>
                        <input type="text"
                               class="form-control font-monospace @error('sku') is-invalid @enderror"
                               id="sku"
                               name="sku"
                               placeholder="e.g. GYM-FW-550"
                               value="{{ old('sku', $product->sku ?? $suggestedSku ?? '') }}"
                               required>
                        <button type="button" class="btn btn-outline-secondary" id="btnRefreshSku" title="Regenerate SKU">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                    </div>
                    <small class="text-muted">Unique inventory identifier</small>
                    @error('sku')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Category -->
                <div class="col-md-6">
                    <label for="category" class="form-label fw-semibold">
                        Category <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                        <option value="">-- Choose a category --</option>
                        @foreach($categoryList ?? ['Free Weights', 'Cardio Equipment', 'Strength Machines', 'Fitness Accessories', 'Supplements & Nutrition', 'Recovery & Mobility'] as $cat)
                            <option value="{{ $cat }}" @selected(old('category', $product->category_name ?? '') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Supplier -->
                <div class="col-md-6">
                    <label for="supplier_id" class="form-label fw-semibold">Supplier / Vendor</label>
                    <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                        <option value="">-- No supplier --</option>
                        @if(isset($suppliers))
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" @selected(old('supplier_id', $product->supplier_id ?? '') == $sup->id)>{{ $sup->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('supplier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Initial / Current Quantity -->
                <div class="col-md-4">
                    <label for="quantity" class="form-label fw-semibold">
                        Quantity in Stock <span class="text-danger">*</span>
                    </label>
                    <input type="number"
                           min="0"
                           class="form-control @error('quantity') is-invalid @enderror"
                           id="quantity"
                           name="quantity"
                           value="{{ old('quantity', $product->quantity ?? 0) }}"
                           required>
                    <small class="text-muted">Current units available</small>
                    @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Reorder Level -->
                <div class="col-md-4">
                    <label for="reorder_level" class="form-label fw-semibold">
                        Reorder Level <span class="text-danger">*</span>
                    </label>
                    <input type="number"
                           min="0"
                           class="form-control @error('reorder_level') is-invalid @enderror"
                           id="reorder_level"
                           name="reorder_level"
                           value="{{ old('reorder_level', $product->reorder_level ?? 10) }}"
                           required>
                    <small class="text-muted">Threshold triggering low-stock alert</small>
                    @error('reorder_level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Unit Price -->
                <div class="col-md-4">
                    <label for="unit_price" class="form-label fw-semibold">
                        Unit Price (₱) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number"
                               min="0"
                               step="0.01"
                               class="form-control @error('unit_price') is-invalid @enderror"
                               id="unit_price"
                               name="unit_price"
                               placeholder="0.00"
                               value="{{ old('unit_price', $product->unit_price ?? '') }}"
                               required>
                    </div>
                    <small class="text-muted">Per unit selling or cost price in Philippine Peso (₱)</small>
                    @error('unit_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Description -->
                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Description / Notes</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description"
                              name="description"
                              rows="3"
                              placeholder="Specifications, dimensions, material, or application instructions...">{{ old('description', $product->description ?? '') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="col-12 d-flex gap-2 pt-3 border-top mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i>Save Product
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary px-4">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const skuInput = document.getElementById('sku');
    const categorySelect = document.getElementById('category');
    const btnGenerate = document.getElementById('btnGenerateSku');
    const btnRefresh = document.getElementById('btnRefreshSku');

    const categoryCodes = {
        'Free Weights': 'FW',
        'Cardio Equipment': 'CRD',
        'Strength Machines': 'STR',
        'Fitness Accessories': 'ACC',
        'Supplements & Nutrition': 'SUP',
        'Recovery & Mobility': 'REC'
    };

    function generateSku() {
        if (!skuInput) return;
        const selectedCat = categorySelect ? categorySelect.value : '';
        const code = categoryCodes[selectedCat] || 'EQP';
        const rand = Math.floor(100 + Math.random() * 900);
        skuInput.value = `GYM-${code}-${rand}`;
        skuInput.classList.remove('is-invalid');
    }

    if (btnGenerate) {
        btnGenerate.addEventListener('click', function (e) {
            e.preventDefault();
            generateSku();
        });
    }

    if (btnRefresh) {
        btnRefresh.addEventListener('click', function (e) {
            e.preventDefault();
            generateSku();
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            // If SKU is empty or begins with standard GYM- prefix, adjust code to selected category
            if (!skuInput.value || skuInput.value.startsWith('GYM-')) {
                generateSku();
            }
        });
    }

    // Auto-generate if field is empty on page load
    if (skuInput && !skuInput.value.trim()) {
        generateSku();
    }
});
</script>
