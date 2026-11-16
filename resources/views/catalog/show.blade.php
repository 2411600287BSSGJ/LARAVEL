@extends('layouts.app')

@section('title', $kind)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">{{ $kind }}</p>
            <h1 class="fw-bold mb-1">{{ $record->name }}</h1>
            <p class="text-muted mb-0">{{ $record->description ?: 'No description provided.' }}</p>
        </div>
        <div class="d-flex gap-2">
            @can('update', $record)<a class="btn btn-outline-primary" href="{{ route($routePrefix.'.edit', $record) }}">Edit</a>@endcan
            <a class="btn btn-outline-secondary" href="{{ route($routePrefix.'.index') }}">Back</a>
        </div>
    </div>

    <section class="panel">
        <div class="panel-heading"><div><p class="eyebrow mb-1">Inventory</p><h2>Products using this {{ strtolower($kind) }}</h2></div></div>
        <div class="table-responsive mt-3">
            <table class="table inventory-table align-middle mb-0">
                <thead><tr><th>Product</th><th>SKU</th><th>Quantity</th><th>Unit price</th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>${{ number_format((float) $product->unit_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No active products use this {{ strtolower($kind) }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $products->links() }}</div>
        </div>
    </section>
@endsection
