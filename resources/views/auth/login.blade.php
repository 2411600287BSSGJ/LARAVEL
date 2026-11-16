<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gym Inventory System</title>
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
                        <div class="stat-icon bg-danger text-white rounded-circle mx-auto mb-3 shadow" style="width: 64px; height: 64px; font-size: 1.8rem;">
                            <i class="bi bi-activity"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Gym Inventory</h3>
                        <p class="text-muted small">Sign in to access your gym stock management dashboard</p>
                    </div>

                    <div class="card-body px-4 py-3">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show small mb-3" role="alert">
                                {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold small">
                                    <i class="bi bi-envelope me-1 text-primary"></i>Email Address
                                </label>
                                <input type="email"
                                       class="form-control form-control-lg fs-6 @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', 'admin@gym.com') }}"
                                       required
                                       autofocus
                                       autocomplete="username">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="password" class="form-label fw-semibold small mb-0">
                                        <i class="bi bi-lock me-1 text-primary"></i>Password
                                    </label>
                                    @if (Route::has('password.request'))
                                        <a class="text-decoration-none small text-muted" href="{{ route('password.request') }}">
                                            Forgot?
                                        </a>
                                    @endif
                                </div>
                                <input type="password"
                                       class="form-control form-control-lg fs-6 @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       required
                                       autocomplete="current-password"
                                       value="password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Remember Me -->
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small text-muted" for="remember">Keep me logged in</label>
                            </div>

                            <!-- Submit -->
                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary btn-lg fs-6 fw-semibold py-2">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
                                </button>
                            </div>

                            <!-- Test Credentials Box -->
                            <div class="p-2 bg-light border rounded text-center small text-muted mb-2">
                                <strong>Default Test Account:</strong><br>
                                <code>admin@gym.com</code> / <code>password</code>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer bg-transparent border-0 text-center pb-4 pt-0">
                        <span class="text-muted small">Need an account?</span>
                        <a href="{{ route('register') }}" class="text-decoration-none small fw-semibold text-primary ms-1">Register here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
