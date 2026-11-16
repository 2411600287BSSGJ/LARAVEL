@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="text-uppercase text-muted small fw-bold mb-1">
            <i class="bi bi-activity text-danger me-1"></i>Account Settings
        </p>
        <h1 class="h2 fw-bold mb-1">My Profile</h1>
        <p class="text-muted mb-0">Manage your personal information, password, and account.</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center shadow"
             style="width:56px;height:56px;font-size:1.5rem;">
            <i class="bi bi-person-fill"></i>
        </div>
        <div>
            <div class="fw-bold fs-5">{{ auth()->user()->name }}</div>
            <small class="text-muted">{{ auth()->user()->email }}</small><br>
            <span class="badge bg-{{ auth()->user()->role === 'admin' ? 'danger' : 'secondary' }} text-uppercase">
                {{ auth()->user()->role ?? 'staff' }}
            </span>
        </div>
    </div>
</div>

{{-- Flash success for profile update --}}
@if(session('status') === 'profile-updated')
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>Profile information updated successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('status') === 'password-updated')
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>Password changed successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- ── LEFT COLUMN ── --}}
    <div class="col-lg-8">

        {{-- ── 1. Profile Information ── --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-person-circle text-primary me-2"></i>Profile Information
                </h5>
                <small class="text-muted">Update your name and email address.</small>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            <i class="bi bi-person me-1 text-primary"></i>Full Name
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control form-control-lg @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}"
                               required
                               autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">
                            <i class="bi bi-envelope me-1 text-primary"></i>Email Address
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control form-control-lg @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}"
                               required
                               autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-floppy me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── 2. Change Password ── --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-lock text-warning me-2"></i>Change Password
                </h5>
                <small class="text-muted">Use a strong, unique password to keep your account secure.</small>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">
                            <i class="bi bi-lock me-1 text-secondary"></i>Current Password
                        </label>
                        <input type="password"
                               id="current_password"
                               name="current_password"
                               class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                               autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-semibold">
                            <i class="bi bi-lock-fill me-1 text-warning"></i>New Password
                        </label>
                        <input type="password"
                               id="new_password"
                               name="password"
                               class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                               autocomplete="new-password">
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            <i class="bi bi-shield-check me-1 text-success"></i>Confirm New Password
                        </label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               class="form-control"
                               autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-key me-1"></i>Update Password
                    </button>
                </form>
            </div>
        </div>

    </div>{{-- /col-lg-8 --}}

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-4">

        {{-- ── Account Summary Card ── --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-info-circle text-info me-2"></i>Account Summary
                </h5>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small fw-semibold">Name</span>
                        <span class="fw-semibold text-dark">{{ $user->name }}</span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small fw-semibold">Email</span>
                        <span class="fw-semibold text-dark small">{{ $user->email }}</span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small fw-semibold">Role</span>
                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'secondary' }} text-uppercase">
                            {{ $user->role ?? 'staff' }}
                        </span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small fw-semibold">Email Verified</span>
                        @if($user->email_verified_at)
                            <span class="text-success small"><i class="bi bi-check-circle-fill me-1"></i>Verified</span>
                        @else
                            <span class="text-warning small"><i class="bi bi-exclamation-circle-fill me-1"></i>Not Verified</span>
                        @endif
                    </li>
                    <li class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted small fw-semibold">Member Since</span>
                        <span class="text-dark small">{{ $user->created_at->format('M d, Y') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- ── Delete Account (Staff only — Admins cannot delete themselves) ── --}}
        @if(auth()->user()->role !== 'admin')
        <div class="card shadow-sm border-0 border-danger">
            <div class="card-header bg-danger-subtle border-bottom py-3">
                <h5 class="mb-0 fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Danger Zone
                </h5>
                <small class="text-muted">This action is permanent and cannot be undone.</small>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-3">
                    Deleting your account will permanently remove all your data from the system.
                    Please download any information you wish to keep beforehand.
                </p>
                <button type="button"
                        class="btn btn-outline-danger w-100"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteAccountModal">
                    <i class="bi bi-trash3 me-1"></i>Delete My Account
                </button>
            </div>
        </div>
        @else
        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body p-4 text-center text-muted">
                <i class="bi bi-shield-lock-fill fs-2 text-secondary d-block mb-2"></i>
                <p class="small mb-0 fw-semibold">Admin accounts cannot be self-deleted.</p>
                <p class="small mb-0">Contact another administrator to remove this account.</p>
            </div>
        </div>
        @endif

    </div>{{-- /col-lg-4 --}}
</div>

{{-- ── Delete Account Modal (staff only) ── --}}
@if(auth()->user()->role !== 'admin')
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteAccountModalLabel">
                    <i class="bi bi-trash3-fill me-2"></i>Delete Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                        <span>Are you sure? This cannot be undone.</span>
                    </div>
                    <div class="mb-3">
                        <label for="delete_password" class="form-label fw-semibold">
                            Confirm your password to continue
                        </label>
                        <input type="password"
                               id="delete_password"
                               name="password"
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                               placeholder="Enter your current password"
                               required>
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3 me-1"></i>Yes, Delete My Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Auto-open delete modal if there were validation errors --}}
@if(auth()->user()->role !== 'admin' && $errors->userDeletion->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
    });
</script>
@endif
@endif {{-- end staff-only block --}}

@endsection
