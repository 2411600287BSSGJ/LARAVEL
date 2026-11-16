@extends('layouts.app')
@section('title', 'Add Supplier')
@section('content')
    <h1 class="fw-bold mb-4">Add Supplier</h1>
    @include('suppliers.form', ['action' => route('suppliers.store'), 'method' => 'POST'])
@endsection
