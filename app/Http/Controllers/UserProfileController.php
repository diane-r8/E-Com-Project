<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserProfileController extends Controller
{
    // Show the user's profile
    public function show()
    {
        $user = auth()->user()->fresh();

        if (!$user->hasVerifiedEmail()) {
            return redirect('/email/verify');
        }

        // Ensure the user has a profile
        if (!$user->profile) {
            $user->profile()->create(['profile_picture' => null]);
        }

        return view('user.profile', compact('user'));
    }

    // Show the edit form
    public function edit()
    {
        if (!auth()->user()->hasVerifiedEmail()) {
            return redirect('/email/verify');
        }

        return redirect()->route('user.profile')->with('edit_mode', true);
    }

    // Update the user's profile (excluding password)
    public function update(Request $request)
    {
        $user = auth()->user()->load('profile');

        if (!$user->hasVerifiedEmail()) {
            return redirect('/email/verify');
        }

        // Validate the input data
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'gender' => 'nullable|in:male,female,other',
            'profile_picture' => 'nullable|image|max:2048', // 2MB max
            'remove_profile_picture' => 'nullable|boolean',
        ]);

        // Ensure user has a profile record
        if (!$user->profile) {
            $user->profile()->create([]);
        }

                // Handle profile picture removal
        if ($request->has('remove_profile_picture') && $request->remove_profile_picture) {
            if ($user->profile->profile_picture && Storage::exists('public/' . $user->profile->profile_picture)) {
                Storage::delete('public/' . $user->profile->profile_picture);
            }
            $user->profile->update(['profile_picture' => null]);
        }

        // Handle profile picture upload
        elseif ($request->hasFile('profile_picture')) {
            if ($user->profile->profile_picture && Storage::exists('public/' . $user->profile->profile_picture)) {
                Storage::delete('public/' . $user->profile->profile_picture);
            }
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile->update(['profile_picture' => $path]);
        }

        // Update user details in users table
        $user->update([
            'username' => $validated['username'],
            'fname' => $validated['fname'],
            'lname' => $validated['lname'],
            'email' => $validated['email'],
            'two_factor_enabled' => $request->input('two_factor_enabled', 0),
        ]);

        // Update profile details in user_profiles table
        $user->profile->update([
            'gender' => $validated['gender'] ?? null,
        ]);

        // Handle Two-Factor Authentication toggle
        $user->update([
            'user_type_enable' => $request->has('two_factor_enabled') ? 1 : 0,
        ]);

        return redirect()->route('user.profile')->with('success', 'Profile updated successfully!');
    }

    // Update password separately
    public function updatePassword(Request $request)
{
    $user = auth()->user();

    $validated = $request->validate([
        'current_password' => ['required'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    // Check if the current password matches
    if (!Hash::check($validated['current_password'], $user->password)) {
        return redirect()->route('user.profile')
            ->withErrors(['current_password' => 'The current password is incorrect.'])
            ->with('password_mode', true); // Keeps the password form open
    }

    // Update the password
    $user->update([
        'password' => Hash::make($validated['password']),
    ]);

    return redirect()->route('user.profile')->with('success', 'Password changed successfully!');
}

public function destroy()
{
    $user = auth()->user();

    // Ensure the user has a profile and delete profile picture if exists
    if ($user->profile && $user->profile->profile_picture) {
        Storage::delete('public/' . $user->profile->profile_picture);
    }

    // Delete the user profile (if applicable)
    if ($user->profile) {
        $user->profile()->delete();
    }

    // Log out the user before deleting
    Auth::logout();

    // Permanently delete the user (bypass soft delete)
    $user->forceDelete();

    return redirect('/')->with('success', 'Your account has been permanently deleted.');
}

}
