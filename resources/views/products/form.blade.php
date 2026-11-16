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
                           placeholder="e.g. 2x4x8 Kiln-Dried Douglas Fir Stud"
                           value="{{ old('name', $product->name ?? '') }}"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- SKU -->
                <div class="col-md-4">
                    <label for="sku" class="form-label fw-semibold">
                        SKU Code <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           class="form-control font-monospace @error('sku') is-invalid @enderror"
                           id="sku"
                           name="sku"
                           placeholder="e.g. LUM-DF-248"
                           value="{{ old('sku', $product->sku ?? '') }}"
                           required>
                    @error('sku')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Category -->
                <div class="col-md-6">
                    <label for="category" class="form-label fw-semibold">
                        Category <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           list="category-suggestions"
                           class="form-control @error('category') is-invalid @enderror"
                           id="category"
                           name="category"
                           placeholder="Select or type category (e.g. Lumber, Tools...)"
                           value="{{ old('category', $product->category_name ?? '') }}"
                           required>
                    <datalist id="category-suggestions">
                        @foreach($categoryList ?? ['Lumber', 'Hardware', 'Tools', 'Electrical', 'Plumbing', 'Paint'] as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Supplier -->
                <div class="col-md-6">
                    <label for="supplier" class="form-label fw-semibold">Supplier / Vendor</label>
                    <input type="text"
                           list="supplier-suggestions"
                           class="form-control @error('supplier') is-invalid @enderror"
                           id="supplier"
                           name="supplier"
                           placeholder="Select or type supplier name..."
                           value="{{ old('supplier', $product->supplier_name ?? '') }}">
                    <datalist id="supplier-suggestions">
                        @if(isset($suppliers))
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->name }}">
                            @endforeach
                        @endif
                    </datalist>
                    @error('supplier')
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
                        Unit Price ($) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
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
                    <small class="text-muted">Per unit selling or cost price</small>
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
