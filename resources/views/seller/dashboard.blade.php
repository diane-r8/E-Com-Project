
@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <!-- Existing content... -->


<div class="container mt-4">
    <div class="row">
        <!-- Total Orders -->
        <div class="col-md-3">
            <div class="card shadow-sm p-3 text-center">
                <h5><i class="fas fa-shopping-cart"></i> Total Orders</h5>
                <p>{{ $totalOrders }}</p>
            </div>
        </div>
        <!-- Total Sales -->
        <div class="col-md-3">
            <div class="card shadow-sm p-3 text-center">
                <h5><i class="fas fa-wallet"></i> Total Sales</h5>
                <p>₱0.00</p>
            </div>
        </div>
        <!-- Total Products with View Button -->
        <div class="col-md-3">
            <div class="card shadow-sm p-3 text-center">
                <h5><i class="fas fa-box"></i> Total Products</h5>
                <p>{{ $totalProducts }}</p>
                <a href="{{ route('seller.products') }}" class="view-icon">
                    <i class="bi bi-eye"></i> View
                </a>
            </div>
        </div>
        <!-- Added to Cart with View Button -->
        <div class="col-md-3">
            <div class="card shadow-sm p-3 text-center">
                <h5><i class="fas fa-shopping-basket"></i> Added to Cart</h5>
                <p>{{ $totalAddedToCart }}</p>
            </div>
        </div>
    </div>
</div> 

