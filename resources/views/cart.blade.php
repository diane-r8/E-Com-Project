@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Your Shopping Cart</h2>

    <!-- Show success messages -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(empty($cart))
        <p>Your cart is empty.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cart as $id => $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>₱{{ number_format($item['price'], 2) }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>
                            <form action="{{ route('cart.remove', $id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <form action="{{ route('cart.clear') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-warning">Clear Cart</button>
        </form>
    @endif
</div>
@endsection
