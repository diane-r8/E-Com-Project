<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class NotificationController extends Controller
{
    /**
     * Get count of new pending orders
     */
    public function getNewOrdersCount()
    {
        // Get the last time notifications were viewed (or default to 24 hours ago)
        $lastViewed = Session::get('last_orders_notification_viewed', now()->subDay());
        
        // Count new pending orders since last viewed time
        $count = Order::where('created_at', '>=', $lastViewed)
            ->where('status', 'pending')
            ->count();
        
        return response()->json(['count' => $count]);
    }
    
    /**
     * Mark order notifications as viewed (resets the counter)
     */
    public function markOrderNotificationsAsRead()
    {
        // Update the last viewed timestamp
        Session::put('last_orders_notification_viewed', now());
        
        return response()->json(['success' => true]);
    }
} 