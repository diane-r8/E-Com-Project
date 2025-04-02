<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariation;


//NEW
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = session()->get('cart', []);
        return view('cart', compact('cartItems'));
    }
    public function addToCart(Request $request)
    {
        // Ensure variation_id is being received
        if (!$request->variation_id) {
            return response()->json(['error' => 'No variation selected'], 400);
        }
    
        // Fetch the product variation using ID
        $variation = ProductVariation::where('id', $request->variation_id)->first();
    
        if (!$variation) {
            return response()->json(['error' => 'Invalid product or variation'], 400);
        }
    
        // Fetch the main product
        $product = Product::find($variation->product_id);
    
        // Initialize session cart
        $cart = session()->get('cart', []);
    
        $cart[$variation->id] = [
            "product_name" => $product->name,
            "variation_name" => $variation->name,
            "price" => $variation->price,
            "quantity" => $request->quantity,
            "image" => $product->image 
        ];
    
        session()->put('cart', $cart);
    
       
        return redirect()->route('products.index')->with('success', 'Product added to cart!');

    
    }
    

    

    public function removeFromCart($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart')->with('success', 'Product removed from cart.');
    }

    public function updateCart(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $request->change;
            if ($cart[$id]['quantity'] < 1) {
                $cart[$id]['quantity'] = 1;
            }
            session()->put('cart', $cart);

            return response()->json([
                'quantity' => $cart[$id]['quantity'],
                'total' => number_format($cart[$id]['price'] * $cart[$id]['quantity'], 2)
            ]);
        }

        return response()->json(['error' => 'Item not found'], 404);
    }

    public function removeMultiple(Request $request)
    {
        $cart = session()->get('cart', []);
        if ($request->has('selected_products')) {
            foreach ($request->selected_products as $id) {
                unset($cart[$id]);
            }
            session()->put('cart', $cart);
        }

        return redirect()->route('cart')->with('success', 'Selected products removed from cart.');
    }

  //new  
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $cartItems = session()->get('cart', []);

        if (empty($cartItems)) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty.');
        }

    //     // Create Order
    //     $order = new Order();
    //     $order->user_id = $user->id;
    //     $order->total_price = array_sum(array_column($cartItems, 'total_price'));
    //     $order->payment_method = $request->payment_method; // 'gcash' or 'cod'
    //     $order->delivery_method = $request->delivery_method; // 'pickup' or 'delivery'
    //     $order->delivery_fee = $this->calculateDeliveryFee($request->distance);
    //     $order->status = 'Pending';
    //     $order->save();

    //     // Save Order Items
    //     foreach ($cartItems as $item) {
    //         $orderItem = new OrderItem();
    //         $orderItem->order_id = $order->id;
    //         $orderItem->product_id = $item['id'];
    //         $orderItem->variation_id = $item['variation_id'] ?? null;
    //         $orderItem->quantity = $item['quantity'];
    //         $orderItem->price = $item['price'];
    //         $orderItem->save();
    //     }

    //     // Clear the cart
    //     session()->forget('cart');

    //     return redirect()->route('order.success', ['order_id' => $order->id]);
    // }

    // private function calculateDeliveryFee($distance)
    // {
    //     if ($distance <= 5) {
    //         return 50; // Flat rate for short distances
    //     } elseif ($distance <= 10) {
    //         return 100; // Medium distance fee
    //     } else {
    //         return 150; // Long distance fee
    //     }
    // }
}

}




