<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Gym Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body class="login-page">

    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100 justify-content-center">
            <div class="col-11 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                <div class="card shadow-lg border-0 rounded-4 login-card">
                    <div class="card-header bg-transparent border-0 text-center pt-4">
                        <div class="brand-icon mb-3">
                            <img src="{{ asset('js/assets/catsss.png') }}" alt="Logo" class="brand-logo"
                                style="width: 100px;">
                        </div>
                        <h2 class="fw-bold text-primary">Verify Your Email</h2>
                    </div>

                    <div class="card-body px-4 py-3 text-center">
                        <p class="text-muted mb-4">
                            Verify your email address by clicking the link we sent you. If you recently changed your
                            email address, check that inbox for the new verification link.
                        </p>

                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success mb-3">
                                A new verification link has been sent to your email address.
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    Log Out
                                </button>
                            </form>

                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">
                                    Resend Verification Email
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>