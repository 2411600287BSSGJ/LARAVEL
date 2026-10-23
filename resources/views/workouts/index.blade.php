@extends('layouts.app')

@section('content')
    <div class="row">
        <!-- SIDEBAR (Now handled by the partial file) -->
        @include('partials.sidebar')

        <!-- MAIN CONTENT (TABLE) -->
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <div class="panel-heading mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="eyebrow mb-1">Activity log</p>
                        <h2>All Workouts</h2>
                    </div>
                    <a href="{{ route('workouts.create') }}" class="btn btn-primary btn-sm">+ Add Workout</a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive mt-3">
                    <table class="table inventory-table mb-0">
                        <thead>
                            <tr>
                                <th>Workout</th>
                                <th>Type</th>
                                <th>Minutes</th>
                                <th>Calories</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workouts as $w)
                                <tr>
                                    <td><strong>{{ $w->name }}</strong></td>
                                    <td>{{ $w->type }}</td>
                                    <td>{{ $w->minutes }} min</td>
                                    <td>{{ $w->calories }} kcal</td>
                                    <td>
                                        <span class="status-badge status-{{ $w->status }}">
                                            <span></span>{{ ucfirst($w->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('workouts.show', $w) }}"
                                            class="btn btn-sm btn-outline-secondary">View</a>
                                        <a href="{{ route('workouts.edit', $w) }}"
                                            class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('workouts.destroy', $w) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Delete this workout?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
@endsection