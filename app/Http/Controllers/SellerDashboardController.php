<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\CartItem;


class SellerDashboardController extends Controller
{

        public function index(Request $request)
    {
        $status = $request->input('status', 'all'); // Default to 'all' if no status is selected.
        $orders = Order::query();

        // Filter orders based on the status
        if ($status !== 'all') {
            $orders = $orders->where('status', $status);
        }

        // Optionally, you can paginate the orders if you have many
        $orders = $orders->paginate(10); // This will paginate orders, showing 10 orders per page.

        $totalOrders = Order::count();
        $totalProducts = Product::count(); // Assuming you have a Product model for counting products.
        $totalAddedToCart = CartItem::count(); // Assuming CartItem model keeps track of cart items.

        // Calculate total sales (if needed, you can filter by completed orders)
        $totalSales = $orders->where('status', 'completed')->sum('total_price');

        return view('seller.dashboard', compact('orders', 'totalOrders', 'totalProducts', 'totalAddedToCart', 'totalSales'));
    }

}
