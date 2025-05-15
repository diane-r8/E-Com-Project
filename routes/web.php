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
use App\Http\Controllers\XenditController;
use App\Models\ProductVariation;
use App\Http\Controllers\SellerCartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\PaymentController;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Http\Controllers\SellerDashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SellerReviewController;
use App\Http\Controllers\BuyerOrderController;


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

// Public pages and API routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');

// Redirect after login
Route::get('/dashboard', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// User Profile Routes (Requires Authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'show'])->name('user.profile');
    Route::get('/user/profile/edit', [UserProfileController::class, 'edit'])->name('user.profile.edit');
    Route::put('/user/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
    Route::delete('/user/profile', [UserProfileController::class, 'destroy'])->name('user.profile.destroy');
    Route::put('/user/profile/password', [UserProfileController::class, 'updatePassword'])->name('user.profile.password.update');
    
    // Order Management for Users
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::patch('/orders/{order}/received', [OrderController::class, 'received'])->name('orders.received');
    Route::post('/orders/{order}/rate', [OrderController::class, 'rate'])->name('orders.rate');
    
    // Purchase History
    Route::get('/purchase-history', [BuyerOrderController::class, 'index'])->name('buyer.orders.index');
    Route::get('/orders/{order}', [BuyerOrderController::class, 'show'])->name('buyer.orders.show');
});

// Admin Dashboard Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

