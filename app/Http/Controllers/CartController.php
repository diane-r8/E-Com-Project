<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the cart page.
     */
   
     public function index()
     {
         // Get cart items from session (default to empty array if not set)
         $cart = session()->get('cart', []);
     
         return view('cart', compact('cart')); // Updated view name
     }
         /**
     * Add an item to the cart.
     */
    public function addToCart(Request $request)
    {
        // Get the cart from session or create an empty array
        $cart = session()->get('cart', []);

        // Generate a unique ID for the cart item (use product ID when products are ready)
        $id = $request->id ?? uniqid();  

        // Create the cart item
        $cart[$id] = [
            'name' => $request->name ?? 'Sample Product',
            'price' => $request->price ?? 100, // Default price (change when products are ready)
            'quantity' => isset($cart[$id]) ? $cart[$id]['quantity'] + 1 : 1,
        ];

        // Save updated cart to session
        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Item added to cart!');
    }

    /**
     * Remove an item from the cart.
     */
    public function removeFromCart($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    /**
     * Clear the cart.
     */
    public function clearCart()
    {
        session()->forget('cart');

        return redirect()->route('cart.index')->with('success', 'Cart cleared.');
    }
}
