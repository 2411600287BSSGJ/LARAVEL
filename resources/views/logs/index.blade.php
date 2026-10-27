@extends('layouts.app')

@section('content')
    <div class="row">
        @include('partials.sidebar')
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <div class="panel-heading mb-3">
                    <h2>System Activity Logs</h2>
                </div>
                <div class="table-responsive">
                    <table class="table inventory-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Workout</th>
                                <th>Action</th>
                                <th>Minutes Δ</th>
                                <th>Calories Δ</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                                    <td>{{ $log->workout->name ?? 'Deleted' }}</td>
                                    <td><span class="badge bg-primary">{{ ucfirst($log->type) }}</span></td>
                                    <td class="{{ $log->minutes_changed > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $log->minutes_changed > 0 ? '+' : '' }}{{ $log->minutes_changed }}
                                    </td>
                                    <td class="{{ $log->calories_changed > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $log->calories_changed > 0 ? '+' : '' }}{{ $log->calories_changed }}
                                    </td>
                                    <td>{{ $log->user->name ?? 'System' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $logs->links() }}</div>
            </div>
        </main>
    </div>
@endsection