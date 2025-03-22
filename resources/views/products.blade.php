
@extends('layouts.app')

@section('content')
    <div class="container mt-5">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Filter Form -->
        <form method="GET" action="{{ route('products.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <select name="category_id" class="form-control" onchange="this.form.submit()">
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

        <div class="row">
            @if($products->isEmpty())
                <p>No products available.</p>
            @else
                @foreach($products as $product)
                    <div class="col-md-4 mb-4">
                        <div class="card">
   
                        <img src="{{ asset('images/' . $product->image . '.jpg') }}" 
     onerror="this.onerror=null; this.src='{{ asset('images/' . $product->image . '.png') }}';" 
     alt="{{ $product->name }}">

                        <div class="card-body">
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="card-text">{{ $product->description }}</p>
                                <p><strong>Price:</strong> {{ $product->price }}</p>
                                <p><strong>Stock:</strong> {{ $product->stock }}</p> 
                                <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                                </form>
                               
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delegate event listeners to handle dynamic modals
        const modalElements = document.querySelectorAll('.modal');
        
        modalElements.forEach(modal => {
            modal.addEventListener('shown.bs.modal', function () {
                const decreaseButton = modal.querySelector('.decreaseQuantity');
                const increaseButton = modal.querySelector('.increaseQuantity');
                const quantityInput = modal.querySelector('.quantity');

                // Decrease quantity
                decreaseButton.addEventListener('click', function() {
                    let currentQuantity = parseInt(quantityInput.value);
                    if (currentQuantity > 1) {
                        quantityInput.value = currentQuantity - 1;
                    }
                });

                // Increase quantity
                increaseButton.addEventListener('click', function() {
                    let currentQuantity = parseInt(quantityInput.value);
                    quantityInput.value = currentQuantity + 1;
                });
            });
        });
    });
</script>
@endsection

<!-- JavaScript for handling quantity increase and decrease -->



   