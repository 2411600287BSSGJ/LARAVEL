@extends('layouts.app')
@section('title', 'Users')

@section('content')
    <div class="row">
        @include('partials.sidebar')

        <main class="col-lg-9 col-xl-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="eyebrow mb-1">Administration</p>
                    <h1 class="fw-bold mb-0">Users &amp; Roles</h1>
                </div>
                <a class="btn btn-outline-success btn-sm" href="{{ route('exports.download', 'users') }}">Export CSV</a>
            </div>

            <div class="panel">
                <div class="table-responsive">
                    <table class="table inventory-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <form class="d-flex gap-2" method="POST" action="{{ route('users.role', $user) }}">
                                            @csrf @method('PATCH')
                                            <select class="form-select form-select-sm" name="role" style="width: 100px;">
                                                <option value="staff" @selected($user->role === 'staff')>Staff</option>
                                                <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                            </select>
                                            <button class="btn btn-sm btn-outline-primary">Save</button>
                                        </form>
                                    </td>
                                    <td>{{ $user->created_at->format('M j, Y') }}</td>
                                    <td>
                                        @if(auth()->id() !== $user->id)
                                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                                onsubmit="return confirm('Archive this user account?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Archive</button>
                                            </form>
                                        @else
                                            <span class="text-muted small fst-italic">Current Account</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </main>
    </div>
@endsection