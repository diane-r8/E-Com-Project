@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Order #{{ $order->id }}</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Order Details</h5>
                <div>
                    <a href="{{ route('seller.order_management') }}" class="btn btn-secondary">Back to Orders</a>
                    <a href="{{ route('seller.generate_invoice', $order->id) }}" class="btn btn-primary">Generate Invoice</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge 
                            @if($order->status == 'pending') bg-warning 
                            @elseif($order->status == 'processing') bg-info 
                            @elseif($order->status == 'shipped') bg-primary 
                            @elseif($order->status == 'delivered') bg-success 
                            @elseif($order->status == 'cancelled') bg-danger 
                            @else bg-secondary @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>
                    <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                    <p><strong>Payment Status:</strong> 
                        <span class="badge 
                            @if($order->payment_status == 'paid') bg-success 
                            @elseif($order->payment_status == 'pending') bg-warning 
                            @elseif($order->payment_status == 'failed') bg-danger 
                            @else bg-secondary @endif">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Customer:</strong> {{ $order->full_name }}</p>
                    <p><strong>Email:</strong> {{ $order->email }}</p>
                    <p><strong>Phone:</strong> {{ $order->phone_number }}</p>
                    <p><strong>Shipping Address:</strong> {{ $order->shipping_address }}</p>
                    @if($order->landmark)
                        <p><strong>Landmark:</strong> {{ $order->landmark }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>Order Items</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Variation</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'Product #' . $item->product_id }}</td>
                        <td>{{ $item->variation->name ?? 'N/A' }}</td>
                        <td>₱{{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₱{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                        <td>₱{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Shipping:</strong></td>
                        <td>₱{{ number_format($order->shipping_fee, 2) }}</td>
                    </tr>
                    @if($order->rush_order_fee > 0)
                    <tr>
                        <td colspan="4" class="text-end"><strong>Rush Order Fee:</strong></td>
                        <td>₱{{ number_format($order->rush_order_fee, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                        <td><strong>₱{{ number_format($order->total_price, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection