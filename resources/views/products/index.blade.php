@extends('layouts.app')

@section('title', 'Product Catalog')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-bold mb-1">
                <i class="bi bi-activity text-danger me-1"></i>Gym Equipment & Inventory
            </p>
            <h1 class="h2 fw-bold mb-1">Equipment Catalog</h1>
            <p class="text-muted mb-0">Manage gym equipment, free weights, machines, accessories, and nutrition stock.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-success" href="{{ route('reports.export.valuation') }}">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
            </a>
            <a class="btn btn-primary" href="{{ route('products.create') }}">
                <i class="bi bi-plus-lg me-1"></i>Add Product
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('products.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Search Catalog</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name, SKU, or supplier...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Category</label>
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            @foreach($categoryList as $cat)
                                <option value="{{ $cat }}" @selected(request('category') == $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Stock Status</label>
                        <select class="form-select" name="stock">
                            <option value="">All Stock Levels</option>
                            <option value="in_stock" @selected(request('stock') === 'in_stock')>In Stock (> Reorder)</option>
                            <option value="low" @selected(request('stock') === 'low')>Low Stock (≤ Reorder)</option>
                            <option value="out" @selected(request('stock') === 'out')>Out of Stock (0)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Sort Order</label>
                        <select class="form-select" name="sort">
                            <option value="recent" @selected(request('sort', 'recent') === 'recent')>Newest First</option>
                            <option value="name" @selected(request('sort') === 'name')>Name (A - Z)</option>
                            <option value="stock_low" @selected(request('sort') === 'stock_low')>Lowest Stock First</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Highest Price First</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button class="btn btn-primary flex-fill" type="submit">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a class="btn btn-outline-secondary" href="{{ route('products.index') }}" title="Reset">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 25%;">Product Name & SKU</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Reorder Level</th>
                        <th class="text-end">Unit Price (₱)</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('products.show', $product) }}" class="fw-bold text-decoration-none text-dark">
                                        {{ $product->name }}
                                    </a>
                                    @if($product->created_at && $product->created_at->diffInHours(now()) <= 48)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1 py-0 small" style="font-size: 0.68rem;" title="Newly Added Product">
                                            <i class="bi bi-sparkles me-1"></i>NEW
                                        </span>
                                    @endif
                                </div>
                                <small class="text-muted font-monospace"><i class="bi bi-upc me-1"></i>{{ $product->sku }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    {{ $product->category_name }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $product->supplier_name }}</small>
                            </td>
                            <td class="text-center">
                                <strong class="fs-6 {{ $product->quantity == 0 ? 'text-danger' : ($product->quantity <= $product->reorder_level ? 'text-warning' : 'text-success') }}">
                                    {{ $product->quantity }}
                                </strong>
                            </td>
                            <td class="text-center text-muted">
                                {{ $product->reorder_level }}
                            </td>
                            <td class="text-end fw-semibold">
                                ₱{{ number_format((float) $product->unit_price, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $product->stock_status_color }}">
                                    {{ $product->stock_status }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-info" href="{{ route('products.show', $product) }}" title="View details and transaction history">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('products.edit', $product) }}" title="Edit product">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                <h5>No Products Found</h5>
                                <p class="small mb-3">No products matched your search or filter criteria.</p>
                                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">Add New Product</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted">
                    Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                </span>
                <div>
                    {{ $products->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
