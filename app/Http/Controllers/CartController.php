<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariation;
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

        return response()->json(['success' => true]);
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();
        $cart = session()->get('cart', []);

        // Retrieve selected items from the request
        $selectedItems = $request->input('selected_items', []); // Defaults to empty array if nothing is passed

        // Check if any items are selected
        if (empty($selectedItems)) {
            return redirect()->route('cart')->with('error', 'Please select at least one item to checkout.');
        }

        // Filter only selected items from the cart
        $filteredCart = array_filter($cart, function ($key) use ($selectedItems) {
            return in_array($key, $selectedItems); // Keep only selected items
        }, ARRAY_FILTER_USE_KEY);

        // If no valid selected items are found, return an error
        if (empty($filteredCart)) {
            return redirect()->route('cart')->with('error', 'No items were selected or invalid items were selected.');
        }

        // Calculate total price for the selected items
        $totalPrice = array_reduce($filteredCart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        // Return the checkout view with selected items
        return view('checkout', [
            'cart' => $filteredCart,
            'totalPrice' => $totalPrice,
            'selectedItems' => $selectedItems
        ]);
    }
}