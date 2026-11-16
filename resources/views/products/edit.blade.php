@extends('layouts.app')

@section('title', 'Edit Product - ' . $product->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-pencil-square text-primary me-1"></i>Hardware Inventory
            </p>
            <h1 class="h2 fw-bold mb-0">Edit Product: {{ $product->name }}</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.show', $product) }}" class="btn btn-outline-info">
                <i class="bi bi-eye me-1"></i>View Product
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Catalog
            </a>
        </div>
    </div>

    @include('products.form', [
        'action' => route('products.update', $product),
        'method' => 'PUT',
        'product' => $product
    ])
@endsection
