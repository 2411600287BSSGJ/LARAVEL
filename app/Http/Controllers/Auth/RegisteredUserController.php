<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Validate registration form, send OTP to the email,
     * and store pending data in session — do NOT create the user yet.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Generate and send OTP to the given email (temporary user object for the mailer)
        $tempUser       = new User();
        $tempUser->name  = $request->name;
        $tempUser->email = $request->email;

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store everything in session — no DB write yet
        $request->session()->put('otp_intent',          'register');
        $request->session()->put('otp_email',           $request->email);
        $request->session()->put('otp_register_name',   $request->name);
        $request->session()->put('otp_register_email',  $request->email);
        $request->session()->put('otp_register_password', Hash::make($request->password));
        $request->session()->put('otp_code',            $code);
        $request->session()->put('otp_expires_at',      now()->addMinutes(10)->timestamp);

        // Send verification email
        OtpController::sendRegistrationOtp($tempUser, $code);

        return redirect()->route('otp.show');
    }
}