// Seller Dashboard Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard with controller
    Route::get('/seller/dashboard', [App\Http\Controllers\SellerDashboardController::class, 'index'])->name('seller.dashboard');
    Route::post('/seller/dashboard/filtered-data', [App\Http\Controllers\SellerDashboardController::class, 'getFilteredData'])->name('seller.dashboard.filtered-data');

    // Seller Product Management - keep ProductController
    Route::get('/seller/create_product', [ProductController::class, 'create'])->name('seller.create_product');
    Route::post('/seller/create_product', [ProductController::class, 'store'])->name('seller.store_product');
    Route::get('/seller/products', [ProductController::class, 'sellerProducts'])->name('seller.products');   
    Route::get('/seller/products/{id}/edit', [ProductController::class, 'edit'])->name('seller.edit_product');
    Route::put('/seller/products/{id}/update', [ProductController::class, 'update'])->name('seller.update_product');
    Route::delete('/seller/products/{id}/delete', [ProductController::class, 'destroy'])->name('seller.delete_product');
    
    // Variation Management Routes
    Route::post('/seller/variations/store', function(Request $request) {
        // Validate the incoming request
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);
        
        // Create the variation
        $variation = new \App\Models\ProductVariation;
        $variation->product_id = $validated['product_id'];
        $variation->name = $validated['name'];
        $variation->price = $validated['price'];
        $variation->stock = $validated['stock'];
        $variation->save();
        
        return redirect()->back()->with('success', 'Variation added successfully!');
    })->name('seller.store_variation');
    
    // Replace SellerController routes with closures
    Route::get('/seller/variations/{id}/edit', function($id) {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        $variation = \App\Models\ProductVariation::findOrFail($id);
        return view('seller.edit_variation', compact('variation'));
    })->name('seller.edit_variation');

    Route::post('/seller/variations/{id}/update', function($id) {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        $request = request();
        $variation = \App\Models\ProductVariation::findOrFail($id);
        $variation->update($request->only(['name', 'price', 'stock']));
        return redirect()->route('seller.products')->with('success', 'Variation updated successfully');
    })->name('seller.update_variation');
    
    // Fix stock adjustment routes to properly handle JSON data
    Route::post('/seller/product/{id}/adjust-stock', function($id) {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $product = \App\Models\Product::findOrFail($id);
        $request = request();
        $data = json_decode($request->getContent(), true);
        
        $product->stock = max(0, $data['stock']);
        $product->save();
        
        // Update availability status
        if ($product->stock == 0) {
            $product->availability = 0;
        } else {
            $product->availability = 1;
        }
        $product->save();
        
        return response()->json([
            'success' => true, 
            'stock' => $product->stock,
            'availability' => $product->availability
        ]);
    });

    Route::post('/seller/variation/{id}/adjust-stock', function($id) {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $variation = \App\Models\ProductVariation::findOrFail($id);
        $request = request();
        $data = json_decode($request->getContent(), true);
        
        $variation->stock = max(0, $data['stock']);
        $variation->save();
        
        return response()->json([
            'success' => true, 
            'stock' => $variation->stock
        ]);
    });
    
    // Add variation delete route
    Route::delete('/seller/variation/delete/{id}', function($id) {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $variation = \App\Models\ProductVariation::findOrFail($id);
        $variation->delete();
        
        return redirect()->route('seller.products')
            ->with('success', 'Variation deleted successfully.');
    });
    
    // Replace bulk delete with closure
    Route::delete('/seller/products/delete-bulk', function() {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $request = request();
        $productIds = $request->input('product_ids', []);
        
        if (empty($productIds)) {
            return redirect()->route('seller.products')
                ->with('error', 'No products selected for deletion.');
        }
        
        \App\Models\Product::whereIn('id', $productIds)->delete();
        
        return redirect()->route('seller.products')
            ->with('success', count($productIds) . ' products deleted successfully.');
    })->name('seller.delete_bulk_products');
    
    // Order Management Routes
    Route::get('/seller/order_management', function() {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $ordersQuery = \App\Models\Order::with(['items.product', 'items.variation'])
            ->orderBy('created_at', 'desc');
            
        // Apply filters if provided
        if (request('status')) {
            $ordersQuery->where('status', request('status'));
        }
        
        if (request('search')) {
            $search = request('search');
            $ordersQuery->where(function($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                      ->orWhere('full_name', 'like', "%{$search}%");
            });
        }
        
        if (request('date_from')) {
            $ordersQuery->whereDate('created_at', '>=', request('date_from'));
        }
        
        if (request('date_to')) {
            $ordersQuery->whereDate('created_at', '<=', request('date_to'));
        }
        
        $orders = $ordersQuery->get();
        
        return view('seller.order_management', compact('orders'));
    })->name('seller.order_management');
    
    // Add missing view order and invoice routes
    // This must come BEFORE the /seller/order/{id} route to avoid conflicts
    Route::get('/seller/invoice/{id}', function($id) {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $order = \App\Models\Order::with(['items.product', 'items.variation'])
            ->findOrFail($id);
        
        return view('seller.order_invoice', compact('order'));
    })->name('seller.generate_invoice');
    
    Route::get('/seller/order/{id}', function($id) {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $order = \App\Models\Order::with(['items.product', 'items.variation'])
            ->findOrFail($id);
        
        return view('seller.view_order', compact('order'));
    })->name('seller.view_order');
    
    // Order status update routes
    Route::post('/seller/order/update-status/{id}', function($id) {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $order = \App\Models\Order::findOrFail($id);
        $status = request()->status;
        $order->status = $status;
        $order->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'badgeClass' => ($status == 'pending' ? 'warning' : 
                          ($status == 'processing' ? 'info' : 
                          ($status == 'shipped' ? 'primary' : 
                          ($status == 'delivered' ? 'success' : 
                          ($status == 'cancelled' ? 'danger' : 'secondary')))))
        ]);
    })->name('seller.update_order_status');

    // Bulk order status update
    Route::post('/seller/orders/bulk-update', function() {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $orderIds = request()->input('order_ids', []);
        $status = request()->input('status');
        
        if (empty($orderIds) || !$status) {
            return redirect()->route('seller.order_management')
                ->with('error', 'No orders selected or no status specified.');
        }
        
        $orders = \App\Models\Order::whereIn('id', $orderIds)->get();
        
        foreach ($orders as $order) {
            $order->status = $status;
            $order->save();
        }
        
        return redirect()->route('seller.order_management')
            ->with('success', count($orderIds) . ' orders updated successfully.');
    })->name('seller.bulk_update_order_status');

    // Review Management
    Route::get('/seller/reviews', [SellerReviewController::class, 'index'])->name('seller.reviews');
    Route::get('/seller/reviews/{id}', [SellerReviewController::class, 'show'])->name('seller.reviews.show');
    Route::patch('/seller/reviews/{id}/approve', [SellerReviewController::class, 'approve'])->name('seller.reviews.approve');
    Route::patch('/seller/reviews/{id}/reject', [SellerReviewController::class, 'reject'])->name('seller.reviews.reject');
});

