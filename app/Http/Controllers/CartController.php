<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;


class CartController extends Controller
{
    public function index()
    {
        $cartItems = CartItem::with(['product', 'productVariation'])
        ->where('user_id', Auth::id())
        ->get();

         return view('cart', compact('cartItems'));
    }

    public function addToCart(Request $request)
    {
        if (!$request->variation_id) {
            return response()->json(['error' => 'No variation selected'], 400);
        }

        $variation = ProductVariation::find($request->variation_id);
        if (!$variation) {
            return response()->json(['error' => 'Invalid variation'], 400);
        }

        $product = Product::find($variation->product_id);

        // Check if item already exists for this user
        $existing = CartItem::where('user_id', Auth::id())
            ->where('product_variation_id', $variation->id)
            ->first();

        if ($existing) {
            $existing->quantity += $request->quantity;
            $existing->save();
        } else {
            CartItem::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'product_variation_id' => $variation->id,
                'quantity' => $request->quantity,
                'price' => $variation->price
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product added to cart!');
    }


    public function removeFromCart($id)
    {
        $cartItem = CartItem::where('user_id', Auth::id())
        ->where('id', $id)
        ->first();

        if ($cartItem) {
            $cartItem->delete();
            return redirect()->route('cart')->with('success', 'Product removed from cart.');
        }

        return redirect()->route('cart')->with('error', 'Item not found.');
    }

    public function updateCart(Request $request, $id)
    {
        $request->validate([
            'change' => 'required|integer'
        ]);
    
        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
    
        $newQuantity = $cartItem->quantity + $request->change;
        $cartItem->quantity = max(1, $newQuantity); // avoid quantity < 1
        $cartItem->save();
    
        return response()->json([
            'quantity' => $cartItem->quantity,
            'total' => number_format($cartItem->price * $cartItem->quantity, 2)
        ]);
    }

    public function removeMultiple(Request $request)
    {
        $ids = $request->input('selected_products', []);

        if (!empty($ids)) {
            CartItem::where('user_id', Auth::id())
                ->whereIn('id', $ids)
                ->delete();
        }
    
        return response()->json(['success' => true]);
    }

    public function checkout(Request $request)
    {
        
        $user = Auth::user();

        $selectedItems = $request->input('selected_items', []);
        if (empty($selectedItems)) {
            return redirect()->route('cart')->with('error', 'Please select at least one item to checkout.');
        }

        $cartItems = CartItem::with('productVariation')
            ->where('user_id', $user->id)
            ->whereIn('id', $selectedItems)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'No valid cart items selected.');
        }

        $totalPrice = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return view('checkout', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice,
            'selectedItems' => $selectedItems
        ]);
    }

        public function removeSelected(Request $request)
    {
        $selected = $request->input('selected_items', []);
        Cart::destroy($selected); // Or however you remove items

        return redirect()->back()->with('success', 'Selected items removed.');
    }

}