<!-- Manage Orders Dropdown -->
<div class="container mx-auto p-6">
    <div class="mb-6">
        <label for="orderFilter" class="block text-gray-700 font-semibold mb-2">Filter Orders:</label>
        <select id="orderFilter" class="w-full p-2 border rounded">
            @php
                $tabs = ['all' => 'All Orders', 'new' => 'New', 'processing' => 'Processing', 'completed' => 'Completed', 'canceled' => 'Canceled', 'refunded' => 'Refunded'];
            @endphp
            @foreach($tabs as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>


    <!-- Orders Table -->
    <div class="bg-white shadow-sm rounded-lg p-4 mt-4">
        <table class="w-full text-left border border-gray-300">
            <thead class="bg-gray-200 text-gray-700 uppercase font-semibold">
                <tr class="border border-gray-300">
                    <th class="py-3 px-4 border border-gray-300">Order ID</th>
                    <th class="py-3 px-4 border border-gray-300">Customer</th>
                    <th class="py-3 px-4 border border-gray-300">Product</th>
                    <th class="py-3 px-4 border border-gray-300">Order Date</th>
                    <th class="py-3 px-4 border border-gray-300">Amount</th>
                    <th class="py-3 px-4 border border-gray-300">Payment</th>
                    <th class="py-3 px-4 border border-gray-300">Status</th>
                    <th class="py-3 px-4 border border-gray-300">Action</th>
                </tr>
            </thead>
            <tbody id="orderTableBody">
                @if(empty($orders) || count($orders) == 0)
                    <tr>
                        <td colspan="8" class="text-center py-4 text-gray-500 border border-gray-300">No orders yet.</td>
                    </tr>
                @else
                    @foreach($orders as $order)
                        <tr>
                            <td class="py-2 px-4 border border-gray-300">{{ $order->id }}</td>
                             <!-- Display Customer Full Name -->
                            <td class="py-2 px-4 border border-gray-300">{{ $order->full_name }}</td>
                             <!-- Display Product Name and Quantity -->
                            <td class="py-2 px-4 border border-gray-300">
                                @if($order->items && $order->items->count() > 0)
                                    @foreach($order->items as $item)
                                        <p>{{ $item->product ? $item->product->name : 'Product not found' }} - {{ $item->quantity }}
                                        @if($item->variation)
                                            ({{ $item->variation->name }})
                                        @endif
                                        </p>
                                    @endforeach
                                @else
                                    <p>No products available for this order</p>
                                @endif
                            </td>
                            <td class="py-2 px-4 border border-gray-300">{{ $order->created_at->format('Y-m-d') }}</td>
                            <td class="py-2 px-4 border border-gray-300">₱{{ number_format($order->total_price, 2) }}</td>
                            <td class="py-2 px-4 border border-gray-300">{{ $order->payment_method }}</td>
                            <td class="py-2 px-4 border border-gray-300">
                                <span class="{{ 'text-' . strtolower($order->status) }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="py-2 px-4 border border-gray-300">

                                @php
                                    $items = $order->items->map(function ($item) {
                                        return [
                                            'product' => $item->product->name ?? 'Unknown Product',
                                            'variation' => $item->variation?->name ?? 'N/A',
                                            'price' => $item->price,
                                            'quantity' => $item->quantity,
                                            'subtotal' => $item->price * $item->quantity,
                                        ];
                                    });
                                @endphp

                                <!-- Edit Button to Show Modal -->                               
                                <button 
                                    type="button"
                                    class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition mr-2 show-summary"
                                    data-order-id="{{ $order->id }}"
                                    data-order-date="{{ $order->created_at->format('F j, Y g:i A') }}"
                                    data-status="{{ ucfirst($order->status) }}" {{-- Capitalize status --}}
                                    data-customer="{{ $order->full_name }}"
                                    data-phone="{{ $order->phone_number ?? 'N/A' }}"
                                    data-address="{{ $order->shipping_address ?? 'N/A' }}"
                                    data-payment="{{ $order->payment_method }}"
                                    data-delivery-fee="{{ $order->delivery_fee }}"
                                    data-total-price="{{ $order->total_price }}"
                                    data-subtotal="{{ $order->items->sum(fn($item) => $item->price * $item->quantity) }}"
                                    data-items='@json($items)'
                                >
                                    Edit
                                </button>
                                <!-- Delete order -->
                                <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        @if(!empty($orders) && count($orders) >= 10)
            <div class="mt-4 text-center">
                <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition" id="loadMore">Load More</button>
            </div>
        @endif
    </div>
</div>

<!-- Order Summary Modal -->
<div id="orderModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center" style="display: none;">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Order Summary</h3>
            <button id="closeModal" class="text-gray-500 hover:text-red-600 text-xl font-bold">&times;</button>
        </div>
        
        <p><strong>Order ID:</strong> <span id="modalOrderId"></span></p>
        <p><strong>Order Date:</strong> <span id="modalOrderDate"></span></p>
        <p><strong>Customer:</strong> <span id="modalCustomer"></span></p>
        <p><strong>Phone:</strong> <span id="modalPhone"></span></p>
        <p><strong>Address:</strong> <span id="modalAddress"></span></p>

        <table class="w-full text-sm mb-4 border" id="modalProductTable">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-1">Product</th>
                    <th class="text-left p-1">Variation</th>
                    <th class="text-right p-1">Price</th>
                    <th class="text-right p-1">Qty</th>
                    <th class="text-right p-1">Subtotal</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <p><strong>Subtotal:</strong> ₱<span id="modalSubtotal"></span></p>
        <p><strong>Delivery Fee:</strong> ₱<span id="modalDeliveryFee"></span></p>
        <p><strong>Total:</strong> ₱<span id="modalTotalPrice"></span></p>

        <p><strong>Payment:</strong> <span id="modalPayment"></span></p>
        <p><strong>Status:</strong> <span id="modalStatus"></span></p>

        <div class="mt-4">
            <button class="status-btn processing bg-yellow-500 text-white px-3 py-1 rounded">Processing</button>
            <button class="status-btn completed bg-green-500 text-white px-3 py-1 rounded">Completed</button>
            <button class="status-btn canceled bg-red-500 text-white px-3 py-1 rounded">Canceled</button>
        </div>

    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('orderModal');

    document.querySelectorAll('.show-summary').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('modalOrderId').textContent = this.dataset.orderId;
            document.getElementById('modalOrderDate').textContent = this.dataset.orderDate;
            document.getElementById('modalCustomer').textContent = this.dataset.customer;
            document.getElementById('modalPhone').textContent = this.dataset.phone;
            document.getElementById('modalAddress').textContent = this.dataset.address;
            document.getElementById('modalPayment').textContent = this.dataset.payment;
            document.getElementById('modalSubtotal').textContent = parseFloat(this.dataset.subtotal).toFixed(2);
            document.getElementById('modalDeliveryFee').textContent = parseFloat(this.dataset.deliveryFee).toFixed(2);
            document.getElementById('modalTotalPrice').textContent = parseFloat(this.dataset.totalPrice).toFixed(2);
            document.getElementById('modalStatus').textContent = this.dataset.status;


            // Fill in product table
            const tbody = document.querySelector('#modalProductTable tbody');
            tbody.innerHTML = '';
            const items = JSON.parse(this.dataset.items);
            items.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="p-1">${item.product}</td>
                    <td class="p-1">${item.variation}</td>
                    <td class="p-1 text-right">₱${parseFloat(item.price).toFixed(2)}</td>
                    <td class="p-1 text-right">${item.quantity}</td>
                    <td class="p-1 text-right">₱${parseFloat(item.subtotal).toFixed(2)}</td>
                `;
                tbody.appendChild(row);
            });

            modal.style.display = 'flex';
        });
    });

    // 🧹 Properly attach both close buttons
    document.getElementById('closeModal').addEventListener('click', function () {
        modal.style.display = 'none';
    });

    // ✅ Filter logic (outside of the loop)
    const orderFilter = document.getElementById('orderFilter');
    if (orderFilter) {
        orderFilter.addEventListener('change', function () {
            window.location.href = `?status=${this.value}`;
        });
    }
});

</script>

<style>
.text-processing { color: #d69e2e; } /* Yellow */
.text-completed { color: #38a169; } /* Green */
.text-canceled { color: #e53e3e; } /* Red */

.card {
    position: relative;
    transition: all 0.3s ease-in-out;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card:hover {
    transform: scale(1.05);
    box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
}

.card:hover h5,
.card:hover p {
    filter: blur(4px);
    transition: filter 0.3s ease-in-out;
}

.view-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 24px;
    color: #007bff;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}

.card:hover .view-icon {
    opacity: 1;
}

.view-icon:hover {
    color: #0056b3;
}
#orderModal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black background */
    z-index: 9999;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    padding: 20px; /* Padding around the inner box */
    overflow: auto; /* In case modal content is taller than screen */
}

#orderModal .bg-white {
    background-color: white;
    margin: auto;
    max-width: 800px; /* ← adjust this to control the modal content width */
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);

    position: relative; /* Needed so absolute positioning is relative to this container */
}

#closeModal {
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
}


</style>

@endsection
