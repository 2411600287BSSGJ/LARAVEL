@extends('layouts.app')
@section('title', 'Add Product')
@section('content')
    <div class="mb-4"><p class="eyebrow mb-1">Inventory</p><h1 class="fw-bold">Add Product</h1></div>
    @include('products.form', ['action' => route('products.store'), 'method' => 'POST'])
@endsection
