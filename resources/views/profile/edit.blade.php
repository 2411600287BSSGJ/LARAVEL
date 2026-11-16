@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <!-- Page Header -->
    <div class="dashboard-heading d-flex justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="eyebrow mb-2">Account</p>
            <h1 class="fw-bold mb-1">Profile</h1>
            <p class="text-muted mb-0">Manage your name, email address, and account security.</p>
        </div>
    </div>

    <!-- Success Alert -->
    @if (session('status') === 'profile-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Profile updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Email Verification Warning -->
    @if (!$user->hasVerifiedEmail())
        <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-3" role="alert">
            <span>Your email address is not verified. Verify it to access inventory features.</span>
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="btn btn-warning btn-sm fw-bold" type="submit">Resend Verification Email</button>
            </form>
        </div>
    @endif

    <!-- Profile Info Panel -->
    <section class="panel mb-4">
        <div class="panel-heading mb-3">
            <h2>Profile Information</h2>
        </div>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="name">Name</label>
                    <input class="form-control" id="name" name="name" type="text" maxlength="255" required
                        value="{{ old('name', $user->name) }}" autocomplete="name">
                    @error('name')
                    <div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="email">Email Address</label>
                    <input class="form-control" id="email" name="email" type="email" maxlength="255" required
                        value="{{ old('email', $user->email) }}" autocomplete="email">
                    @error('email')
                    <div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <small class="text-muted mt-1 d-block">Changing your email will require re-verification.</small>
                </div>
                <div class="col-12 mt-4 pt-3 border-top">
                    <button class="btn btn-primary px-4 fw-bold" type="submit">Save Profile</button>
                </div>
            </div>
        </form>
    </section>

    <!-- Password Panel -->
    <section class="panel mb-4">
        <div class="panel-heading mb-3">
            <h2>Change Password</h2>
        </div>
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold" for="current_password">Current Password</label>
                    <input class="form-control" id="current_password" name="current_password" type="password" required
                        autocomplete="current-password">
                    @error('current_password')
                    <div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="password">New Password</label>
                    <input class="form-control" id="password" name="password" type="password" required
                        autocomplete="new-password">
                    @error('password')
                    <div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="password_confirmation">Confirm New Password</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password"
                        required autocomplete="new-password">
                </div>
                <div class="col-12 mt-4 pt-3 border-top">
                    <button class="btn btn-outline-primary px-4 fw-bold" type="submit">Update Password</button>
                </div>
            </div>
        </form>
    </section>
@endsection