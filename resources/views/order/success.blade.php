@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        <h2 class="mt-3">Thank You for Your Order!</h2>
                        <p class="lead">Your order has been placed successfully.</p>
                        <p class="mb-0">Order ID: #{{ $order->id }}</p>
                        <p>Date: {{ $order->created_at->format('M d, Y, h:i A') }}</p>
                    </div>

                    @if($order->payment_method == 'gcash' && isset($order->payment_status) && $order->payment_status != 'Paid')
                        <div class="alert alert-info text-center">
                            <p class="mb-2"><strong>Payment Status:</strong> Pending</p>
                            <p>Please complete your GCash payment to process your order.</p>
                        </div>
                    @endif

                    <h4 class="border-bottom pb-2">Order Summary</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Variation</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderItems as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'Product #' . $item->product_id }}</td>
                                    <td>{{ $item->variation->name ?? 'N/A' }}</td>
                                    <td>₱{{ number_format($item->price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-end">₱{{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end">₱{{ number_format($order->total_price - $order->delivery_fee - $order->rush_order_fee, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Delivery Fee:</strong></td>
                                    <td class="text-end">₱{{ number_format($order->delivery_fee, 2) }}</td>
                                </tr>
                                @if($order->rush_order_fee > 0)
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Rush Order Fee:</strong></td>
                                    <td class="text-end">₱{{ number_format($order->rush_order_fee, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong>₱{{ number_format($order->total_price, 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h4 class="border-bottom pb-2 mt-4">Shipping Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $order->full_name }}</p>
                            <p><strong>Phone:</strong> {{ $order->phone_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Address:</strong> {{ $order->shipping_address }}</p>
                            @if($order->landmark)
                                <p><strong>Landmark:</strong> {{ $order->landmark }}</p>
                            @endif
                        </div>
                    </div>

                    <h4 class="border-bottom pb-2 mt-4">Payment Information</h4>
                    <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                    @if(isset($order->payment_status))
                        <p><strong>Payment Status:</strong> {{ $order->payment_status }}</p>
                    @endif

                    <div class="text-center mt-4">
                        <a href="{{ url('/products') }}" class="btn btn-primary">Continue Shopping</a>
                        <a href="{{ route('order.download-receipt', ['orderId' => $order->id]) }}" class="btn btn-success">
                            <i class="fas fa-download me-1"></i> Download Receipt
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection