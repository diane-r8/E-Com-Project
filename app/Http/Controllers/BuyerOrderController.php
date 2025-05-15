<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BuyerOrderController extends Controller
{
        public function index()
    {
        $orders = auth()->user()->orders()->with('items.product.variation')->get();
        
        return view('buyers.orders.index', compact('orders'));

    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return view('buyers.orders.show', compact('order'));
    }

}