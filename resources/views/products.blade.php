
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Product Catalog</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            @if($products->isEmpty())
                <p>No products available.</p>
            @else
                @foreach($products as $product)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                        <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top product-image" alt="{{ $product->name }}">
                        <div class="card-body">
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="card-text">{{ $product->description }}</p>
                                <p><strong>Price:</strong> {{ $product->price }}</p>
                                <p><strong>Stock:</strong> {{ $product->stock }}</p>
                                <a href="#" class="btn btn-primary">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
