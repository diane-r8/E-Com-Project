<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    
    public function index()
    {
        // Fetch orders along with their order items and the related product
        $orders = Order::with(['items.variation', 'items.product'])->get();
        
        // You can also add filtering here if needed (by status)
        // Example: $orders = Order::where('status', 'processing')->with(['orderItems.product'])->get();
        
        return view('seller.dashboard', compact('orders'));
    }
    public function updateStatus(Order $order, Request $request)
    {
        // Validate the incoming status
        $validated = $request->validate([
            'status' => 'required|in:new,processing,completed,canceled,refunded',
        ]);

        // Update the order status
        $order->status = $validated['status'];
        $order->save();

        // Return a JSON response indicating success
        return response()->json(['success' => true]);
    }

   
    public function destroy(Order $order)
    {
        // Delete the order from the database
        $order->delete();

        // Return a redirect or JSON response after deletion
        return redirect()->route('seller.dashboard')->with('success', 'Order deleted successfully.');
    }
}
