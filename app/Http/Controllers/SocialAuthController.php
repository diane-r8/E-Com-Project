<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\TwoFactorCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the provider (Google/Facebook).
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from Google/Facebook.
     */
    public function callback($provider)
    {
        try {
            // Get user details from the provider
            $socialUser = Socialite::driver($provider)->user();

            // Check if user already exists
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                // Split full name into first name and last name
                $nameParts = explode(' ', $socialUser->getName(), 2);
                $fname = $nameParts[0];
                $lname = isset($nameParts[1]) ? $nameParts[1] : 'Not Provided';

                // Create a unique username
                $username = Str::slug($socialUser->getName());

                // Handle duplicate usernames
                if (User::where('username', $username)->exists()) {
                    $username .= '-' . Str::random(3);
                }

                // ✅ Create a new user
                $user = User::create([
                    'fname' => $fname,
                    'lname' => $lname,
                    'username' => $username,
                    'email' => $socialUser->getEmail(),
                    'password' => bcrypt(Str::random(16)), // Random password
                    'user_type' => 'buyer', // Default to buyer role
                ]);

                // ✅ Create a profile in user_profiles table
                $user->profile()->create([
                    'profile_picture' => $socialUser->getAvatar()
                        ? $socialUser->getAvatar()
                        : 'default-profile.jpg',
                ]);
            }

            // ✅ Log the user in
            Auth::login($user);

            // ✅ Refresh user to reload updated attributes (including two_factor_enabled)
            $user = $user->fresh();

            // ✅ Check if 2FA is enabled for the user
            if ($user->two_factor_enabled) {
                // Generate and send the OTP using existing logic
                $this->generateAndSendOTP($user);

                // ✅ Redirect to the OTP verification page
                return redirect()->route('social.verify.otp.form')->with('success', 'Please enter the OTP sent to your email.');
            }

            // If 2FA is not enabled, redirect to the user profile
            return redirect()->route('user.profile')->with('success', 'Logged in successfully!');
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Failed to log in using social account.');
        }
    }

    /**
     * Generate and send OTP using the existing 2FA logic.
     */
    private function generateAndSendOTP($user)
    {
        // Delete any previous OTPs for the user
        TwoFactorCode::where('user_id', $user->id)->delete();

        // Generate a random 6-digit OTP
        $otpCode = rand(100000, 999999);

        // Create a new OTP record in the database
        TwoFactorCode::create([
            'user_id' => $user->id,
            'otp' => $otpCode,
            'expires_at' => now()->addMinutes(2),
        ]);

        // Send the OTP to the user's email
        Mail::send('emails.otp', ['otp' => $otpCode, 'expires_in' => 2], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Your OTP Code');
        });
    }

    /**
     * Show OTP verification form.
     */
    public function showVerifyForm()
    {
        return view('auth.verify-otp');
    }

    /**
     * Verify the OTP entered by the user.
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        // Get the logged-in user
        $user = Auth::user();

        // Check if OTP exists and is valid
        $twoFactorCode = TwoFactorCode::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if ($twoFactorCode) {
            // ✅ OTP is valid, delete after successful verification
            $twoFactorCode->delete();

            // ✅ Redirect to the user profile after verification
            return redirect()->route('user.profile')->with('success', '2FA verification successful!');
        }

        // ❌ Invalid OTP, show error
        return redirect()->route('social.verify.otp.form')->with('error', 'Invalid or expired OTP. Please try again.');

    }
}
