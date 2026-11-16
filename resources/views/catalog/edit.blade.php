@extends('layouts.app')
@section('title', 'Edit '.$kind)
@section('content')
    <h1 class="fw-bold mb-4">Edit {{ $kind }}</h1>
    @include('catalog.form', ['action' => route($routePrefix.'.update', $record), 'method' => 'PUT'])
@endsection
