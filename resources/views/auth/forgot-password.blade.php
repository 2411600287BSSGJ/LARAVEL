<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Gym Inventory System</title>
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
                        <div class="stat-icon bg-warning text-dark rounded-circle mx-auto mb-3 shadow" style="width: 64px; height: 64px; font-size: 1.8rem;">
                            <i class="bi bi-key-fill"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Reset Password</h3>
                        <p class="text-muted small">Forgot your password? Enter your email to receive a password reset link.</p>
                    </div>

                    <div class="card-body px-4 py-3">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show small mb-3" role="alert">
                                {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <!-- Email Address -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold small">
                                    <i class="bi bi-envelope me-1 text-primary"></i>Email Address
                                </label>
                                <input type="email"
                                       class="form-control form-control-lg fs-6 @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required
                                       autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Submit -->
                            <div class="d-grid gap-2 mb-3 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg fs-6 fw-semibold py-2">
                                    <i class="bi bi-envelope-paper me-1"></i>Send Reset Link
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer bg-transparent border-0 text-center pb-4 pt-0">
                        <a href="{{ route('login') }}" class="text-decoration-none small fw-semibold text-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back to Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
