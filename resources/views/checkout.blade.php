@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Checkout</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('placeOrder') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Cart Items Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($cartItems as $id => $item)
                    @php $subtotal = $item['price'] * $item['quantity']; @endphp
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>₱{{ number_format($item['price'], 2) }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>₱{{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @php $total += $subtotal; @endphp
                @endforeach
            </tbody>
        </table>

        <!-- Delivery Area -->
        <div class="form-group">
            <label for="delivery_area_id">Select Delivery Area:</label>
            <select name="delivery_area_id" id="delivery_area_id" class="form-control" required>
                @foreach($deliveryAreas as $area)
                    <option value="{{ $area->id }}" data-fee="{{ $area->delivery_fee }}">
                        {{ $area->area_name }} - ₱{{ number_format($area->delivery_fee, 2) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Phone Number -->
        <div class="form-group">
            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone_number" class="form-control" required>
        </div>

        <!-- Payment Method -->
        <div class="form-group">
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" class="form-control" required>
                <option value="COD">Cash on Delivery</option>
                <option value="GCash">GCash</option>
            </select>
        </div>

        <!-- GCash Proof of Payment -->
        <div class="form-group d-none" id="gcashProof">
            <label for="proof_of_payment">Upload GCash Proof of Payment:</label>
            <input type="file" name="proof_of_payment" id="proof_of_payment" class="form-control" accept="image/*">
        </div>

        <!-- Rush Order Option -->
        <div class="form-group">
            <input type="checkbox" id="rush_order" name="rush_order" value="1"> 
            <label for="rush_order">Rush Order (+ ₱50)</label>
        </div>

        <!-- Total Price -->
        <div class="text-right">
            <h5>Total Price: ₱<span id="totalPrice">{{ number_format($total, 2) }}</span></h5>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-success" id="placeOrderBtn">Place Order</button>
    </form>
</div>

<!-- JavaScript for Dynamic Pricing & GCash Upload Handling -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paymentMethod = document.querySelector('#payment_method');
    const gcashProof = document.querySelector('#gcashProof');
    const deliveryArea = document.querySelector('#delivery_area_id');
    const rushOrderCheckbox = document.querySelector('#rush_order');
    const totalPriceElement = document.querySelector('#totalPrice');
    const placeOrderBtn = document.querySelector('#placeOrderBtn');
    let baseTotal = {{ $total }}; 
    let rushOrderFee = 0;
    let deliveryFee = parseFloat(deliveryArea.options[deliveryArea.selectedIndex].dataset.fee);

    // ✅ Show/Hide GCash Proof Input
    paymentMethod.addEventListener('change', function () {
        gcashProof.classList.toggle('d-none', this.value !== 'GCash');
    });

    // ✅ Update Total Price on Delivery Area Change
    deliveryArea.addEventListener('change', function () {
        deliveryFee = parseFloat(this.options[this.selectedIndex].dataset.fee);
        updateTotalPrice();
    });

    // ✅ Add Rush Order Fee if Checked
    rushOrderCheckbox.addEventListener('change', function () {
        rushOrderFee = this.checked ? 50 : 0;
        updateTotalPrice();
    });

    // ✅ Update Total Price Calculation
    function updateTotalPrice() {
        let finalTotal = baseTotal + deliveryFee + rushOrderFee;
        totalPriceElement.textContent = `₱${finalTotal.toFixed(2)}`;
    }

    // ✅ Validate GCash Proof Before Submitting
    placeOrderBtn.addEventListener('click', function (event) {
        if (paymentMethod.value === 'GCash') {
            const proofInput = document.querySelector('#proof_of_payment');
            if (!proofInput.files.length) {
                alert('Please upload proof of payment for GCash.');
                event.preventDefault();
            }
        }
    });

    // Initial Total Price Calculation
    updateTotalPrice();
});
</script>
@endsection
