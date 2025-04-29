<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DeliveryArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $allCart = session()->get('cart', []); // Retrieve the full cart from session

        // ✅ NEW: Handle selected items passed via GET
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

        return view('checkout', compact('cart', 'totalPrice', 'deliveryFee', 'rushOrderFee'));
    }

    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string',
            'delivery_area' => 'required|string',
            'payment_method' => 'required|string',
            'delivery_method' => 'required|string',
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $totalPrice = array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        $deliveryFee = 0;
        if ($request->has('delivery_area')) {
            $deliveryArea = $request->delivery_area;
            $area = DeliveryArea::where('area_name', $deliveryArea)->first();

            if ($area) {
                $deliveryFee = $area->delivery_fee;
            } else {
                $deliveryFee = 0; // Default to 0 if not found
            }
        }

        $rushOrderFee = $request->has('rush_order') ? 50 : 0;
        $totalPrice += $deliveryFee + $rushOrderFee;

        $order = new Order();
        $order->user_id = auth()->id();
        $order->shipping_address = $validated['shipping_address'];
        $order->delivery_area = $request->delivery_area;
        $order->total_price = $totalPrice;
        $order->payment_method = $validated['payment_method'];
        $order->delivery_method = $validated['delivery_method'];
        $order->delivery_fee = $deliveryFee;
        $order->rush_order_fee = $rushOrderFee;
        $order->status = 'Pending';
        $order->save();

        foreach ($cart as $item) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_name = $item['product_name'];
            $orderItem->variation_name = $item['variation_name'];
            $orderItem->price = $item['price'];
            $orderItem->quantity = $item['quantity'];
            $orderItem->save();
        }

        session()->forget('cart');

        return view('checkout.success', ['total' => $totalPrice]);
    }
}
