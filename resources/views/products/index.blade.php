@extends('layouts.app')
@section('title', 'Products')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Inventory</p>
            <h1 class="fw-bold mb-0">Products</h1>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-success btn-sm" href="{{ route('exports.download', 'products') }}">Export CSV</a>
            @can('create', \App\Models\Product::class)
                <a class="btn btn-primary btn-sm" href="{{ route('products.create') }}">Add Product</a>
            @endcan
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="panel mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-muted small fw-semibold">Search</label>
                <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Name or SKU">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Brand</label>
                <select class="form-select" name="brand_id">
                    <option value="">All brands</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Category</label>
                <select class="form-select" name="category_id">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                            {{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Stock</label>
                <select class="form-select" name="stock">
                    <option value="">Any</option>
                    <option value="low" @selected(request('stock') === 'low')>Low stock</option>
                    <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
                    <option value="in_stock" @selected(request('stock') === 'in_stock')>In stock</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2 mt-3">
                <button class="btn btn-primary btn-sm">Filter</button>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('products.index') }}">Reset</a>
            </div>
        </div>
    </form>

    <!-- Table -->
    <div class="panel">
        <div class="table-responsive">
            <table class="table inventory-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Reorder Level</th>
                        <th>Unit Price</th>
                        @can('create', \App\Models\Product::class)
                        <th>Actions</th>@endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $product) }}"
                                    class="text-decoration-none"><strong>{{ $product->name }}</strong></a>
                                <div class="small text-muted">SKU: {{ $product->sku }}</div>
                            </td>
                            <td>{{ $product->brand?->name ?? '—' }}</td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>
                                <span
                                    class="badge {{ $product->quantity === 0 ? 'bg-danger' : ($product->quantity <= $product->reorder_level ? 'bg-warning text-dark' : 'bg-success') }}">
                                    {{ $product->quantity }}
                                </span>
                            </td>
                            <td>{{ $product->reorder_level }}</td>
                            <td>${{ number_format((float) $product->unit_price, 2) }}</td>
                            @can('create', \App\Models\Product::class)
                                <td class="text-nowrap">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('products.edit', $product) }}">Edit</a>
                                    @can('delete', $product)
                                        <form class="d-inline" method="POST" action="{{ route('products.destroy', $product) }}"
                                            onsubmit="return confirm('Archive this product?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Archive</button>
                                        </form>
                                    @endcan
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No products match your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection