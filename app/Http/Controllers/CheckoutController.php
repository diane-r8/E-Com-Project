<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\CustomOrder;
use App\Models\DeliveryArea;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    // ✅ Show Checkout Page
    public function checkout(Request $request)
    {
        $cartItems = session()->get('cart', []);

        // Check if the cart is empty
        if (empty($cartItems)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty. Add items before proceeding to checkout.');
        }

        // Get all available delivery areas
        $deliveryAreas = DeliveryArea::all();

        return view('checkout', compact('cartItems', 'deliveryAreas'));
    }

    // ✅ Place Order Logic
    public function placeOrder(Request $request)
    {
        $cartItems = session()->get('cart', []);

        // Check if the cart is empty
        if (empty($cartItems)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        // ✅ Validate Request Data
        $request->validate([
            'phone_number' => 'required|string',
            'delivery_area_id' => 'required|exists:delivery_areas,id',
            'payment_method' => 'required|in:GCash,COD',
            'proof_of_payment' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'custom_order_details' => 'nullable|string',
            'budget_max' => 'nullable|numeric|min:1',
        ]);

        // ✅ Handle Proof of Payment for GCash
        $proofPath = null;
        if ($request->payment_method === 'GCash' && $request->hasFile('proof_of_payment')) {
            $proofPath = $request->file('proof_of_payment')->store('payment_proofs', 'public');
        }

        // ✅ Get Delivery Fee
        $deliveryArea = DeliveryArea::find($request->delivery_area_id);
        $deliveryFee = $deliveryArea->delivery_fee;

        // ✅ Validate Product Stock Before Placing Order
        foreach ($cartItems as $id => $item) {
            $product = Product::find($id);
            if (!$product || $product->stock < $item['quantity']) {
                return redirect()->route('cart')->with('error', "Product {$item['name']} is out of stock or insufficient quantity.");
            }
        }

        // ✅ Calculate Total Price
        $totalPrice = array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $cartItems)) + $deliveryFee;

        // ✅ Add Rush Order Fee if Applicable
        if ($request->has('rush_order')) {
            $totalPrice += 50;
        }

        DB::beginTransaction(); // Start Transaction

        try {
            // ✅ Create Order
            $order = Order::create([
                'user_id' => Auth::id(),
                'rush_order' => $request->has('rush_order'),
                'phone_number' => $request->phone_number,
                'delivery_area_id' => $request->delivery_area_id,
                'total_price' => $totalPrice,
                'status' => 'pending', // Order starts as 'pending'
            ]);

            // ✅ Create Order Items
            foreach ($cartItems as $id => $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // ✅ Update Product Stock
                $product = Product::find($id);
                $product->stock -= $item['quantity'];
                $product->save();
            }

            // ✅ Handle Custom Orders if Available
            if ($request->filled('custom_order_details') && $request->filled('budget_max')) {
                CustomOrder::create([
                    'order_id' => $order->id,
                    'details' => $request->custom_order_details,
                    'budget_max' => $request->budget_max,
                    'final_price' => null,
                    'status' => 'pending',
                ]);
            }

            // ✅ Create Payment Record
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $request->payment_method,
                'proof_of_payment' => $proofPath,
                'amount_paid' => $totalPrice,
                'status' => $request->payment_method === 'COD' ? 'pending' : 'completed',
            ]);

            // ✅ Clear Cart After Successful Order
            session()->forget('cart');

            DB::commit(); // Commit Transaction

            return redirect()->route('cart')->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback if something goes wrong
            return redirect()->route('cart')->with('error', 'Error placing order. Please try again.');
        }
    }
}
