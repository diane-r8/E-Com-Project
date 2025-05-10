<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Order Receipt #{{ $order->id }}</title>
    <style>
        @font-face {
            font-family: 'Courier';
            src: local('Courier New');
            font-weight: normal;
            font-style: normal;
        }
        
        body {
            font-family: 'Courier', monospace; /* Typewriter font for everything */
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 10px;
            position: relative;
        }
        /* Watermark style */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-family: Arial, sans-serif; /* Regular font for watermark */
            font-size: 80px;
            font-weight: bold;
            color: rgba(200, 200, 200, 0.3); /* Very light gray */
            z-index: -1;
            text-align: center;
            letter-spacing: 4px; /* Wide spacing for elegant look */
            pointer-events: none;
            width: 100%;
        }

        
        .container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #333;
            margin: 0 0 5px 0;
            font-size: 16pt;
        }
        .header p {
            margin: 3px 0;
        }
        .order-details {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        th, td {
            padding: 5px 8px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-size: 9pt;
        }
        .product-col {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .total-row td {
            font-weight: bold;
            border-top: 1px solid #aaa;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9pt;
            color: #333;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .text-right {
            text-align: right;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
            margin-bottom: 8px;
            font-size: 12pt;
            color: #333;
        }
        .two-col-section {
            display: flex;
            margin-bottom: 15px;
        }
        .half-section {
            width: 48%;
        }
        .half-section:first-child {
            margin-right: 4%;
        }
        /* Extra spaces for readability with monospace */
        .monospace-spacing {
            letter-spacing: -0.5px; /* Adjust for readability in monospace */
        }
    </style>
</head>
<body>
    <!-- Text logo watermark -->
    <div class="watermark">CRAFTS N' WRAPS</div>
    
    <div class="container">
        <div class="header">
            <h1>Order Receipt</h1>
            <p>Order ID: #{{ $order->id }} &nbsp;|&nbsp; <strong>Date:</strong> {{ $currentTime->format('M d, Y - h:i A') }}</p>
        </div>
        
        <div class="section">
            <h2 class="section-title">Order Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 35%;">Product</th>
                        <th style="width: 20%;">Variation</th>
                        <th style="width: 15%;">Price</th>
                        <th style="width: 10%;">Qty</th>
                        <th style="width: 20%;" class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderItems as $item)
                    <tr>
                        <td class="product-col">{{ $item->product->name ?? 'Product #' . $item->product_id }}</td>
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
        
        <!-- Combined sections for shipping and payment -->
        <div class="two-col-section">
            <div class="half-section">
                <h2 class="section-title">Shipping Information</h2>
                <p><strong>Name:</strong> {{ $order->full_name }}</p>
                <p><strong>Phone:</strong> {{ $order->phone_number }}</p>
                <p class="monospace-spacing"><strong>Address:</strong> {{ $order->shipping_address }}</p>
                @if($order->landmark)
                    <p><strong>Landmark:</strong> {{ $order->landmark }}</p>
                @endif
            </div>
            
            <div class="half-section">
                <h2 class="section-title">Payment Information</h2>
                <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
             
                
              
            </div>
        </div>
        
        <div class="footer">
            <p>Thank you for shopping with Crafts N' Wraps!</p>
            <p>If you have any questions, please contact our customer support.</p>
            <p>This is an official receipt. Keep it for your records.</p>
        </div>
    </div>
</body>
</html>