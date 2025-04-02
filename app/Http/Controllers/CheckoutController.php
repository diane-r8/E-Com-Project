<?php
// CheckoutController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariation;

class CheckoutController extends Controller
{
    // Display the checkout page
    public function showCheckoutPage(Request $request)
    {
        $product = Product::find($request->product_id);
        $variation = ProductVariation::find($request->variation_id);
        $quantity = $request->quantity;

        if (!$product || !$variation) {
            return redirect()->back()->with('error', 'Invalid product or variation');
        }

        $totalPrice = $variation->price * $quantity;

        // Show the checkout page with the product, variation, quantity, and total price
        return redirect()->route('checkout')->with([
            'product' => $product,
            'variation' => $variation,
            'quantity' => $quantity,
            'totalPrice' => $totalPrice
        ]);
        
    }

    // Process the checkout (handle the form submission)
    public function processCheckout(Request $request)
    {
        // Validate input, you can extend this validation as needed
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'required|exists:product_variations,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($validated['product_id']);
        $variation = ProductVariation::find($validated['variation_id']);
        $quantity = $validated['quantity'];

        if (!$product || !$variation) {
            return redirect()->back()->with('error', 'Invalid product or variation');
        }

        // Calculate the total price
        $totalPrice = $variation->price * $quantity;

        // Order creation logic, payment processing goes here

        // Redirect to a success or payment confirmation page
        return view('checkout.success', compact('product', 'variation', 'quantity', 'totalPrice'));
    }
}
