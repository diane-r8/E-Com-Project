@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="product-catalog-title">Product Catalog</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Filter Form -->
        <form method="GET" action="{{ route('products.index') }}" class="mb-4 product-filter-form">
            <div class="row">
                <div class="col-md-4">
                    <select name="category_id" class="form-control product-category-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <div class="row product-catalog-grid">
            @if($products->isEmpty())
                <p>No products available.</p>
            @else
                @foreach($products as $product)
                    <div class="col-md-4 mb-4 product-card">
                        <div class="card product-card-body">
                            <!-- Product Image Container: Centered Image -->
                            <div class="product-image-wrapper">
                                <img src="{{ asset('images/' . $product->image . '.jpg') }}" 
                                     onerror="this.onerror=null; this.src='{{ asset('images/' . $product->image . '.png') }}';" 
                                     alt="{{ $product->name }}" 
                                     class="product-image">
                            </div>

                            <!-- Product Description Container: Left Aligned Text -->
                            <div class="card-body product-description-container">
                                <h5 class="card-title product-name">{{ $product->name }}</h5>
                                <p class="card-text product-description">{{ $product->description }}</p>
                                <p><strong>Price:</strong> {{ $product->price }}</p>
                                <p><strong>Stock:</strong> {{ $product->stock }}</p>
                            </div>

                            <!-- Quick View and Add to Cart Buttons -->
                            <div class="product-actions">
                                <!-- Quick View Button with Bootstrap Icon -->
                                <button type="button" class="btn btn-light product-quick-view" data-toggle="modal" data-target="#quickViewModal{{ $product->id }}">
                                    <i class="bi bi-eye-outline"></i> Quick View
                                </button>

                                <!-- Add to Cart Button with Bootstrap Icon -->
                                <form action="{{ route('cart.add', $product->id) }}" method="POST" class="add-to-cart-form">
                                    @csrf
                                    <button type="submit" class="btn btn-primary product-add-to-cart">
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
