<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\DeliveryArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\PaymentController;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $user = Auth::user();
        
        // Check if this is a "Buy Now" request
        if ($request->has('buy_now') && session()->has('buy_now_item')) {
            // Fetch the buy now item from session
            $cart = session()->get('buy_now_item');
            $isBuyNow = true;
        } else {
            // Retrieve cart items from the database
            $cartItems = CartItem::with('product', 'productVariation')
                ->where('user_id', $user->id)
                ->get();
                
            // Filter selected items if provided in the request
            $selectedIds = $request->input('selected_items', []);
            if (!empty($selectedIds)) {
                $cartItems = $cartItems->whereIn('id', $selectedIds);
            }

            // If no items, redirect to cart
            if ($cartItems->isEmpty()) {
                return redirect()->route('cart')->with('error', 'Your cart is empty or no items were selected.');
            }
            
            // Prepare the cart items in the same format as the session cart
            $cart = [];
            foreach ($cartItems as $item) {
                $cart[$item->id] = [
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'image' => $item->product->image,
                    'variation_id' => $item->productVariation ? $item->productVariation->id : null,
                    'variation_name' => $item->productVariation ? $item->productVariation->name : null,
                ];
            }
            
            $isBuyNow = false;
        }

        \Log::info('Checkout method', [
            'cart' => $cart,
            'is_buy_now' => $isBuyNow,
            'request_all' => $request->all()
        ]);

        // Calculate total price
        $totalPrice = array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        // Handle delivery and rush fees
        $deliveryFee = 0;
        $rushOrderFee = 0;

        if ($request->has('delivery_area')) {
            // Fetch delivery fee from the database
            $area = DeliveryArea::where('area_name', $request->delivery_area)->first();
            $deliveryFee = $area ? $area->delivery_fee : 0;
        }

        if ($request->has('rush_order') && $request->rush_order) {
            $rushOrderFee = 50;
        }

        // Add delivery and rush fees to total
        $totalPrice += $deliveryFee + $rushOrderFee;

        // Get all delivery areas for the dropdown
        $deliveryAreas = DeliveryArea::all();

        return view('checkout', compact('cart', 'totalPrice', 'deliveryFee', 'rushOrderFee', 'deliveryAreas', 'isBuyNow'));
    }

    /**
     * Place an order specifically from the cart in the database
     */
    public function placeCartOrder(Request $request)
    {
        // Log all data to debug
        \Log::info('placeCartOrder method called', [
            'request_data' => $request->all()
        ]);

        $user = Auth::user();
        
        // Basic validation
        $validated = $request->validate([
            'full_name' => 'required|string',
            'phone_number' => 'required|string',
            'delivery_area_id' => 'required',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|in:gcash,COD,GCash',
        ]);
        
        // Get cart data from the database
        $cartItems = CartItem::with('product', 'productVariation')
            ->where('user_id', $user->id)
            ->get();
        
        if ($cartItems->isEmpty()) {
            \Log::error('Cart is empty in placeCartOrder');
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }
        
        \Log::info('Cart contains items', ['cart_count' => $cartItems->count()]);
        
        // Convert cart items to array format for processing
        $cart = [];
        foreach ($cartItems as $item) {
            $cart[$item->id] = [
                'product_id' => $item->product->id,
                'product_name' => $item->product->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'image' => $item->product->image,
                'variation_id' => $item->productVariation ? $item->productVariation->id : null,
                'variation_name' => $item->productVariation ? $item->productVariation->name : null,
            ];
        }
        
        // Calculate totals
        $subtotal = array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);
        
        $area = DeliveryArea::find($validated['delivery_area_id']);
        $deliveryFee = $area ? $area->delivery_fee : 0;
        $rushOrderFee = $request->has('rush_order') ? 50 : 0;
        
        // Calculate final total
        $totalPrice = $subtotal + $deliveryFee + $rushOrderFee;
        
        try {
            // Create a new order
            $order = new Order();
            $order->user_id = $user->id;
            $order->full_name = $validated['full_name'];
            $order->phone_number = $validated['phone_number'];
            $order->delivery_area_id = $validated['delivery_area_id'];
            $order->shipping_address = $validated['shipping_address'];
            $order->landmark = $request->landmark;
            $order->total_price = $totalPrice;
            $order->delivery_fee = $deliveryFee;
            $order->rush_order_fee = $rushOrderFee;
            $order->status = 'Pending';
            $order->payment_method = $validated['payment_method'];
            $order->payment_status = 'Pending';
            $order->is_rush = $request->has('is_rush') ? true : false;
            
            // Handle is_default to avoid constraint violation
            if ($request->has('is_default') && $request->is_default) {
                // If user wants this as default, find any existing default and update it first
                Order::where('user_id', $user->id)
                     ->where('is_default', true)
                     ->update(['is_default' => false]);
                
                $order->is_default = true;
            } else {
                $order->is_default = false;
            }
            
            $order->save();
            
            \Log::info('Order created', ['order_id' => $order->id]);
            
            // Create order items
            foreach ($cart as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $item['product_id'];
                $orderItem->price = $item['price'];
                $orderItem->quantity = $item['quantity'];
                
                if (isset($item['variation_id']) && $item['variation_id']) {
                    $orderItem->variation_id = $item['variation_id'];
                }
                
                $orderItem->save();
                \Log::info('Order item created', ['order_item_id' => $orderItem->id]);
            }
            
            // Save address as default if requested
            if ($request->has('is_default') && $request->is_default) {
                if (Schema::hasColumn('users', 'default_address')) {
                    $user->default_address = $validated['shipping_address'];
                }
                if (Schema::hasColumn('users', 'default_landmark')) {
                    $user->default_landmark = $request->landmark;
                }
                if (Schema::hasColumn('users', 'default_phone')) {
                    $user->default_phone = $validated['phone_number'];
                }
                if (Schema::hasColumn('users', 'default_area_id')) {
                    $user->default_area_id = $validated['delivery_area_id'];
                }
                $user->save();
            }
            
            // Clear the cart from database
            CartItem::where('user_id', $user->id)->delete();
            
            // Handle payment method
            if (strtolower($validated['payment_method']) == 'gcash') {
                \Log::info('Processing payment with GCash');
                return redirect()->route('payment.process', ['order_id' => $order->id]);
            }
            
            // For COD, redirect to success
            \Log::info('Processing COD order, redirecting to success page');
            return redirect()->route('order.success', ['order_id' => $order->id]);
            
        } catch (\Exception $e) {
            \Log::error('Error in placeCartOrder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('cart')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Process a Buy Now order directly.
     * This is a simplified version that avoids all the complex logic.
     */
    public function processBuyNow(Request $request)
    {
        \Log::info('processBuyNow called', ['request' => $request->all()]);
        
        // Basic validation
        $validated = $request->validate([
            'full_name' => 'required|string',
            'phone_number' => 'required|string',
            'delivery_area_id' => 'required',
            'shipping_address' => 'required|string',
            'payment_method' => 'required',
        ]);
        
        // Get the buy now item
        $buyNowItem = session()->get('buy_now_item', []);
        
        if (empty($buyNowItem)) {
            \Log::error('Buy Now item not found in session');
            return redirect()->route('cart')->with('error', 'Product information not found');
        }
        
        \Log::info('Buy Now Item found', ['item' => $buyNowItem]);
        
        // Get the first item (there should be only one in buy_now_item)
        $item = reset($buyNowItem);
        $productId = key($buyNowItem);
        
        // Calculate totals
        $price = $item['price'] * $item['quantity'];
        $area = DeliveryArea::find($validated['delivery_area_id']);
        $deliveryFee = $area ? $area->delivery_fee : 0;
        $rushOrderFee = $request->has('rush_order') ? 50 : 0;
        $totalPrice = $price + $deliveryFee + $rushOrderFee;
        
        \Log::info('Order details', [
            'productId' => $productId,
            'price' => $price,
            'deliveryFee' => $deliveryFee,
            'rushOrderFee' => $rushOrderFee,
            'totalPrice' => $totalPrice
        ]);
        
        try {
            // Create a new order with minimal fields
            $order = new Order;
            $order->user_id = auth()->id();
            $order->full_name = $validated['full_name'];
            $order->phone_number = $validated['phone_number'];
            $order->delivery_area_id = $validated['delivery_area_id'];
            $order->shipping_address = $validated['shipping_address'];
            $order->landmark = $request->landmark;
            $order->total_price = $totalPrice;
            $order->delivery_fee = $deliveryFee;
            $order->rush_order_fee = $rushOrderFee;
            $order->status = 'Pending';
            $order->payment_method = $validated['payment_method'];
            $order->payment_status = 'Pending';
            $order->is_rush = $request->has('is_rush') ? true : false;
            
            // Important: SKIP setting is_default for now to avoid constraints
            $order->is_default = false;
            $order->save();
            
            \Log::info('Order created', ['order_id' => $order->id]);
            
            // Now create the order item
            $orderItem = new OrderItem;
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $item['product_id'] ?? $productId;
            $orderItem->price = $item['price'];
            $orderItem->quantity = $item['quantity'];
            
            if (isset($item['variation_id']) && $item['variation_id']) {
                $orderItem->variation_id = $item['variation_id'];
            }
            
            $orderItem->save();
            
            \Log::info('Order item created', ['order_item_id' => $orderItem->id]);
            
            // Clear the buy now item from session
            session()->forget('buy_now_item');
            
            // Handle payment method
            if (strtolower($validated['payment_method']) == 'gcash') {
                \Log::info('Processing payment with GCash');
                return redirect()->route('payment.process', ['order_id' => $order->id]);
            }
            
            // For COD, redirect to success
            \Log::info('Processing COD order, redirecting to success page');
            return redirect()->route('order.success', ['order_id' => $order->id]);
            
        } catch (\Exception $e) {
            \Log::error('Error in processBuyNow', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function orderSuccess($orderId)
    {
        $order = Order::findOrFail($orderId);
        $orderItems = OrderItem::where('order_id', $orderId)->get();

        // Fetch product details for display
        foreach ($orderItems as $item) {
            $item->product = Product::find($item->product_id);

            if ($item->variation_id) {
                $item->variation = ProductVariation::find($item->variation_id);
            }
        }

        return view('order.success', compact('order', 'orderItems'));
    }

    public function buyNow(Request $request)
    {
        $productId = $request->input('product_id');
        $variationId = $request->input('variation_id');
        $quantity = $request->input('quantity', 1);
        
        $product = Product::findOrFail($productId);
        $variation = $variationId ? ProductVariation::findOrFail($variationId) : null;
        
        $item = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $variation ? $variation->price : $product->price,
            'quantity' => $quantity,
            'image' => $product->image,
        ];

        if ($variation) {
            $item['variation_id'] = $variation->id;
            $item['variation_name'] = $variation->name;
        }

        // Store in session
        session()->put('buy_now_item', [$product->id => $item]);

        return redirect()->route('checkout', ['buy_now' => true]);
    }
    
    public function downloadReceipt($orderId)
    {
        $order = Order::findOrFail($orderId);
        $orderItems = OrderItem::where('order_id', $orderId)->get();
        
        // Load product details for display
        foreach ($orderItems as $item) {
            // Load product information
            $item->product = Product::find($item->product_id);
            
            // Load variation information if available
            if ($item->variation_id) {
                $item->variation = ProductVariation::find($item->variation_id);
            }
        }
        
        // Check if user is authorized to view this receipt (if not an admin)
        if (auth()->id() != $order->user_id && !(auth()->user() && auth()->user()->is_admin)) {
            return redirect()->route('home')->with('error', 'You are not authorized to view this receipt.');
        }
        
        // Set current time for the receipt with correct timezone
        $currentTime = Carbon::now('Asia/Manila');
        
        // Generate the receipt PDF
        $pdf = PDF::loadView('order.receipt-pdf', [
            'order' => $order,
            'orderItems' => $orderItems,
            'currentTime' => $currentTime
        ]);
        
        // Configure PDF options
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'dejavu sans'
        ]);
        
        return $pdf->download('Crafts-N-Wraps-Order-'.$order->id.'-Receipt.pdf');
    }
}