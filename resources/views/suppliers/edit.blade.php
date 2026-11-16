@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('content')
    <h1 class="fw-bold mb-4">Edit Supplier</h1>
    @include('suppliers.form', ['action' => route('suppliers.update', $supplier), 'method' => 'PUT'])
@endsection
