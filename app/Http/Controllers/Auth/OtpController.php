<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class OtpController extends Controller
{
    // ---------------------------------------------------------------
    //  Show the OTP verification form
    // ---------------------------------------------------------------
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('otp_email')) {
            return redirect()->route('login');
        }

        $intent = $request->session()->get('otp_intent', 'login');

        return view('auth.otp', [
            'email'  => $request->session()->get('otp_email'),
            'intent' => $intent,                      // 'login' | 'register'
        ]);
    }

    // ---------------------------------------------------------------
    //  Verify the OTP — handles both login and register intents
    // ---------------------------------------------------------------
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $intent = $request->session()->get('otp_intent', 'login');

        if ($intent === 'register') {
            return $this->verifyRegister($request);
        }

        return $this->verifyLogin($request);
    }

    // ---------------------------------------------------------------
    //  Verify LOGIN OTP
    // ---------------------------------------------------------------
    private function verifyLogin(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('otp_user_id');

        if (! $userId) {
            return redirect()->route('login')
                ->withErrors(['otp' => 'Session expired. Please log in again.']);
        }

        $user = User::find($userId);

        if (! $user) {
            return redirect()->route('login')->withErrors(['otp' => 'User not found.']);
        }

        // Check expiry
        if (! $user->otp_expires_at || now()->isAfter($user->otp_expires_at)) {
            $request->session()->forget(['otp_user_id', 'otp_email', 'otp_intent', 'otp_remember']);
            return redirect()->route('login')
                ->withErrors(['email' => 'OTP expired. Please log in again.']);
        }

        // Check code
        if ($request->otp !== $user->otp_code) {
            return back()->withErrors(['otp' => 'Invalid code. Please try again.']);
        }

        // Clear OTP and log in
        $user->update(['otp_code' => null, 'otp_expires_at' => null]);
        $request->session()->forget(['otp_user_id', 'otp_email', 'otp_intent', 'otp_remember']);

        Auth::login($user, $request->session()->get('otp_remember', false));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    // ---------------------------------------------------------------
    //  Verify REGISTER OTP — creates the account after verification
    // ---------------------------------------------------------------
    private function verifyRegister(Request $request): RedirectResponse
    {
        $code      = $request->session()->get('otp_code');
        $expiresAt = $request->session()->get('otp_expires_at');

        // Check expiry
        if (! $expiresAt || now()->timestamp > $expiresAt) {
            $request->session()->forget([
                'otp_intent', 'otp_email', 'otp_code', 'otp_expires_at',
                'otp_register_name', 'otp_register_email', 'otp_register_password',
            ]);
            return redirect()->route('register')
                ->withErrors(['email' => 'OTP expired. Please register again.']);
        }

        // Check code
        if ($request->otp !== $code) {
            return back()->withErrors(['otp' => 'Invalid code. Please try again.']);
        }

        // Create the user now that email is verified
        $user = User::create([
            'name'     => $request->session()->get('otp_register_name'),
            'email'    => $request->session()->get('otp_register_email'),
            'password' => $request->session()->get('otp_register_password'),
            'role'     => 'staff',
        ]);

        // Clear session
        $request->session()->forget([
            'otp_intent', 'otp_email', 'otp_code', 'otp_expires_at',
            'otp_register_name', 'otp_register_email', 'otp_register_password',
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Welcome! Your account has been verified and created.');
    }

    // ---------------------------------------------------------------
    //  Resend OTP
    // ---------------------------------------------------------------
    public function resend(Request $request): RedirectResponse
    {
        $intent = $request->session()->get('otp_intent', 'login');

        if ($intent === 'register') {
            // Re-generate a fresh code for the pending registration
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $request->session()->put('otp_code',       $code);
            $request->session()->put('otp_expires_at', now()->addMinutes(10)->timestamp);

            $tempUser        = new User();
            $tempUser->name  = $request->session()->get('otp_register_name', 'User');
            $tempUser->email = $request->session()->get('otp_register_email');

            static::sendRegistrationOtp($tempUser, $code);
        } else {
            $userId = $request->session()->get('otp_user_id');
            $user   = $userId ? User::find($userId) : null;

            if ($user) {
                static::sendOtp($user);
            }
        }

        return back()->with('status', 'A new code has been sent to ' . $request->session()->get('otp_email') . '.');
    }

    // ---------------------------------------------------------------
    //  Email helper: LOGIN OTP (sent to existing user)
    // ---------------------------------------------------------------
    public static function sendOtp(User $user): void
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp_code'       => $code,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        static::dispatchOtpEmail($user->email, $user->name, $code, 'Sign-In Verification');
    }

    // ---------------------------------------------------------------
    //  Email helper: REGISTRATION OTP (user not in DB yet)
    // ---------------------------------------------------------------
    public static function sendRegistrationOtp(User $user, string $code): void
    {
        static::dispatchOtpEmail($user->email, $user->name, $code, 'Email Verification – Complete Registration');
    }

    // ---------------------------------------------------------------
    //  Shared email sender
    // ---------------------------------------------------------------
    private static function dispatchOtpEmail(string $email, string $name, string $code, string $subject): void
    {
        Mail::send([], [], function ($message) use ($email, $name, $code, $subject) {
            $message->to($email, $name)
                ->subject($subject . ' — Gym Inventory System')
                ->html('<!DOCTYPE html>
<html><body style="font-family:sans-serif;background:#f5f5f5;padding:30px;">
<div style="max-width:480px;margin:auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 2px 8px rgba(0,0,0,.08);">
    <div style="text-align:center;margin-bottom:24px;">
        <span style="font-size:40px;">🏋️</span>
        <h2 style="color:#dc3545;margin:8px 0 4px;">Gym Inventory System</h2>
        <p style="color:#666;margin:0;">' . e($subject) . '</p>
    </div>
    <p style="color:#333;">Hi <strong>' . e($name) . '</strong>,</p>
    <p style="color:#555;">Use the code below to continue. It expires in <strong>10 minutes</strong>.</p>
    <div style="background:#f8f9fa;border:2px dashed #dc3545;border-radius:8px;text-align:center;padding:20px;margin:24px 0;">
        <span style="font-size:40px;font-weight:700;letter-spacing:12px;color:#dc3545;">' . $code . '</span>
    </div>
    <p style="color:#888;font-size:13px;">If you did not request this, ignore this email.</p>
    <hr style="border:none;border-top:1px solid #eee;margin:20px 0;">
    <p style="color:#aaa;font-size:12px;text-align:center;">Gym Inventory System &bull; Secure Access</p>
</div>
</body></html>');
        });
    }
}
