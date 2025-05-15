@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="mb-3">From:</h6>
                    <div>Your Company Name</div>
                    <div>123 Street Name</div>
                    <div>City, ST 12345</div>
                    <div>Email: info@example.com</div>
                    <div>Phone: +1 234 567 8901</div>
                </div>
                
                <div class="col-sm-6">
                    <h6 class="mb-3">To:</h6>
                    <div>{{ $order->full_name }}</div>
                    <div>{{ $order->shipping_address }}</div>
                    <div>Email: {{ $order->email }}</div>
                    <div>Phone: {{ $order->phone_number }}</div>
                </div>
            </div>
            
            <div class="table-responsive-sm">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Description</th>
                            <th class="right">Unit Cost</th>
                            <th class="center">Qty</th>
                            <th class="right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td class="left">{{ $item->product->name ?? 'Product #' . $item->product_id }}</td>
                            <td class="left">{{ $item->variation->name ?? 'Standard' }}</td>
                            <td class="right">₱{{ number_format($item->price, 2) }}</td>
                            <td class="center">{{ $item->quantity }}</td>
                            <td class="right">₱{{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="row">
                <div class="col-lg-4 col-sm-5"></div>
                <div class="col-lg-4 col-sm-5 ml-auto">
                    <table class="table table-clear">
                        <tbody>
                            <tr>
                                <td class="left"><strong>Subtotal</strong></td>
                                <td class="right">₱{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="left"><strong>Shipping</strong></td>
                                <td class="right">₱{{ number_format($order->shipping_fee, 2) }}</td>
                            </tr>
                            @if($order->rush_order_fee > 0)
                            <tr>
                                <td class="left"><strong>Rush Order Fee</strong></td>
                                <td class="right">₱{{ number_format($order->rush_order_fee, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="left"><strong>Total</strong></td>
                                <td class="right"><strong>₱{{ number_format($order->total_price, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <div>
                    <h6>Terms and Conditions</h6>
                    <p>Thank you for your business.</p>
                </div>
                <div>
                    <a href="{{ route('seller.order_management') }}" class="btn btn-secondary">Back to Orders</a>
                    <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection