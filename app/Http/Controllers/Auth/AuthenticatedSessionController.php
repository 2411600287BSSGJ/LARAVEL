<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     * Validates credentials, then sends OTP instead of logging in directly.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Validate credentials without actually logging the user in
        $request->authenticate();

        // Get the authenticated user then immediately log them out
        $user = Auth::user();
        Auth::logout();

        // Store user info in session for OTP step
        $request->session()->put('otp_user_id', $user->id);
        $request->session()->put('otp_email',   $user->email);
        $request->session()->put('otp_remember', $request->boolean('remember'));

        // Send the 6-digit OTP via Resend email
        OtpController::sendOtp($user);

        return redirect()->route('otp.show');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
