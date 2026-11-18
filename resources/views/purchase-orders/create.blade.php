@extends('layouts.app')

@section('title', 'New Purchase Order')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="text-uppercase text-muted small fw-bold mb-1">
                        <i class="bi bi-cart-plus text-primary me-1"></i>Procurement & Replenishment
                    </p>
                    <h1 class="h2 fw-bold mb-0">Create Purchase Order</h1>
                    <small class="text-muted">Generate an official procurement order for supplier restocking.</small>
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
                    <form method="POST" action="{{ route('purchase-orders.store') }}">
                        @csrf

                        <!-- Product Selection -->
                        <div class="mb-3">
                            <label for="product_id" class="form-label fw-semibold">
                                Select Product to Order <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                                <option value="">-- Choose Equipment or Supplement --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                            data-supplier="{{ $product->supplier_id }}"
                                            data-price="{{ $product->unit_price }}"
                                            data-stock="{{ $product->quantity }}"
                                            data-reorder="{{ $product->reorder_level }}"
                                            @selected(old('product_id', $selectedProductId) == $product->id)>
                                        {{ $product->name }} ({{ $product->sku }}) — Current Stock: {{ $product->quantity }} (Reorder Level: {{ $product->reorder_level }})
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Supplier Selection -->
                        <div class="mb-3">
                            <label for="supplier_id" class="form-label fw-semibold">
                                Target Supplier
                            </label>
                            <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                                <option value="">-- Auto-detect from Product or Select --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $selectedSupplierId) == $supplier->id)>
                                        {{ $supplier->name }} (Lead time: {{ $supplier->lead_time_days }} days)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">If left blank, the product's primary supplier will be used automatically.</small>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Quantity -->
                        <div class="mb-3">
                            <label for="quantity" class="form-label fw-semibold">
                                Order Quantity <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('quantity') is-invalid @enderror" 
                                       id="quantity" 
                                       name="quantity" 
                                       value="{{ old('quantity', 10) }}" 
                                       min="1" 
                                       required>
                                <span class="input-group-text">units</span>
                            </div>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes / Instructions -->
                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">
                                Delivery Notes / Special Instructions
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="e.g. Expedited shipping requested; please deliver to main gym loading bay.">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-send-check me-1"></i>Generate Purchase Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
