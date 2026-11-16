@extends('layouts.app')
@section('title', 'Edit Product')
@section('content')
    <div class="mb-4"><p class="eyebrow mb-1">Inventory</p><h1 class="fw-bold">Edit Product</h1></div>
    @include('products.form', ['action' => route('products.update', $product), 'method' => 'PUT'])
@endsection
