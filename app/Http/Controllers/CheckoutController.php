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
use App\Http\Controllers\PaymentController;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $user = Auth::user();
        
        // Check if this is a "Buy Now" request
        if ($request->has('buy_now') && session()->has('buy_now_item')) {
            // Fetch the buy now item
            $cart = session()->get('buy_now_item');
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
        }

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

        return view('checkout', compact('cart', 'totalPrice', 'deliveryFee', 'rushOrderFee', 'deliveryAreas'));
    }

    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string',
            'phone_number' => 'required|string',
            'delivery_area_id' => 'required',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|in:gcash,COD', // Ensure valid payment methods
        ]);

        $user = Auth::user();

        // Fetch cart items or buy now item
        if (session()->has('buy_now_item')) {
            $cart = session()->get('buy_now_item');
            session()->forget('buy_now_item'); // Clear buy now item from session
        } else {
            $cartItems = CartItem::with('product', 'productVariation')
                ->where('user_id', $user->id)
                ->get();

            if ($cartItems->isEmpty()) {
                return redirect()->route('cart')->with('error', 'Your cart is empty.');
            }

            // Prepare cart items
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
        }

        // Calculate total price
        $totalPrice = array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        // Get delivery area details
        $area = DeliveryArea::find($request->delivery_area_id);
        $deliveryFee = $area ? $area->delivery_fee : 0;

        // Check if rush order is requested
        $rushOrderFee = $request->has('rush_order') ? 50 : 0;

        // Add delivery and rush fees to the total
        $totalPrice += $deliveryFee + $rushOrderFee;

        // Create the order
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
        $order->save();

        // Add order items
        foreach ($cart as $item) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $item['product_id'];
            $orderItem->price = $item['price'];
            $orderItem->quantity = $item['quantity'];

            if (isset($item['variation_id'])) {
                $orderItem->variation_id = $item['variation_id'];
            }

            $orderItem->save();
        }

        // Clear cart after order if it's not a Buy Now purchase
        if (!session()->has('buy_now_item')) {
            CartItem::where('user_id', $user->id)->delete();
        }

        // Handle payment method (e.g., GCash)
        if ($validated['payment_method'] === 'gcash') {
            return app(PaymentController::class)->createPayment($order);
        }

        // Redirect to success page for COD or other payment methods
        return redirect()->route('order.success', ['order_id' => $order->id]);
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

    // Similar to Buy Now logic in the new controller
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


