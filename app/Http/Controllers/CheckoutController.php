<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\DeliveryArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\PaymentController;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        // Check if this is a "Buy Now" request
        if ($request->has('buy_now') && session()->has('buy_now_item')) {
            $cart = session()->get('buy_now_item');
        } else {
            $allCart = session()->get('cart', []); // Retrieve the full cart from session

            // ✅ Handle selected items passed via GET
            $selectedIds = $request->input('selected_items', []);
            $cart = [];

            if (!empty($selectedIds)) {
                foreach ($selectedIds as $id) {
                    if (isset($allCart[$id])) {
                        $cart[$id] = $allCart[$id];
                    }
                }
            } else {
                $cart = $allCart;
            }
        }

        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty or no items were selected.');
        }

        // ✅ Use filtered cart to calculate total
        $totalPrice = array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        // Prepare for delivery fee and rush order fee
        $deliveryFee = 0;
        $rushOrderFee = 0;

        if ($request->has('delivery_area')) {
            // Fetch delivery fee from the database
            $deliveryArea = $request->delivery_area;
            $area = DeliveryArea::where('area_name', $deliveryArea)->first();

            if ($area) {
                $deliveryFee = $area->delivery_fee;
            } else {
                $deliveryFee = 0; // Default to 0 if not found
            }
        }

        if ($request->has('rush_order') && $request->rush_order) {
            $rushOrderFee = 50;
        }

        // Add delivery and rush fees to total
        $totalPrice += $deliveryFee + $rushOrderFee;

        // Get all delivery areas for the dropdown
        $deliveryAreas = DeliveryArea::all();

        return view('checkout', compact('cart', 'totalPrice', 'deliveryFee', 'rushOrderFee', 'deliveryAreas'));
    }

    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string',
            'phone_number' => 'required|string',
            'delivery_area_id' => 'required',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|in:gcash,COD,GCash', // Add this validation rule
        ]);
    
        // Check if this was a "Buy Now" purchase
        if (session()->has('buy_now_item')) {
            $cart = session()->get('buy_now_item');
            session()->forget('buy_now_item'); // Clear the "Buy Now" item
        } else {
            $cart = session()->get('cart', []);
        }
    
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }
    
        $totalPrice = array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);
    
        // Get delivery area details
        $area = DeliveryArea::find($request->delivery_area_id);
        $deliveryFee = $area ? $area->delivery_fee : 0;
    
        // Check if rush order is requested
        $rushOrderFee = $request->has('rush_order') ? 50 : 0;
        
        // Calculate final total
        $totalPrice += $deliveryFee + $rushOrderFee;
    
        $order = new Order();
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
        $order->is_rush = $request->has('is_rush');
        
        // IMPORTANT: Handle is_default to avoid constraint violation
        if ($request->has('is_default') && $request->is_default) {
            // If user wants this as default, find any existing default and update it first
            Order::where('user_id', auth()->id())
                 ->where('is_default', true)
                 ->update(['is_default' => false]);
            
            $order->is_default = true;
        } else {
            // Since we need an order, find and delete a previous non-default order for this user if it exists
            // Only do this if this is a "buy now" order to avoid deleting legitimate past orders
            if ($request->has('buy_now') && $request->buy_now) {
                $existingNonDefaultOrder = Order::where('user_id', auth()->id())
                                               ->where('is_default', false)
                                               ->where('status', 'Pending') // Only delete pending orders
                                               ->first();
                                               
                if ($existingNonDefaultOrder) {
                    $existingNonDefaultOrder->delete();
                }
            }
            
            $order->is_default = false;
        }
        
        $order->save();

    
        // Add order items
        foreach ($cart as $item) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $item['product_id'];
            $orderItem->price = $item['price'];
            $orderItem->quantity = $item['quantity'];
            
            // Only set variation_id if it exists in the cart item
            if (isset($item['variation_id'])) {
                $orderItem->variation_id = $item['variation_id'];
            }
            
            $orderItem->save();
        }
    
        // Save address as default if requested
        if ($request->has('is_default') && $request->is_default) {
            $user = Auth::user();
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
        
        // Clear cart if this wasn't a "Buy Now" purchase
        if (!session()->has('buy_now_item')) {
            session()->forget('cart');
        }
    
        // Handle payment method
        if ($request->input('payment_method') === 'gcash') {
            // Create a payment record and redirect to Xendit
            return app(PaymentController::class)->createPayment($order);
        }
    
        // For COD or other payment methods, redirect to success page
        return redirect()->route('order.success', ['order_id' => $order->id]);
    }

    public function orderSuccess($orderId)
    {
        $order = Order::findOrFail($orderId);
        $orderItems = OrderItem::where('order_id', $orderId)->get();
        
        // Fetch product details for display
        foreach ($orderItems as $item) {
            // Load product information
            $item->product = Product::find($item->product_id);
            
            // Load variation information if available
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
        
        // Get variation if provided
        $variation = null;
        if ($variationId) {
            $variation = ProductVariation::findOrFail($variationId);
        }
        
        // Create buy now item with all necessary information
        $item = [
            'product_id' => $product->id,
            'product_name' => $product->name, // Add product name
            'price' => $variation ? $variation->price : $product->price,
            'quantity' => $quantity,
            'image' => $product->image // Add product image if used in checkout view
        ];
        
        if ($variation) {
            $item['variation_id'] = $variation->id;
            $item['variation_name'] = $variation->name; // Add variation name
        } else {
            // Ensure variation_name exists even if null (to prevent errors)
            $item['variation_name'] = null;
        }
        
        // Store in session
        session()->put('buy_now_item', [$product->id => $item]);
        
        // Redirect to checkout
        return redirect()->route('checkout', ['buy_now' => true]);
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
        // Important: SKIP setting is_default for now
        $order->save();
        
        \Log::info('Order created', ['order_id' => $order->id]);
        
        // Now create the order item
        $orderItem = new OrderItem;
        $orderItem->order_id = $order->id;
        $orderItem->product_id = $item['product_id'] ?? $productId;
        $orderItem->price = $item['price'];
        $orderItem->quantity = $item['quantity'];
        
        if (isset($item['variation_id'])) {
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


}


