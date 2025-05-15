<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

//!!!!!ONLY for SELLER SIDE: Order Managemnet (seller/dashboard)
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
    public function updateStatus(Request $request, $id)
    {
        // Find the order by ID
        $order = Order::findOrFail($id);
        
        // Validate the incoming status
        $validated = $request->validate([
            'status' => 'required|in:new,processing,shipped,delivered,cancelled',
        ]);

        // Update the order status
        $order->status = $validated['status'];
        $order->save();

        // Return a JSON response indicating success
        return response()->json(['success' => true, 'status' => $order->status]);
    }

   
    public function destroy(Order $order)
    {
        // Delete the order from the database
        $order->delete();

        // Return a redirect or JSON response after deletion
        return redirect()->route('seller.dashboard')->with('success', 'Order deleted successfully.');
    }

    public function cancel(Order $order)
    {
        // Optional: check if the order belongs to the logged-in user
        if ($order->user_id !== auth()->id()) {
            abort(403); // Or redirect with error
        }

        // Only allow cancellation if it's still pending
        if (strtolower($order->status) !== 'pending') {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        $order->status = 'Cancelled';
        $order->save();

        return back()->with('success', 'Order has been cancelled.');
    }


   public function received(Order $order)
    {
        // Debug information
        \Log::info('Attempting to mark order as received:', [
            'order_id' => $order->id,
            'order_user_id' => $order->user_id,
            'auth_user_id' => auth()->id(),
            'order_status' => $order->status
        ]);

        // Check if the order belongs to the logged-in user
        if ($order->user_id !== auth()->id()) {
            \Log::warning('Unauthorized attempt to mark order as received', [
                'order_id' => $order->id,
                'order_user_id' => $order->user_id,
                'auth_user_id' => auth()->id()
            ]);
            abort(403, 'You are not authorized to mark this order as received.');
        }

        // Only allow the user to mark the order as received if it's delivered
        if (strtolower($order->status) !== 'delivered' && $order->status !== 'Delivered') {
            \Log::info('Invalid order status for marking as received', [
                'order_id' => $order->id,
                'current_status' => $order->status
            ]);
            return back()->with('error', 'This order cannot be marked as received. Order must be in Delivered status.');
        }

        try {
            // Update the status to "Received"
        $order->status = 'Received';
        $order->save();

            \Log::info('Order marked as received successfully', [
                'order_id' => $order->id
            ]);

        return back()->with('success', 'Order has been marked as received.');
        } catch (\Exception $e) {
            \Log::error('Error marking order as received', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'An error occurred while marking the order as received. Please try again.');
        }
    }

    public function rateOrder(Request $request, $id)
    {
        // Find the order by ID
        $order = Order::findOrFail($id);

        // Validate the rating and review
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
            'product_id' => 'required|exists:products,id'
        ]);

        // Store the rating and review with required user_id and product_id
        $order->reviews()->create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'review' => $request->review,
            'status' => 'approved'
        ]);

        // Redirect back to the order page (or dashboard)
        return redirect()->route('orders.index')->with('success', 'Thank you for your feedback!');
    }

    /**
     * Rate an order and create a review for the product
     */
    public function rate(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Debug information
        \Log::info('Rating submission:', [
            'order_id' => $id,
            'order_status' => $order->status,
            'user_id' => auth()->id(),
            'order_user_id' => $order->user_id,
            'rating' => $request->rating,
            'review' => $request->review
        ]);
        
        // Check if the order belongs to the user and is in 'received' status
        if ($order->user_id !== auth()->id() || strtolower($order->status) !== 'received') {
            \Log::warning('Rating rejected: incorrect user or status', [
                'expected_status' => 'received',
                'actual_status' => $order->status
            ]);
            return back()->with('error', 'You can only review orders that you have received.');
        }
        
        // Validate the request
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000'
        ]);
        
        $reviewsCreated = 0;
        $itemCount = $order->items->count();
        
        \Log::info('Order items found: ' . $itemCount);
        
        // Create a review for each product in the order
        foreach ($order->items as $index => $item) {
            \Log::info('Processing item ' . ($index + 1) . ' of ' . $itemCount, [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
            ]);
            
            // Check if a review already exists for this product in this order
            $existingReview = Review::where([
                'user_id' => auth()->id(),
                'product_id' => $item->product_id,
                'order_id' => $order->id
            ])->first();
            
            if ($existingReview) {
                \Log::info('Review already exists for product', [
                    'product_id' => $item->product_id,
                    'review_id' => $existingReview->id
                ]);
            } else {
                try {
                    // Create a new review - explicitly assign all fields directly
                    $review = new Review();
                    $review->user_id = auth()->id();
                    $review->product_id = $item->product_id;
                    $review->order_id = $order->id;
                    $review->rating = $request->rating;
                    $review->review = $request->review;
                    $review->status = 'approved';
                    $review->save();
                    
                    \Log::info('Review created', [
                        'review_id' => $review->id,
                        'product_id' => $item->product_id
                    ]);
                    
                    $reviewsCreated++;
                } catch (\Exception $e) {
                    \Log::error('Error creating review', [
                        'error' => $e->getMessage(),
                        'product_id' => $item->product_id
                    ]);
                }
            }
        }
        
        \Log::info('Reviews created: ' . $reviewsCreated);
        
        if ($reviewsCreated > 0) {
            return back()->with('success', 'Thank you for your review!');
        } else if ($itemCount == 0) {
            return back()->with('error', 'No products found in this order to review.');
        } else {
            return back()->with('info', 'You have already reviewed all products in this order.');
        }
    }
}