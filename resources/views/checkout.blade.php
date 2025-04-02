@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Checkout</h2>

    <div class="card">
        <div class="card-body">
            <h4>{{ $product->name }} - {{ ucfirst($variation->name) }}</h4>
            <p><strong>Price:</strong> ₱{{ number_format($variation->price, 2) }}</p>
            <p><strong>Quantity:</strong> {{ $quantity }}</p>
            <p><strong>Total Price:</strong> ₱{{ number_format($totalPrice, 2) }}</p>

            <form action="{{ route('payment.process') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                <input type="hidden" name="quantity" value="{{ $quantity }}">
                <input type="hidden" name="total_price" value="{{ $totalPrice }}">

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Proceed to Payment
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
