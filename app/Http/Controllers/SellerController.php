<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Order;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PDF;

class SellerController extends Controller
{
    // Constructor to add middleware
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('seller'); // Ensure user is a seller
    }

    // Dashboard view
    public function dashboard()
    {
        return redirect()->route('seller.dashboard');
    }

    // Products view with all products
    public function products()
    {
        $products = Product::with('variations')
            ->where('seller_id', auth()->id())
            ->get();

        return view('seller.products', compact('products'));
    }

    // Edit variation form
    public function editVariation($id)
    {
        $variation = ProductVariation::findOrFail($id);

        // Ensure the seller owns this product variation
        $product = Product::findOrFail($variation->product_id);
        if ($product->seller_id != auth()->id()) {
            return redirect()->route('seller.products')
                ->with('error', 'You do not have permission to edit this variation.');
        }

        return view('seller.edit_variation', compact('variation'));
    }

    // Update variation
    public function updateVariation(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0'
        ]);

        $variation = ProductVariation::findOrFail($id);

        // Ensure the seller owns this product variation
        $product = Product::findOrFail($variation->product_id);
        if ($product->seller_id != auth()->id()) {
            return redirect()->route('seller.products')
                ->with('error', 'You do not have permission to update this variation.');
        }

        $variation->update([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);

        return redirect()->route('seller.products')->with('success', 'Product variation updated successfully.');
    }

    // Adjust product stock via AJAX
    public function adjustProductStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Ensure the seller owns this product
        if ($product->seller_id != auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $product->stock = max(0, $request->stock); // Ensure stock doesn't go below 0
        $product->save();

        // Check if stock is zero and update availability accordingly
        if ($product->stock == 0) {
            $product->availability = 0;
            $product->save();
        } else if ($product->stock > 0 && $product->availability == 0) {
            $product->availability = 1;
            $product->save();
        }

        return response()->json([
            'success' => true, 
            'stock' => $product->stock,
            'availability' => $product->availability
        ]);
    }

    // Adjust variation stock via AJAX
    public function adjustVariationStock(Request $request, $id)
    {
        $variation = ProductVariation::findOrFail($id);

        // Ensure the seller owns this product variation
        $product = Product::findOrFail($variation->product_id);
        if ($product->seller_id != auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $variation->stock = max(0, $request->stock); // Ensure stock doesn't go below 0
        $variation->save();

        return response()->json([
            'success' => true, 
            'stock' => $variation->stock
        ]);
    }

    // Delete a product
    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);

        // Ensure the seller owns this product
        if ($product->seller_id != auth()->id()) {
            return redirect()->route('seller.products')
                ->with('error', 'You do not have permission to delete this product.');
        }

        // Check if product is used in any orders
        $orderItems = OrderItem::where('product_id', $id)->count();
        if ($orderItems > 0) {
            return redirect()->route('seller.products')
                ->with('error', 'This product cannot be deleted because it is associated with orders.');
        }

        // Delete associated image if exists
        if ($product->image && Storage::exists('public/' . $product->image)) {
            Storage::delete('public/' . $product->image);
        }

        // Delete associated variations
        $product->variations()->delete();

        // Delete the product
        $product->delete();

        return redirect()->route('seller.products')
            ->with('success', 'Product deleted successfully.');
    }

    // Delete a product variation
    public function deleteVariation($id)
    {
        $variation = ProductVariation::findOrFail($id);

        // Ensure the seller owns this product variation
        $product = Product::findOrFail($variation->product_id);
        if ($product->seller_id != auth()->id()) {
            return redirect()->route('seller.products')
                ->with('error', 'You do not have permission to delete this variation.');
        }

        // Check if variation is used in any orders
        $orderItems = OrderItem::where('variation_id', $id)->count();
        if ($orderItems > 0) {
            return redirect()->route('seller.products')
                ->with('error', 'This variation cannot be deleted because it is associated with orders.');
        }

        // Delete the variation
        $variation->delete();

        return redirect()->route('seller.products')
            ->with('success', 'Product variation deleted successfully.');
    }

    // Bulk delete products
    public function bulkDeleteProducts(Request $request)
    {
        $productIds = $request->input('product_ids', []);

        if (empty($productIds)) {
            return redirect()->route('seller.products')
                ->with('error', 'No products selected for deletion.');
        }

        // Ensure the seller owns these products
        $products = Product::whereIn('id', $productIds)
            ->where('seller_id', auth()->id())            ->get();

        $deletedCount = 0;

        // Delete products
        foreach ($products as $product) {
            // Check if product is used in any orders
            $orderItems = OrderItem::where('product_id', $product->id)->count();
            if ($orderItems > 0) {
                continue; // Skip this product
            }

            // Delete associated image if exists
            if ($product->image && Storage::exists('public/' . $product->image)) {
                Storage::delete('public/' . $product->image);
            }

            // Delete associated variations
            $product->variations()->delete();

            // Delete the product
            $product->delete();

            $deletedCount++;
        }

        if ($deletedCount === 0) {
            return redirect()->route('seller.products')
                ->with('error', 'No products were deleted. Products associated with orders cannot be deleted.');
        }

        return redirect()->route('seller.products')
            ->with('success', $deletedCount . ' products deleted successfully.');
    }

    // Create product form
    public function createProduct()
    {
        $categories = Category::all();
        return view('seller.create_product', compact('categories'));
    }

    // Store new product
    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'availability' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048',
            'variations' => 'nullable|array'
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Create product
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'availability' => $request->availability,
            'category_id' => $request->category_id,
            'seller_id' => auth()->id(),
            'image' => $imagePath
        ]);

        // Create variations if any
        if ($request->has('variations')) {
            foreach ($request->variations as $variation) {
                if (!empty($variation['name'])) {
                    ProductVariation::create([
                        'product_id' => $product->id,
                        'name' => $variation['name'],
                        'price' => $variation['price'] ?? $product->price,
                        'stock' => $variation['stock'],
                    ]);
                }
            }
        }

        return redirect()->route('seller.products')
            ->with('success', 'Product created successfully.');
    }

    // Edit product form
    public function editProduct($id)
    {
        $product = Product::findOrFail($id);

        // Ensure the seller owns this product
        if ($product->seller_id != auth()->id()) {
            return redirect()->route('seller.products')
                ->with('error', 'You do not have permission to edit this product.');
        }

        $categories = Category::all();
        return view('seller.edit_product', compact('product', 'categories'));
    }

    // Update product
    public function updateProduct(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'availability' => 'required|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        $product = Product::findOrFail($id);

        // Ensure the seller owns this product
        if ($product->seller_id != auth()->id()) {
            return redirect()->route('seller.products')
                ->with('error', 'You do not have permission to update this product.');
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::exists('public/' . $product->image)) {
                Storage::delete('public/' . $product->image);
            }

            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        // Update product
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->availability = $request->availability;
        $product->save();

        return redirect()->route('seller.products')
            ->with('success', 'Product updated successfully.');
    }

    // Order management
    public function orderManagement(Request $request)
    {
        // Apply filters
        $status = $request->status;
        $search = $request->search;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        // Start with basic query for seller's orders
        $query = Order::whereHas('items.product', function($query) {
            $query->where('seller_id', auth()->id());
        })->with(['items.product', 'items.variation']);

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Apply date range filter
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Get results with pagination
        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('seller.order_management', compact('orders'));
    }

    // View order details
    public function viewOrder($id)
    {
        $order = Order::with(['items.product', 'items.variation'])->findOrFail($id);

        // Check if seller has products in this order
        $hasSellerProducts = $order->items->filter(function($item) {
            return $item->product && $item->product->seller_id == auth()->id();
        })->count() > 0;

        if (!$hasSellerProducts) {
            return redirect()->route('seller.order_management')
                ->with('error', 'You do not have permission to view this order.');
        }

        return view('seller.view_order', compact('order'));
    }

    // Update order status
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::with('items.product')->findOrFail($id);

        // Check if seller has products in this order
        $hasSellerProducts = $order->items->filter(function($item) {
            return $item->product && $item->product->seller_id == auth()->id();
        })->count() > 0;

        if (!$hasSellerProducts) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'You do not have permission to update this order.'
                ], 403);
            }

            return redirect()->route('seller.order_management')
                ->with('error', 'You do not have permission to update this order.');
        }

        $order->status = $request->status;
        $order->save();

        // If status is 'cancelled', restore the stock
        if ($request->status === 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->product && $item->product->seller_id == auth()->id()) {
                    if ($item->variation_id) {
                        $variation = ProductVariation::find($item->variation_id);
                        if ($variation) {
                            $variation->stock += $item->quantity;
                            $variation->save();
                        }
                    } else {
                        $item->product->stock += $item->quantity;
                        $item->product->save();
                    }
                }
            }
        }

        // Return JSON response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $order->status,
                'badgeClass' => $this->getStatusBadgeColor($order->status)
            ]);
        }

        // Redirect for non-AJAX requests
        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    // Get badge color for status
    private function getStatusBadgeColor($status)
    {
        $colors = [
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger'
        ];

        return $colors[$status] ?? 'secondary';
    }

    // Get badge color for payment status
    private function getPaymentStatusBadgeColor($status)
    {
        $colors = [
            'paid' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'refunded' => 'info'
        ];

        return $colors[$status] ?? 'secondary';
    }

    // Generate invoice PDF
    public function generateInvoice($id)
    {
        $order = Order::with(['items.product', 'items.variation'])->findOrFail($id);

        // Check if seller has products in this order
        $hasSellerProducts = $order->items->filter(function($item) {
            return $item->product && $item->product->seller_id == auth()->id();
        })->count() > 0;

        if (!$hasSellerProducts) {
            return redirect()->route('seller.order_management')
                ->with('error', 'You do not have permission to generate an invoice for this order.');
        }

        // Filter items to only show seller's products
        $sellerItems = $order->items->filter(function($item) {
            return $item->product && $item->product->seller_id == auth()->id();
        });

        // Calculate seller's subtotal
        $sellerSubtotal = $sellerItems->sum(function($item) {
            return $item->price * $item->quantity;
        });

        // Set timezone to Asia/Manila
        $currentTime = now()->timezone('Asia/Manila');

        // Generate PDF
        $pdf = PDF::loadView('seller.invoice_pdf', [
            'order' => $order,
            'sellerItems' => $sellerItems,
            'sellerSubtotal' => $sellerSubtotal,
            'currentTime' => $currentTime,
            'seller' => auth()->user()
        ]);

        // Stream the PDF
        return $pdf->stream('invoice-' . $order->id . '.pdf');
    }

    // Order statistics for dashboard
    public function getOrderStatistics()
    {
        $sellerId = auth()->id();

        // Get orders that contain seller's products
        $sellerOrders = Order::whereHas('items.product', function($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        })->get();

        // Total orders count
        $totalOrders = $sellerOrders->count();

        // Pending orders count
        $pendingOrders = $sellerOrders->where('status', 'pending')->count();

        // Processing orders count
        $processingOrders = $sellerOrders->where('status', 'processing')->count();

        // Shipped orders count
        $shippedOrders = $sellerOrders->where('status', 'shipped')->count();

        // Delivered orders count
        $deliveredOrders = $sellerOrders->where('status', 'delivered')->count();

        // Cancelled orders count
        $cancelledOrders = $sellerOrders->where('status', 'cancelled')->count();

        // Calculate total revenue from seller's products
        $totalRevenue = OrderItem::whereHas('product', function($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        })->sum(DB::raw('price * quantity'));

        // Get order counts by payment method
        $ordersByPaymentMethod = $sellerOrders->groupBy('payment_method')
            ->map(function($orders) {
                return $orders->count();
            });

        // Get order counts by day for the last 30 days
        $thirtyDaysAgo = now()->subDays(30);
        $ordersByDay = Order::whereHas('items.product', function($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        })
        ->where('created_at', '>=', $thirtyDaysAgo)
        ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->pluck('count', 'date')
        ->toArray();

        // Get revenue by day for the last 30 days
        $revenueByDay = OrderItem::whereHas('product', function($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        })
        ->whereHas('order', function($query) use ($thirtyDaysAgo) {
            $query->where('created_at', '>=', $thirtyDaysAgo);
        })
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.price * order_items.quantity) as revenue')
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->pluck('revenue', 'date')
        ->toArray();

        // Return statistics
        return [
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'processingOrders' => $processingOrders,
            'shippedOrders' => $shippedOrders,
            'deliveredOrders' => $deliveredOrders,
            'cancelledOrders' => $cancelledOrders,
            'totalRevenue' => $totalRevenue,
            'ordersByPaymentMethod' => $ordersByPaymentMethod,
            'ordersByDay' => $ordersByDay,
            'revenueByDay' => $revenueByDay
        ];
    }

    // Enhanced dashboard with statistics
    public function enhancedDashboard()
    {
        $statistics = $this->getOrderStatistics();

        // Get recent orders
        $recentOrders = Order::whereHas('items.product', function($query) {
            $query->where('seller_id', auth()->id());
        })
        ->with(['items.product'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        // Get low stock products
        $lowStockProducts = Product::where('seller_id', auth()->id())
            ->where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        return view('seller.enhanced_dashboard', compact(
            'statistics', 
            'recentOrders', 
            'lowStockProducts'
        ));
    }

    // Add notification methods for order status changes
    public function getNotifications()
    {
        // In a real app, this would fetch notifications from a notifications table
        // For this example, we'll just return recent orders as notifications
        $notifications = Order::whereHas('items.product', function($query) {
            $query->where('seller_id', auth()->id());
        })
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get()
        ->map(function($order) {
            return [
                'id' => $order->id,
                'message' => "New order #{$order->id} received from {$order->full_name}",
                'time' => $order->created_at->diffForHumans(),
                'read' => false,
                'url' => route('seller.view_order', $order->id)
            ];
        });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'count' => $notifications->count()
        ]);
    }

    // Bulk update order statuses
    public function bulkUpdateOrderStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'integer',
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $orderIds = $request->order_ids;
        $status = $request->status;

        // Get orders that belong to this seller
        $orders = Order::whereIn('id', $orderIds)
            ->whereHas('items.product', function($query) {
                $query->where('seller_id', auth()->id());
            })
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('seller.order_management')
                ->with('error', 'No valid orders selected for update.');
        }

        // Update orders
        foreach ($orders as $order) {
            $order->status = $status;
            $order->save();

            // If status is 'cancelled', restore the stock
            if ($status === 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product && $item->product->seller_id == auth()->id()) {
                        if ($item->variation_id) {
                            $variation = ProductVariation::find($item->variation_id);
                            if ($variation) {
                                $variation->stock += $item->quantity;
                                $variation->save();
                            }
                        } else {
                            $item->product->stock += $item->quantity;
                            $item->product->save();
                        }
                    }
                }
            }
        }

        return redirect()->route('seller.order_management')
            ->with('success', count($orders) . ' orders updated successfully.');
    }

    // Index function for filtering products by category
    public function index(Request $request)
    {
        $categories = Category::all(); // Fetch all categories
        $category_id = $request->category_id; // Get selected category from request

        // Fetch products belonging to the logged-in seller and apply category filter
        $products = Product::with('variations')
            ->where('seller_id', auth()->id()) // Ensure only seller's products are shown
            ->when($category_id, function ($query) use ($category_id) {
                return $query->where('category_id', $category_id);
            })->get();

        return view('seller.products', compact('products', 'categories', 'category_id'));
    }
}