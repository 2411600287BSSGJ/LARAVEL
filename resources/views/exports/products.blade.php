@extends('layouts.app')

@section('title', 'Products Report')

@section('content')
    <h1 class="mb-4">Gym Equipment Report</h1>
    <table class="table table-sm" style="width:100%; border-collapse:collapse;">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>SKU</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Reorder Level</th>
            <th>Unit Price (₱)</th>
            <th>Supplier</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->category }}</td>
                <td>{{ $product->quantity }}</td>
                <td>{{ $product->reorder_level }}</td>
                <td>₱{{ number_format($product->unit_price, 2) }}</td>
                <td>{{ $product->supplier }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
