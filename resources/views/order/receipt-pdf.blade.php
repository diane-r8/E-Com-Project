<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Receipt #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #4a5568;
            margin-bottom: 5px;
        }
        .order-details {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .total-row td {
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #718096;
        }
        .text-right {
            text-align: right;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 15px;
            font-size: 18px;
            color: #2d3748;
        }
        .col {
            width: 50%;
            float: left;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Order Receipt</h1>
            <p>Order ID: #{{ $order->id }}</p>
            <p><strong>Date:</strong> {{ $currentTime->format('F d, Y - h:i A') }}</p>
        </div>
        
        <div class="section">
            <h2 class="section-title">Order Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Variation</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderItems as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'Product #' . $item->product_id }}</td>
                        <td>{{ $item->variation->name ?? 'N/A' }}</td>
                        <td>PHP{{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td class="text-right">PHP{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="text-right">Subtotal:</td>
                        <td class="text-right">PHP{{ number_format($order->total_price - $order->delivery_fee - $order->rush_order_fee, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-right">Delivery Fee:</td>
                        <td class="text-right">PHP{{ number_format($order->delivery_fee, 2) }}</td>
                    </tr>
                    @if($order->rush_order_fee > 0)
                    <tr>
                        <td colspan="4" class="text-right">Rush Order Fee:</td>
                        <td class="text-right">PHP{{ number_format($order->rush_order_fee, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td colspan="4" class="text-right">Total:</td>
                        <td class="text-right">PHP{{ number_format($order->total_price, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h2 class="section-title">Shipping Information</h2>
            <div class="col">
                <p><strong>Name:</strong> {{ $order->full_name }}</p>
                <p><strong>Phone:</strong> {{ $order->phone_number }}</p>
            </div>
            <div class="col">
                <p><strong>Address:</strong> {{ $order->shipping_address }}</p>
                @if($order->landmark)
                    <p><strong>Landmark:</strong> {{ $order->landmark }}</p>
                @endif
            </div>
            <div style="clear: both;"></div>
        </div>
        
        <div class="section">
            <h2 class="section-title">Payment Information</h2>
            <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
            @if(isset($order->payment_status))
                <p><strong>Payment Status:</strong> {{ $order->payment_status }}</p>
            @endif
        </div>
        
        <div class="footer">
        <p>Thank you for shopping with Crafts N' Wraps!</p>
            <p>If you have any questions, please contact our customer support.</p>
            <p>This is an official receipt. Keep it for your records.</p>
        </div>
    </div>
</body>
</html>