@extends('layouts.app')

@section('title', 'Add New Product')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-box-seam text-primary me-1"></i>Hardware Inventory
            </p>
            <h1 class="h2 fw-bold mb-0">Add New Product</h1>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Catalog
        </a>
    </div>

    @include('products.form', [
        'action' => route('products.store'),
        'method' => 'POST',
        'product' => new \App\Models\Product()
    ])
@endsection
