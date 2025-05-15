<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SellerDashboardController extends Controller
{
        public function index(Request $request)
    {
        $sellerId = Auth::id();
        $status = $request->input('status', 'all');
        $period = $request->input('period', '30days');

        // Set date range based on period
        $startDate = null;
        $endDate = Carbon::now()->endOfDay();
        
        switch($period) {
            case '7days':
                $startDate = Carbon::now()->subDays(6)->startOfDay();
                break;
            case '30days':
                $startDate = Carbon::now()->subDays(29)->startOfDay();
                break;
            case '90days':
                $startDate = Carbon::now()->subDays(89)->startOfDay();
                break;
            case 'ytd':
                $startDate = Carbon::now()->startOfYear()->startOfDay();
                break;
            default:
                $startDate = Carbon::now()->subDays(29)->startOfDay();
        }

        // Get orders query
        $orders = Order::query();
        
        // Apply date filters
        $orders->whereBetween('created_at', [$startDate, $endDate]);
        
        // Apply status filter if not 'all'
        if ($status !== 'all') {
            $orders = $orders->where('status', $status);
        }

        // Get total sales calculation
        $totalSales = $orders->sum('total_price');
        
        // Get orders for display with pagination
        $displayOrders = clone $orders;
        $displayOrders = $displayOrders->orderBy('created_at', 'desc')->paginate(10);

        // Get total orders count
        $totalOrders = Order::count();

        // Get total products count
        $totalProducts = Product::count();
        
        // Get total added to cart count
        $totalAddedToCart = CartItem::count();

        // Get order status distribution
        $orderStatusDistribution = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst($item->status),
                    'count' => $item->count
                ];
            });

        // Get sales trend data (daily for the selected period)
        $salesTrend = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as daily_sales')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Format the dates and ensure all dates in range are included (even with zero sales)
        $formattedSalesTrend = [];
        $dateLabels = [];
        $salesData = [];
        
        // Create a period range of all dates
        $dateRange = Carbon::parse($startDate)->daysUntil(Carbon::parse($endDate)->endOfDay());
        
        // Initialize all dates with zero sales
        foreach($dateRange as $date) {
            $dateStr = $date->format('Y-m-d');
            $formattedSalesTrend[$dateStr] = 0;
            $dateLabels[] = $date->format('M d');
        }
        
        // Fill in actual sales data
        foreach($salesTrend as $day) {
            $formattedSalesTrend[$day->date] = $day->daily_sales;
        }
        
        // Convert to sequential arrays for Chart.js
        foreach($formattedSalesTrend as $sales) {
            $salesData[] = $sales;
        }

        // Get top selling products
        $topProducts = OrderItem::select(
                'products.id', 
                'products.name', 
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
            
        $topProductNames = $topProducts->pluck('name')->toArray();
        $topProductSales = $topProducts->pluck('total_sold')->toArray();

        // Get recent orders
        $recentOrders = Order::with(['items.product', 'items.variation'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Count recent orders by status for summary cards
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $shippedOrders = Order::where('status', 'shipped')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();

        return view('seller.dashboard', compact(
            'displayOrders', 
            'totalOrders', 
            'totalProducts', 
            'totalAddedToCart', 
            'totalSales',
            'orderStatusDistribution',
            'dateLabels',
            'salesData',
            'topProductNames',
            'topProductSales',
            'recentOrders',
            'pendingOrders',
            'processingOrders',
            'shippedOrders',
            'deliveredOrders'
        ));
    }
    
    /**
     * AJAX endpoint to fetch filtered dashboard data
     */
    public function getFilteredData(Request $request)
    {
        $period = $request->input('period', '30days');
        $status = $request->input('status', 'all');
        
        // Set date range based on period
        $startDate = null;
        $endDate = Carbon::now()->endOfDay();
        
        switch($period) {
            case '7days':
                $startDate = Carbon::now()->subDays(6)->startOfDay();
                break;
            case '30days':
                $startDate = Carbon::now()->subDays(29)->startOfDay();
                break;
            case '90days':
                $startDate = Carbon::now()->subDays(89)->startOfDay();
                break;
            case 'ytd':
                $startDate = Carbon::now()->startOfYear()->startOfDay();
                break;
            default:
                $startDate = Carbon::now()->subDays(29)->startOfDay();
        }
        
        // Base query
        $ordersQuery = Order::query();
        
        // Apply date filter
        $ordersQuery->whereBetween('created_at', [$startDate, $endDate]);
        
        // Apply status filter if not 'all'
        if ($status !== 'all') {
            $ordersQuery->where('status', $status);
        }
        
        // Get totals
        $totalSales = $ordersQuery->sum('total_price');
        $totalOrders = $ordersQuery->count();
        
        // Get order status distribution
        $orderStatusDistribution = Order::select('status', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst($item->status),
                    'count' => $item->count
                ];
            });
        
        // Get sales trend data
        $salesTrend = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as daily_sales')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Format dates and ensure all dates in range are included
        $formattedSalesTrend = [];
        $dateLabels = [];
        $salesData = [];
        
        // Create a period range of all dates
        $dateRange = Carbon::parse($startDate)->daysUntil(Carbon::parse($endDate)->endOfDay());
        
        // Initialize all dates with zero sales
        foreach($dateRange as $date) {
            $dateStr = $date->format('Y-m-d');
            $formattedSalesTrend[$dateStr] = 0;
            $dateLabels[] = $date->format('M d');
        }
        
        // Fill in actual sales data
        foreach($salesTrend as $day) {
            $formattedSalesTrend[$day->date] = $day->daily_sales;
        }
        
        // Convert to sequential arrays for Chart.js
        foreach($formattedSalesTrend as $sales) {
            $salesData[] = $sales;
        }
        
        // Get top selling products
        $topProducts = OrderItem::select(
                'products.id', 
                'products.name', 
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
            
        return response()->json([
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'orderStatusDistribution' => $orderStatusDistribution,
            'salesTrend' => [
                'labels' => $dateLabels,
                'data' => $salesData
            ],
            'topProducts' => [
                'labels' => $topProducts->pluck('name'),
                'data' => $topProducts->pluck('total_sold')
            ]
        ]);
    }
}