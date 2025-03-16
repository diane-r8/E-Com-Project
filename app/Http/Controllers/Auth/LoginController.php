<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\TwoFactorCode;
use App\Mail\SendOTP;
use Carbon\Carbon;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login (default).
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Handle user authentication and redirect based on user type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated(Request $request, $user)
    {
        // If the user's email is not verified, redirect them to the email verification page
        if (!$user->hasVerifiedEmail()) {
            return redirect('/email/verify');
        }

        // Handle 2FA if enabled
        if ($user->two_factor_enabled) {
            // Generate OTP
            $otp = rand(100000, 999999);
            TwoFactorCode::updateOrCreate(
                ['user_id' => $user->id],
                ['otp' => $otp, 'expires_at' => now()->addMinutes(2)]
            );

            // Send OTP via email
            Mail::to($user->email)->send(new SendOTP($otp));

            // Store in session
            Session::put('2fa:user_id', $user->id);

            // Logout the current session
            Auth::logout();

            return redirect()->route('verify.otp.form')->with('message', 'An OTP has been sent to your email.');
        }

        // Check user type and redirect accordingly
        if ($user->user_type === 'admin') {
            return redirect('/admin/dashboard');
        } elseif ($user->user_type === 'seller') {
            return redirect('/seller/dashboard');
        }

        // Default redirect for all other users to the dashboard
        return redirect('/dashboard');

        dd('Authenticated');
    }

    /**
     * Customize the failed login response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Show the OTP verification form.
     *
     * @return \Illuminate\View\View
     */
    public function showOtpForm()
{
    if (!Session::has('2fa:user_id')) {
        dd('Session not found, redirecting to login');
        return redirect()->route('login');
    }

    return view('auth.verify-otp');
}

    /**
     * Handle OTP verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric'
        ]);

        $userId = Session::get('2fa:user_id');

        if (!$userId) {
            return redirect()->route('login')->withErrors(['otp' => 'Invalid session. Please login again.']);
        }

        $otpRecord = TwoFactorCode::where('user_id', $userId)
                                  ->where('otp', $request->otp)
                                  ->where('expires_at', '>', Carbon::now())
                                  ->first();

        if (!$otpRecord) {
            return redirect()->route('verify.otp.form')->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        // Delete OTP record after successful verification
        $otpRecord->delete();

        // Log in the user
        Auth::loginUsingId($userId);
        Session::forget('2fa:user_id');

        // Redirect the user after successful OTP verification
        $user = Auth::user();
        if ($user->user_type === 'admin') {
            return redirect('/admin/dashboard');
        } elseif ($user->user_type === 'seller') {
            return redirect('/seller/dashboard');
        }

        return redirect('/dashboard')->with('success', 'Login successful.');
    }
}
