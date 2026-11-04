@extends('layouts.app')
@section('content')
    <div class="row">
        @include('partials.sidebar')
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <div class="panel-heading mb-3 d-flex justify-content-between">
                    <h2>Trainers</h2>
                    <a href="{{ route('trainers.create') }}" class="btn btn-primary btn-sm">+ Add Trainer</a>
                </div>
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div> @endif
                <table class="table inventory-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Specialty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trainers as $t)
                            <tr>
                                <td>{{ $t->name }}</td>
                                <td>{{ $t->email }}</td>
                                <td>{{ $t->specialty }}</td>
                                <td>
                                    <a href="{{ route('trainers.show', $t) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    <a href="{{ route('trainers.edit', $t) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('trainers.destroy', $t) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>
@endsection