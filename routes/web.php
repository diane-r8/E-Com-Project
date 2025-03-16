<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminMiddleware;


// ✅ Authentication Routes (Including Email Verification)
Auth::routes(['verify' => true]);

// ✅ Email Verification Routes
Route::middleware(['auth'])->group(function () {
    // Show email verification notice
    Route::get('/email/verify', function () {
        return view('auth.verify');
    })->name('verification.notice');

    // Handle email verification link
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/dashboard'); // Redirect to dashboard after successful verification
    })->middleware(['signed'])->name('verification.verify');

    // Resend verification email
    Route::post('/email/resend', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('resent', true);
    })->middleware(['throttle:6,1'])->name('verification.resend');
});

// ✅ Public Pages (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('home');
    })->name('home');

    Route::get('/about', function () {
        return view('about');
    })->name('about');

    Route::get('/products', function () {
        return view('products');
    })->name('products');

    Route::get('/contact', function () {
        return view('contact');
    })->name('contact');
});

// ✅ Redirect after login
Route::get('/dashboard', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ✅ User Profile Routes (Includes Account Deletion)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'show'])->name('user.profile');
    Route::get('/user/profile/edit', [UserProfileController::class, 'edit'])->name('user.profile.edit');
    Route::put('/user/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
    Route::delete('/user/profile', [UserProfileController::class, 'destroy'])->name('user.profile.destroy');
    Route::put('/user/profile/password', [UserProfileController::class, 'updatePassword'])->name('user.profile.password.update');

});

// ✅ Admin Dashboard Routes
    Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');
});

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');

// ✅ Seller Dashboard Routes
Route::middleware(['auth', 'verified', 'seller'])->group(function () {
    Route::get('/seller/dashboard', function () {
        return view('seller.dashboard');
    })->name('seller.dashboard');
});

Route::get('/verify-otp', [App\Http\Controllers\Auth\LoginController::class, 'showOtpForm'])
    ->name('verify.otp.form');

Route::post('/verify-otp', [App\Http\Controllers\Auth\LoginController::class, 'verifyOtp'])
    ->name('verify.otp');





