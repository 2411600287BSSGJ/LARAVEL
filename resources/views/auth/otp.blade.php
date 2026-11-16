<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $intent === 'register' ? 'Verify Email' : 'Verify Code' }} - Gym Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <style>
        .otp-icon { width: 72px; height: 72px; font-size: 2rem; }
        .digit-boxes { display: flex; gap: 8px; justify-content: center; }
        .digit-box {
            width: 48px; height: 60px; font-size: 1.6rem; font-weight: 700;
            text-align: center; border: 2px solid #dee2e6; border-radius: 8px;
            outline: none; transition: border-color .2s;
        }
        .digit-box:focus { border-color: #dc3545; box-shadow: 0 0 0 3px rgba(220,53,69,.15); }
    </style>
</head>
<body class="login-page">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
        <div class="row w-100 justify-content-center">
            <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                <div class="card shadow-lg border-0 rounded-4 login-card">

                    {{-- Header --}}
                    <div class="card-header bg-transparent border-0 text-center pt-4 pb-2">
                        <div class="stat-icon {{ $intent === 'register' ? 'bg-primary' : 'bg-danger' }} text-white rounded-circle mx-auto mb-3 shadow otp-icon">
                            <i class="bi bi-{{ $intent === 'register' ? 'envelope-check-fill' : 'shield-lock-fill' }}"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">
                            {{ $intent === 'register' ? 'Verify Your Email' : 'Verify Your Identity' }}
                        </h3>
                        <p class="text-muted small mb-0">
                            @if($intent === 'register')
                                We sent a verification code to confirm your email.<br>
                            @else
                                A sign-in code was sent to<br>
                            @endif
                            <strong class="text-dark">{{ $email }}</strong>
                        </p>
                        @if($intent === 'register')
                            <span class="badge bg-primary-subtle text-primary mt-2">
                                <i class="bi bi-person-plus me-1"></i>Account will be created after verification
                            </span>
                        @endif
                    </div>

                    <div class="card-body px-4 py-3">

                        {{-- Status / success messages --}}
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show small mb-3" role="alert">
                                <i class="bi bi-check-circle-fill me-1"></i>{{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- Error messages --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show small mb-3" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                {{ $errors->first() }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('otp.verify') }}" id="otpForm">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-semibold small d-block text-center mb-3">
                                    <i class="bi bi-123 me-1 text-{{ $intent === 'register' ? 'primary' : 'danger' }}"></i>Enter 6-Digit Code
                                </label>

                                {{-- Individual digit boxes --}}
                                <div class="digit-boxes mb-2" id="digitBoxes">
                                    @for($i = 1; $i <= 6; $i++)
                                        <input type="text"
                                               class="digit-box"
                                               maxlength="1"
                                               pattern="[0-9]"
                                               inputmode="numeric"
                                               autocomplete="off"
                                               data-index="{{ $i }}"
                                               id="digit{{ $i }}">
                                    @endfor
                                </div>

                                {{-- Hidden full OTP field --}}
                                <input type="hidden" name="otp" id="otpHidden">
                                @error('otp')
                                    <p class="text-danger text-center small mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="d-grid gap-2 mb-3">
                                <button type="submit"
                                        class="btn btn-{{ $intent === 'register' ? 'primary' : 'danger' }} btn-lg fs-6 fw-semibold py-2"
                                        id="verifyBtn" disabled>
                                    <i class="bi bi-{{ $intent === 'register' ? 'person-check' : 'shield-check' }} me-1"></i>
                                    {{ $intent === 'register' ? 'Verify & Create Account' : 'Verify & Sign In' }}
                                </button>
                            </div>
                        </form>

                        {{-- Countdown + Resend --}}
                        <div class="text-center">
                            <p class="text-muted small mb-1">
                                Code expires in <strong id="countdown" class="text-{{ $intent === 'register' ? 'primary' : 'danger' }}">10:00</strong>
                            </p>
                            <form method="POST" action="{{ route('otp.resend') }}">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0 small" id="resendBtn" disabled>
                                    <i class="bi bi-arrow-clockwise me-1"></i>Resend Code
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent border-0 text-center pb-4 pt-0">
                        <a href="{{ $intent === 'register' ? route('register') : route('login') }}"
                           class="text-decoration-none small text-muted">
                            <i class="bi bi-arrow-left me-1"></i>
                            {{ $intent === 'register' ? 'Back to Registration' : 'Back to Login' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- Digit box navigation ---
        const boxes  = document.querySelectorAll('.digit-box');
        const hidden = document.getElementById('otpHidden');
        const btn    = document.getElementById('verifyBtn');

        function updateHidden() {
            const code = [...boxes].map(b => b.value).join('');
            hidden.value = code;
            btn.disabled = code.length < 6;
        }

        boxes.forEach((box, idx) => {
            box.addEventListener('input', () => {
                box.value = box.value.replace(/\D/g, '').slice(-1);
                updateHidden();
                if (box.value && idx < boxes.length - 1) boxes[idx + 1].focus();
            });
            box.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !box.value && idx > 0) boxes[idx - 1].focus();
            });
            box.addEventListener('paste', e => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData)
                    .getData('text').replace(/\D/g, '').slice(0, 6);
                [...pasted].forEach((ch, i) => { if (boxes[i]) boxes[i].value = ch; });
                updateHidden();
                boxes[Math.min(pasted.length, 5)].focus();
            });
        });

        // Auto-submit when all 6 digits entered
        document.getElementById('otpForm').addEventListener('input', () => {
            if ([...boxes].map(b => b.value).join('').length === 6) {
                setTimeout(() => document.getElementById('otpForm').submit(), 200);
            }
        });

        boxes[0].focus();

        // --- Countdown timer (10 minutes) ---
        let seconds = 600;
        const countdownEl = document.getElementById('countdown');
        const resendBtn   = document.getElementById('resendBtn');

        function tick() {
            if (seconds <= 0) {
                countdownEl.textContent = 'Expired';
                resendBtn.disabled = false;
                return;
            }
            const m = String(Math.floor(seconds / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            countdownEl.textContent = `${m}:${s}`;
            seconds--;
            setTimeout(tick, 1000);
        }

        setTimeout(() => { resendBtn.disabled = false; }, 60000); // allow resend after 60s
        tick();
    </script>
</body>
</html>
