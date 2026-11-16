<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - Gym Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body class="login-page">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
        <div class="row w-100 justify-content-center">
            <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                <div class="card shadow-lg border-0 rounded-4 login-card">
                    <div class="card-header bg-transparent border-0 text-center pt-4 pb-2">
                        <div class="stat-icon bg-info text-white rounded-circle mx-auto mb-3 shadow" style="width: 64px; height: 64px; font-size: 1.8rem;">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Set New Password</h3>
                        <p class="text-muted small">Enter your email and chosen new password below.</p>
                    </div>

                    <div class="card-body px-4 py-3">
                        <form method="POST" action="{{ route('password.store') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <!-- Email Address -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold small">
                                    <i class="bi bi-envelope me-1 text-primary"></i>Email Address
                                </label>
                                <input type="email"
                                       class="form-control form-control-lg fs-6 @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $request->email) }}"
                                       required
                                       autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold small">
                                    <i class="bi bi-lock me-1 text-primary"></i>New Password
                                </label>
                                <input type="password"
                                       class="form-control form-control-lg fs-6 @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       required
                                       autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label fw-semibold small">
                                    <i class="bi bi-shield-check me-1 text-primary"></i>Confirm Password
                                </label>
                                <input type="password"
                                       class="form-control form-control-lg fs-6"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       required
                                       autocomplete="new-password">
                            </div>

                            <!-- Submit -->
                            <div class="d-grid gap-2 mb-3 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg fs-6 fw-semibold py-2">
                                    <i class="bi bi-check2-circle me-1"></i>Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
