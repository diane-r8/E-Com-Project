@extends('layouts.app')
@section('content')
<div class="container py-5">
    <h2 class="text-center mb-4">Checkout</h2>
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="row">
        {{-- Left Column: Checkout Form --}}
        <div class="col-md-8">
            <!-- Use different form action based on checkout type -->
            @if(isset($isBuyNow) && $isBuyNow)
            <form action="{{ route('processBuyNow') }}" method="POST">
            @else
                <form action="{{ route('cart.checkout') }}" method="POST">
            @endif
                @csrf
                {{-- Full Name Field --}}
                <h5 class="mb-3">Shipping Information</h5>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required>
                </div>

                {{-- Phone Number --}}
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" class="form-control" required>
                </div>

                {{-- Delivery Area --}}
                <div class="mb-3">
                    <label for="delivery_area_id" class="form-label">Delivery Area</label>
                    <select id="delivery_area_id" name="delivery_area_id" class="form-select" required>
                        <option value="">-- Choose an Area --</option>
                        <option value="1" data-fee="50">Sto. Domingo - ₱50</option>
                        <option value="2" data-fee="100">Tabaco - ₱100</option>
                        <option value="3" data-fee="80">Bacacay - ₱80</option>
                        <option value="4" data-fee="150">Legazpi - ₱150</option>
                        <option value="5" data-fee="200">Daraga - ₱200</option>
                    </select>
                </div>

                {{-- Shipping Address --}}
                <div class="mb-3">
                    <label for="shipping_address" class="form-label">Shipping Address</label>
                    <input type="text" id="shipping_address" name="shipping_address" class="form-control" required>
                    <small class="form-text text-muted">
                        Please enter your complete shipping address, including the street name, barangay, and city/municipality. 
                        Example: "123 Main St, Barangay 7, Legazpi City, Albay."
                    </small>
                </div>

                {{-- Landmark (Optional) --}}
                <div class="mb-3">
                    <label for="landmark" class="form-label">Landmark (Optional)</label>
                    <input type="text" id="landmark" name="landmark" class="form-control">
                    <small class="form-text text-muted">
                        If applicable, you can provide a nearby landmark to help with delivery. Example: "Near the church" or "Beside the mall."
                    </small>
                </div>

                {{-- Set Default Address --}}
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                    <label class="form-check-label" for="is_default">
                        Set this as my default address
                    </label>
                </div>

                {{-- Payment Method Section --}}
                <h5 class="mb-3">Payment Method</h5>
                <div class="mb-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment_gcash" value="gcash" required>
                        <label class="form-check-label d-flex align-items-center" for="payment_gcash">
                            <img src="{{ asset('images/gcash-seeklogo.png') }}" alt="GCash" height="30" class="me-2">
                            GCash (via Xendit)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="COD" required>
                        <label class="form-check-label d-flex align-items-center" for="payment_cod">
                        <img src="{{ asset('images/cod.png') }}" alt="COD" height="30" class="me-2">
                            Cash on Delivery (COD)
                        </label>
                    </div>
                </div>

                {{-- Rush Order --}}
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="rush_order" name="rush_order">
                    <label class="form-check-label" for="rush_order">
                        Rush Order (+₱50)
                    </label>
                </div>

                {{-- Hidden Inputs --}}
                <input type="hidden" name="total_price" id="final_total_price">
                <input type="hidden" name="delivery_fee" id="final_delivery_fee">
                <input type="hidden" name="final_rush_order" id="final_rush_order" value="0">

                {{-- Submit Button --}}
                <div class="text-end">
                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-check-circle-fill"></i> Complete Purchase
                    </button>
                </div>
            </form>
        </div>

        {{-- Right Column: Order Summary --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Order Summary</h5>
                    @php $totalPrice = 0; @endphp

                    @foreach ($cart as $item)
                    @php
                        $imagePath = asset("images/default.png"); // Default image
                        if (!empty($item['image'])) {
                            $extensions = ['jpg', 'jpeg', 'png', 'webp'];
                            foreach ($extensions as $ext) {
                                if (file_exists(public_path("images/{$item['image']}.{$ext}"))) {
                                    $imagePath = asset("images/{$item['image']}.{$ext}");
                                    break;
                                }
                            }
                        }
                    @endphp
                    <div class="mb-3 p-2 border rounded bg-light d-flex">
                        <div class="me-3" style="width: 64px; height: 64px; overflow: hidden; border-radius: 0.5rem;">
                            <img src="{{ $imagePath }}" alt="{{ $item['product_name'] }}" class="img-fluid" style="object-fit: cover; width: 100%; height: 100%;">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <span>{{ $item['product_name'] }}</span>
                                <span>₱{{ number_format($item['price'], 2) }}</span>
                            </div>
                            <div class="text-muted small">
                                {{ ucfirst($item['variation_name']) }} × {{ $item['quantity'] }}
                            </div>
                            @php $totalPrice += $item['price'] * $item['quantity']; @endphp
                        </div>
                    </div>
                    @endforeach

                    <hr>

                    <ul class="list-group mb-3">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Subtotal</span>
                            <strong>₱{{ number_format($totalPrice, 2) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Delivery Fee</span>
                            <strong>₱<span id="delivery_fee">0.00</span></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Rush Order</span>
                            <strong>₱<span id="rush_fee">0.00</span></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between fs-5">
                            <span>Total</span>
                            <strong>₱<span id="total_price">{{ number_format($totalPrice, 2) }}</span></strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Order Success Modal --}}
<div class="modal fade" id="orderSuccessModal" tabindex="-1" aria-labelledby="orderSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="orderSuccessModalLabel">Order Placed Successfully!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h5>Thank you for your order!</h5>
                <p id="orderSuccessMessage">Your order has been received and is being processed.</p>
            </div>
            <div class="modal-footer justify-content-center border-0">
                <a href="{{ route('home') }}" class="btn btn-primary">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        updateTotalPrice();

        function updateTotalPrice() {
            let basePrice = parseFloat({{ $totalPrice }});
            let deliveryFee = parseFloat($('#delivery_area_id option:selected').data('fee')) || 0;
            let rushFee = $('#rush_order').is(':checked') ? 50 : 0;
            let total = basePrice + deliveryFee + rushFee;

            $('#delivery_fee').text(deliveryFee.toFixed(2));
            $('#rush_fee').text(rushFee.toFixed(2));
            $('#total_price').text(total.toFixed(2));

            $('#final_total_price').val(total);
            $('#final_delivery_fee').val(deliveryFee);
            $('#final_rush_order').val(rushFee);
        }

        $('#delivery_area_id, #rush_order').change(updateTotalPrice);
    });
</script>
@endsection