// OTP Verification
Route::get('/verify-otp', [App\Http\Controllers\Auth\LoginController::class, 'showOtpForm'])->name('verify.otp.form');
Route::post('/verify-otp', [App\Http\Controllers\Auth\LoginController::class, 'verifyOtp'])->name('verify.otp');

// OTP Verification with SocialAuthController
Route::get('/social-verify-otp', [App\Http\Controllers\SocialAuthController::class, 'showVerifyForm'])->name('social.verify.otp.form');
Route::post('/social-verify-otp', [App\Http\Controllers\SocialAuthController::class, 'verifyOTP'])->name('social.verify.otp');

// Cart Routes (Requires Authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::match(['get', 'post'], '/cart/add/{id}', [CartController::class, 'addToCart'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/remove/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/update/{id}', [CartController::class, 'updateCart'])->name('cart.update');
    Route::post('/cart/remove-multiple', [CartController::class, 'removeMultiple'])->name('cart.removeMultiple');
});

// Social Media Login
Route::get('auth/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// Checkout Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
    Route::post('/placeOrder', [CheckoutController::class, 'placeOrder'])->name('placeOrder');
    Route::get('/order/success/{order_id}', [CheckoutController::class, 'orderSuccess'])->name('order.success');
    Route::post('/buy-now', [CheckoutController::class, 'buyNow'])->name('buy-now');
    Route::post('/buy-now/checkout', [CheckoutController::class, 'processBuyNow'])->name('processBuyNow');
    Route::post('/cart/checkout', [CheckoutController::class, 'placeCartOrder'])->name('cart.checkout');
    Route::get('/order/receipt/{orderId}', [CheckoutController::class, 'downloadReceipt'])->name('order.download-receipt');
});

// Payment Routes
Route::get('/payment-success', function () {
    return 'Payment successful!';
})->name('payment.success');

Route::get('/payment-failed', function () {
    return 'Payment failed!';
})->name('payment.failed');

Route::get('/payment/process/{order_id}', [PaymentController::class, 'processPayment'])->name('payment.process');
Route::get('/payment/callback', [PaymentController::class, 'handleCallback'])->name('payment.callback');

// Xendit payment routes
Route::post('/pay-with-gcash', [XenditController::class, 'payWithGCash'])->name('pay.gcash');
Route::post('/webhooks/xendit', [XenditController::class, 'webhook'])->name('xendit.webhook');



Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus'])->middleware('auth')->name('orders.update-status');

// Chat System Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Customer chat routes
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'customerChat'])->name('customer.chat');
    Route::get('/chat/order/{id}', [App\Http\Controllers\ChatController::class, 'createOrderChat'])->name('chat.order');
    
    // Seller chat routes (with seller middleware)
    Route::get('/seller/chat', [App\Http\Controllers\ChatController::class, 'sellerChat'])->name('seller.chat');
    Route::get('/seller/chat/session/{id}', [App\Http\Controllers\ChatController::class, 'viewSession'])->name('seller.chat.session');
    Route::post('/seller/chat/session/{id}/close', [App\Http\Controllers\ChatController::class, 'closeSession'])->name('seller.chat.close');
    Route::get('/seller/chat/unread-count', [App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('seller.chat.unread-count');
    
    // Shared routes
    Route::post('/chat/send-message', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/check-messages', [App\Http\Controllers\ChatController::class, 'checkMessages'])->name('chat.check-messages');
});

// Notification routes for seller
Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/seller/new-orders-count', [NotificationController::class, 'getNewOrdersCount'])->name('seller.new_orders_count');
    Route::post('/seller/mark-order-notifications-read', [NotificationController::class, 'markOrderNotificationsAsRead'])->name('seller.mark_order_notifications_read');
});

// Chat routes
Route::middleware(['auth'])->group(function () {
    Route::get('/chat/order/{order}', [ChatController::class, 'viewOrderChat'])->name('chat.order');
    Route::get('/chat/unread-count', [ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
    Route::get('/chat/unread-count/{order}', [ChatController::class, 'getUnreadCountByOrder'])->name('chat.unread-count.order');
});