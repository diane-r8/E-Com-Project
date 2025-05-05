@extends('layouts.app')

@section('content')
    <div class="container mt-5">

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
                    <!-- For medium and large screens, 4 cards per row -->
                    <div class="col-md-3 col-sm-6 col-12 mb-3 product-card">
                        <div class="card product-card-body">
                            <!-- Product Image Container -->
                            <div class="product-image-wrapper">
                                <img src="{{ asset('images/' . $product->image . '.jpg') }}" 
                                     onerror="this.onerror=null; this.src='{{ asset('images/' . $product->image . '.png') }}';" 
                                     alt="{{ $product->name }}" 
                                     class="product-image">
                            </div>

                            <!-- Product Name, Price, Quick View and Add to Cart Buttons -->
                            <div class="card-body product-info-container">
                                <h5 class="card-title product-name">{{ $product->name }}</h5>
                                <p class="product-price">₱{{ number_format($product->price, 2) }}</p>

                                <!-- Product Actions (Quick View and Add to Cart) -->
                                <div class="product-actions">
                                    <!-- Quick View Button -->
                                    <button type="button" class="btn btn-secondary product-quick-view" data-bs-toggle="modal" data-bs-target="#quickViewModal{{ $product->id }}">
                                        <i class="bi bi-eye"></i> Quick View
                                    </button>

                                    <!-- Add to Cart Button -->
                                  <!-- Updated outside "Add to Cart" button to trigger modal instead -->
<button type="button" class="bi bi-cart-plus product-add-to-cart" onclick="openQuickView({{ $product->id }})">
    Add to Cart
</button>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick View Modal -->
                    <div class="modal fade" id="quickViewModal{{ $product->id }}" tabindex="-1" aria-labelledby="quickViewModalLabel{{ $product->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document"> <!-- modal-lg for larger size -->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="quickViewModalLabel{{ $product->id }}">{{ $product->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <!-- Left Column (Product Image) -->
                                        <div class="col-md-6">
                                            <img src="{{ asset('images/' . $product->image . '.jpg') }}" 
                                                 onerror="this.onerror=null; this.src='{{ asset('images/' . $product->image . '.png') }}';" 
                                                 alt="{{ $product->name }}" 
                                                 class="img-fluid mb-3">
                                        </div>

                                        <!-- Right Column (Product Details) -->
                                        <div class="col-md-6">
                                            <!-- Product Name -->
                                            <h4>{{ $product->name }}</h4>

                                            <!-- Rating -->
                                            <div class="product-rating mb-3">
                                                <span>⭐⭐⭐⭐⭐ {{ number_format($product->rating, 1) }}</span>
                                            </div>

                                            <!-- Price -->
                                            <p><strong>Price:</strong> ₱{{ number_format($product->price, 2) }}</p>

                                            <!-- Description -->
                                            <p><strong>Description:</strong> {{ $product->description }}</p>

                                            <!-- Variations (Radio Buttons) -->
                                            <div class="form-group">
                                                <label for="variations">Variations:</label>
                                                <div id="variations{{ $product->id }}">
                                                    @foreach($product->variations as $variation)
                                                        <label class="btn btn-outline-secondary">
                                                            <input type="radio" name="variation_id_{{ $product->id }}" 
                                                                   value="{{ $variation->id }}" autocomplete="off" required> 
                                                            {{ ucfirst($variation->name) }} (₱{{ number_format($variation->price, 2) }})
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!-- Quantity Selector -->
                                            <div class="form-group">
                                                <label for="quantity">Quantity:</label>
                                                <div class="input-group">
                                                    <button class="btn btn-secondary decreaseQuantity" type="button">-</button>
                                                    <input type="number" class="form-control quantity" value="1" min="1">
                                                    <button class="btn btn-secondary increaseQuantity" type="button">+</button>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="modal-footer">
                                            <form action="{{ route('cart.add', $product->id) }}" method="POST" onsubmit="return validateVariation({{ $product->id }})">
    @csrf
    <input type="hidden" name="variation_id" class="selectedVariation" data-product-id="{{ $product->id }}">
    <input type="hidden" name="quantity" class="selectedQuantity" data-product-id="{{ $product->id }}" value="1">
    <button type="submit" class="btn btn-custom-add-to-cart">
        <i class="bi bi-cart-plus"></i> Add to Cart
    </button>
</form>

<form action="{{ route('buy-now') }}" method="POST" onsubmit="return validateVariation({{ $product->id }})">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    <input type="hidden" name="variation_id" class="selectedVariation" data-product-id="{{ $product->id }}">
    <input type="hidden" name="quantity" class="selectedQuantity" data-product-id="{{ $product->id }}" value="1">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-credit-card"></i> Buy Now
    </button>
</form>


                                                <button type="button" class="btn btn-custom-continue-shopping" data-bs-dismiss="modal">
                                                    <i class="bi bi-arrow-left-circle"></i> Continue Shopping
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @endforeach
            @endif
        </div>
    </div>


 

    <!-- JavaScript for handling variation selection & quantity -->
   
 <script>
document.addEventListener("DOMContentLoaded", function() {
    // Handle variation selection
    document.querySelectorAll("[id^='variations']").forEach(variationGroup => {
        variationGroup.addEventListener("change", function(event) {
            let productId = variationGroup.id.replace("variations", ""); 
            let selectedVariation = event.target.value;

            // Set value on all matching hidden fields
            document.querySelectorAll(".selectedVariation[data-product-id='" + productId + "']").forEach(input => {
                input.value = selectedVariation;
            });
        });
    });

    // Handle quantity buttons
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function () {
            const decreaseButton = modal.querySelector('.decreaseQuantity');
            const increaseButton = modal.querySelector('.increaseQuantity');
            const quantityInput = modal.querySelector('.quantity');
            const productId = modal.id.replace("quickViewModal", "");

            const updateHiddenQuantity = () => {
                document.querySelectorAll(".selectedQuantity[data-product-id='" + productId + "']").forEach(input => {
                    input.value = quantityInput.value;
                });
            };

            decreaseButton.onclick = () => {
                let currentQuantity = parseInt(quantityInput.value);
                if (currentQuantity > 1) {
                    quantityInput.value = currentQuantity - 1;
                    updateHiddenQuantity();
                }
            };

            increaseButton.onclick = () => {
                let currentQuantity = parseInt(quantityInput.value);
                quantityInput.value = currentQuantity + 1;
                updateHiddenQuantity();
            };

            quantityInput.oninput = updateHiddenQuantity;
        });
    });
});

// Ensure variation is selected before submitting
function validateVariation(productId) {
    let selected = document.querySelector(".selectedVariation[data-product-id='" + productId + "']");
    if (!selected || !selected.value) {
        alert("Please select a variation before proceeding.");
        return false;
    }
    return true;
}

// Trigger Quick View modal
function openQuickView(productId) {
    var modal = new bootstrap.Modal(document.getElementById("quickViewModal" + productId));
    modal.show();
}
</script>

@endsection



