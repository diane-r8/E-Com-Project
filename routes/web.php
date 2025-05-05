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
use App\Http\Controllers\XenditController;
use App\Models\ProductVariation;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\PaymentController;

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

// ✅ Public Pages (Viewable Without Login)
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

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

/**✅ Authentication Routes (Including Email Verification)
Auth::routes(['verify' => true]);
**/

// ✅ Public Pages (Viewable Without Login)
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

Route::post('/contact/send', [ContactController::class, 'send'])->name('contact.send');


// ✅ Redirect to login if user is not authenticated (for protected pages)
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

Route::post('/seller/product/{id}/adjust-stock', [ProductController::class, 'adjustStock'])->name('seller.adjust_stock');

//Public Pages (No Authentication Required)
Route::get('/', fn() => view('home'))->name('home');
Route::get('/about', fn() => view('about'))->name('about');
Route::get('/terms', fn() => view('terms'))->name('terms');
Route::get('/contact', fn() => view('contact'))->name('contact');
Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

//Redirect after login
Route::get('/dashboard', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

//User Profile Routes (Requires Authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'show'])->name('user.profile');
    Route::get('/user/profile/edit', [UserProfileController::class, 'edit'])->name('user.profile.edit');
    Route::put('/user/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
    Route::delete('/user/profile', [UserProfileController::class, 'destroy'])->name('user.profile.destroy');
    Route::put('/user/profile/password', [UserProfileController::class, 'updatePassword'])->name('user.profile.password.update');
});

//Admin Dashboard Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

//Seller Dashboard Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/seller/dashboard', [SellerController::class, 'dashboard'])->name('seller.dashboard');

    //Seller Product Management
    Route::get('/seller/create_product', [ProductController::class, 'create'])->name('seller.create_product');
    Route::post('/seller/create_product', [ProductController::class, 'store'])->name('seller.store_product');
    Route::get('/seller/products', [ProductController::class, 'sellerProducts'])->name('seller.products');   
    Route::get('/seller/products/{id}/edit', [ProductController::class, 'edit'])->name('seller.edit_product');
    Route::put('/seller/products/{id}/update', [ProductController::class, 'update'])->name('seller.update_product');
    Route::delete('/seller/products/{id}/delete', [ProductController::class, 'destroy'])->name('seller.delete_product');
    Route::get('/seller/variations/{id}/edit', [SellerController::class, 'editVariation'])->name('seller.edit_variation');
Route::post('/seller/variations/{id}/update', [SellerController::class, 'updateVariation'])->name('seller.update_variation');
Route::post('/seller/product/{id}/adjust-stock', [SellerController::class, 'adjustProductStock']); Route::post('/seller/variation/{id}/adjust-stock', [SellerController::class, 'adjustVariationStock']);

});

//OTP Verification
Route::get('/verify-otp', [App\Http\Controllers\Auth\LoginController::class, 'showOtpForm'])->name('verify.otp.form');
Route::post('/verify-otp', [App\Http\Controllers\Auth\LoginController::class, 'verifyOtp'])->name('verify.otp');

// OTP Verification with SocialAuthController
Route::get('/social-verify-otp', [App\Http\Controllers\SocialAuthController::class, 'showVerifyForm'])->name('social.verify.otp.form');
Route::post('/social-verify-otp', [App\Http\Controllers\SocialAuthController::class, 'verifyOTP'])->name('social.verify.otp');


//Cart Routes (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    Route::match(['get', 'post'], '/cart/add/{id}', [CartController::class, 'addToCart'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/remove/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/update/{id}', [CartController::class, 'updateCart'])->name('cart.update');
    Route::post('/cart/remove-multiple', [CartController::class, 'removeMultiple'])->name('cart.removeMultiple');
});

//Product Search
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');

//Social Media Login
Route::get('auth/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// Checkout Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'checkout'])->name('checkout'); // Shows the checkout page
    Route::post('/placeOrder', [CheckoutController::class, 'placeOrder'])->name('placeOrder'); // Handles checkout form submission
});

// POST route for payment processing (separate from checkout)

// Payment routes
//Route::post('/payment', [PaymentController::class, 'process'])->name('payment.process');
//Route::get('/payment/choose/{order_id}', [PaymentController::class, 'choose'])->name('payment.choose');

// Route::post('/pay-with-gcash', [XenditController::class, 'createPayment']);
Route::get('/payment-success', function () {
    return 'Payment successful!';
})->name('payment.success');
Route::get('/payment-failed', function () {
    return 'Payment failed!';
})->name('payment.failed');



// Xendit payment routes
Route::post('/pay-with-gcash', [XenditController::class, 'payWithGCash'])->name('pay.gcash');
Route::post('/webhooks/xendit', [XenditController::class, 'webhook'])->name('xendit.webhook');


//Route::get('/order/success/{order_id}', [CheckoutController::class, 'orderSuccess'])->name('order.success');

// Checkout routes
Route::get('/checkout', [CheckoutController::class, 'checkout'])->name('checkout')->middleware('auth');
Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.place-order')->middleware('auth');
Route::get('/order/success/{order_id}', [CheckoutController::class, 'orderSuccess'])->name('order.success')->middleware('auth');
Route::post('/buy-now', [CheckoutController::class, 'buyNow'])->name('buy-now')->middleware('auth');

// Payment routes
Route::get('/payment/callback', [PaymentController::class, 'handleCallback'])->name('payment.callback');
   // Controller for Checkout
   // Route::get('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
   // Route::post('/place-order', [CheckoutController::class, 'placeOrder'])->name('placeOrder');
// New route specifically for Buy Now checkout
Route::post('/buy-now/checkout', [CheckoutController::class, 'processBuyNow'])->name('processBuyNow')->middleware('auth');

// Add this to web.php
Route::get('/payment/process/{order_id}', [PaymentController::class, 'processPayment'])->name('payment.process');

