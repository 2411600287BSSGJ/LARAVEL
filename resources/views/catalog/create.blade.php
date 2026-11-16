@extends('layouts.app')
@section('title', 'Add '.$kind)
@section('content')
    <h1 class="fw-bold mb-4">Add {{ $kind }}</h1>
    @include('catalog.form', ['action' => route($routePrefix.'.store'), 'method' => 'POST'])
@endsection
