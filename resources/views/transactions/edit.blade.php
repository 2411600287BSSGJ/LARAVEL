@extends('layouts.app')

@section('title', 'Edit Audit Record #' . $transaction->id)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="text-uppercase text-muted small fw-bold mb-1">
                        <i class="bi bi-pencil-square text-primary me-1"></i>Audit Trail Correction
                    </p>
                    <h1 class="h2 fw-bold mb-0">Edit Audit Record #{{ $transaction->id }}</h1>
                    <small class="text-muted">Recorded on {{ $transaction->created_at ? $transaction->created_at->format('F d, Y \a\t h:i A') : '—' }}</small>
                </div>
                <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Record
                </a>
            </div>

            <!-- Product Context Info Banner -->
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold d-block">Target Product</span>
                        <strong class="fs-5 text-dark">{{ $transaction->product?->name ?? 'Archived Product' }}</strong>
                        <span class="badge bg-secondary ms-2 font-monospace">{{ $transaction->product?->sku }}</span>
                    </div>
                    <div class="text-end">
                        <span class="text-muted small text-uppercase fw-bold d-block">Current Stock</span>
                        <span class="badge bg-dark fs-6">{{ $transaction->product?->quantity ?? 0 }} units</span>
                    </div>
                </div>
            </div>

            <!-- Edit Form Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-sliders text-primary me-2"></i>Audit Record Details
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('transactions.update', $transaction) }}">
                        @csrf
                        @method('PUT')

                        <!-- Movement Type Display -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Movement Type</label>
                            <div>
                                <span class="badge bg-{{ $transaction->type_badge_color }} fs-6">
                                    {{ $transaction->type_display }}
                                </span>
                                <small class="text-muted ms-2">(Movement type cannot be altered once logged. Create an adjustment to reconcile discrepancies.)</small>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="mb-3">
                            <label for="quantity" class="form-label fw-semibold">
                                Quantity Moved <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="number" 
                                class="form-control @error('quantity') is-invalid @enderror" 
                                id="quantity" 
                                name="quantity" 
                                value="{{ old('quantity', abs($transaction->quantity)) }}" 
                                min="1" 
                                required
                            >
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>Modifying this quantity will automatically reconcile the product's current stock balance.
                            </div>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Unit Price (Philippine Peso) -->
                        <div class="mb-3">
                            <label for="unit_price" class="form-label fw-semibold">
                                Unit Price (₱ PHP)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">₱</span>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    class="form-control @error('unit_price') is-invalid @enderror" 
                                    id="unit_price" 
                                    name="unit_price" 
                                    value="{{ old('unit_price', $transaction->unit_price) }}"
                                >
                            </div>
                            @error('unit_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Reference Number -->
                        <div class="mb-3">
                            <label for="reference_number" class="form-label fw-semibold">
                                Reference Number / Invoice # / PO #
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('reference_number') is-invalid @enderror" 
                                id="reference_number" 
                                name="reference_number" 
                                value="{{ old('reference_number', $transaction->reference_number) }}" 
                                placeholder="e.g. REC-2026-001"
                            >
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes / Justification -->
                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">
                                Audit Notes / Correction Reason
                            </label>
                            <textarea 
                                class="form-control @error('notes') is-invalid @enderror" 
                                id="notes" 
                                name="notes" 
                                rows="3" 
                                placeholder="State reasons for this correction or additional context..."
                            >{{ old('notes', $transaction->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i>Save Audit Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
