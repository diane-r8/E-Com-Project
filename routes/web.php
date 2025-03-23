<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Storage;
use App\Http\Middleware\SellerMiddleware;
use App\Http\Controllers\SellerController;


Route::get('/faq', function () {
    return view('faq');
})->name('faq');

Route::get('/shipping-returns', function () {
    return view('shipping_returns');
})->name('shipping-returns');

Route::get('/privacy-policy', function () {
    return view('privacy_policy');
})->name('privacy-policy');

Route::get('/terms-conditions', function () {
    return view('terms_conditions');
})->name('terms-conditions');
// ✅ Authentication Routes
Auth::routes(['verify' => true]);

// ✅ Email Verification Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', fn() => view('auth.verify'))->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/dashboard');
    })->middleware(['signed'])->name('verification.verify');
    Route::post('/email/resend', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('resent', true);
    })->middleware(['throttle:6,1'])->name('verification.resend');
});

// ✅ Public Pages (No Authentication Required)
Route::get('/', fn() => view('home'))->name('home');
Route::get('/about', fn() => view('about'))->name('about');
Route::get('/terms', fn() => view('terms'))->name('terms');
Route::get('/contact', fn() => view('contact'))->name('contact');
Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

// ✅ Redirect after login
Route::get('/dashboard', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ✅ User Profile Routes (Requires Authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'show'])->name('user.profile');
    Route::get('/user/profile/edit', [UserProfileController::class, 'edit'])->name('user.profile.edit');
    Route::put('/user/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
    Route::delete('/user/profile', [UserProfileController::class, 'destroy'])->name('user.profile.destroy');
    Route::put('/user/profile/password', [UserProfileController::class, 'updatePassword'])->name('user.profile.password.update');
});

// ✅ Admin Dashboard Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

// ✅ Seller Dashboard Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/seller/dashboard', [SellerController::class, 'dashboard'])->name('seller.dashboard');
    // ✅ Seller Product Management
    Route::get('/seller/create_product', [ProductController::class, 'create'])->name('seller.create_product');
    Route::post('/seller/create_product', [ProductController::class, 'store'])->name('seller.store_product');
    Route::get('/seller/products', [ProductController::class, 'sellerProducts'])->name('seller.products');   
    Route::get('/seller/products/{id}/edit', [ProductController::class, 'edit'])->name('seller.edit_product');
    Route::put('/seller/products/{id}/update', [ProductController::class, 'update'])->name('seller.update_product');
    Route::delete('/seller/products/{id}/delete', [ProductController::class, 'destroy'])->name('seller.delete_product');
});

// ✅ OTP Verification
Route::get('/verify-otp', [App\Http\Controllers\Auth\LoginController::class, 'showOtpForm'])->name('verify.otp.form');
Route::post('/verify-otp', [App\Http\Controllers\Auth\LoginController::class, 'verifyOtp'])->name('verify.otp');

// ✅ Cart Routes (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    Route::post('/cart/add/{id}', [CartController::class, 'addToCart'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/remove/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/update/{id}', [CartController::class, 'updateCart'])->name('cart.update');
    Route::post('/cart/remove-multiple', [CartController::class, 'removeMultiple'])->name('cart.removeMultiple');

    // ✅ Checkout Route
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');
});






