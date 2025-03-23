<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth\LoginController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ContactController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\CartController; // for cart
use App\Http\Controllers\ProductController; //for product


// ✅ Authentication Routes (Including Email Verification)
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
Route::middleware(['auth'])->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'show'])->name('user.profile');
    Route::get('/user/profile/edit', [UserProfileController::class, 'edit'])->name('user.profile.edit');
    Route::put('/user/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
    Route::delete('/user/profile', [UserProfileController::class, 'destroy'])->name('user.profile.destroy');
    Route::put('/user/profile/password', [UserProfileController::class, 'updatePassword'])->name('user.profile.password.update');

    // ✅ Redirect to login when attempting to access cart (not logged in)
    Route::get('/cart', function () {
        return redirect()->route('login')->with('message', 'Please log in to access your cart.');
    })->name('cart');
});

// ✅ Email Verification Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/dashboard');
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/resend', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('resent', true);
    })->middleware(['throttle:6,1'])->name('verification.resend');
});

// ✅ Dashboard Routes
Route::get('/dashboard', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ✅ Admin Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');
});

// ✅ Seller Dashboard Routes
// Route::middleware(['auth', 'verified', 'seller'])->group(function () {
//    Route::get('/seller/dashboard', function () {
  //      return view('seller.dashboard');
    //})->name('seller.dashboard');
//});

Route::middleware(['auth', 'seller'])->group(function () {
    Route::get('/seller/dashboard', [SellerController::class, 'index'])->name('seller.dashboard');
});


// ✅ OTP Verification Routes
Route::get('/verify-otp', [App\Http\Controllers\Auth\LoginController::class, 'showOtpForm'])
    ->name('verify.otp.form');

Route::post('/verify-otp', [App\Http\Controllers\Auth\LoginController::class, 'verifyOtp'])
    ->name('verify.otp');
   
// for cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/remove/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
    

 //product  


// Product Catalog (Customers should see this)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

// Seller Product Creation Page
Route::get('/seller/create_product', [ProductController::class, 'create'])->name('seller.create_product');
Route::post('/seller/create_product', [ProductController::class, 'store'])->name('seller.store_product');

//Seller routes


Route::get('/seller/dashboard', [SellerController::class, 'dashboard'])->name('seller.dashboard');
Route::get('/seller/about', [SellerController::class, 'about'])->name('seller.about');
Route::get('/seller/contact', [SellerController::class, 'contact'])->name('seller.contact');


// Seller Product Management

Route::get('/seller/products', [ProductController::class, 'sellerProducts'])->name('seller.products');
Route::get('/seller/product/{id}/edit', [ProductController::class, 'edit'])->name('seller.edit_product');
Route::delete('/seller/product/{id}', [ProductController::class, 'destroy'])->name('seller.delete_product');


//Route::middleware(['auth', 'verified', 'seller'])->group(function () {
  
//});

// Seller Edit Product Page
Route::get('/seller/edit_product/{id}', [ProductController::class, 'edit'])->name('seller.edit_product');

// Seller Update Product
Route::put('/seller/update_product/{id}', [ProductController::class, 'update'])->name('seller.update_product');




