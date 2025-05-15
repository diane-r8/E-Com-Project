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
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
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
                    <div class="col-md-3 col-sm-6 col-12 mb-3 product-card">
                        <div class="card product-card-body">
                            <div class="product-image-wrapper">
                                <img src="{{ asset('images/' . $product->image . '.jpg') }}" 
                                     onerror="this.onerror=null; this.src='{{ asset('images/' . $product->image . '.png') }}';" 
                                     alt="{{ $product->name }}" 
                                     class="product-image">
                            </div>
                            <div class="card-body product-info-container">
                                <h5 class="card-title product-name">{{ $product->name }}</h5>
                                <p class="product-price">₱{{ number_format($product->price, 2) }}</p>

                                <!-- Quick View Trigger Button -->
                                <button type="button" class="product-action-button product-add-to-cart" onclick="openQuickView({{ $product->id }})">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Quick View Modal -->
                    <div class="modal fade" id="quickViewModal{{ $product->id }}" tabindex="-1" aria-labelledby="quickViewModalLabel{{ $product->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content product-modal">
                                <div class="modal-header">
                                    <h5 class="modal-title product-modal-title" id="quickViewModalLabel{{ $product->id }}">{{ $product->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-0">
                                    <div class="row g-0">
                                        <!-- Left: Product Image with Gallery View -->
                                        <div class="col-md-6 product-modal-image-container">
                                            <img src="{{ asset('images/' . $product->image . '.jpg') }}" 
                                                 onerror="this.onerror=null; this.src='{{ asset('images/' . $product->image . '.png') }}';" 
                                                 alt="{{ $product->name }}" 
                                                 class="product-modal-image">
                                            <div class="product-image-overlay">
                                                <span class="zoom-icon"><i class="bi bi-zoom-in"></i></span>
                                            </div>
                                        </div>

                                        <!-- Right: Product Details -->
                                        <div class="col-md-6 product-modal-details">
                                            <div class="p-4">
                                                <div class="product-badges mb-2">
                                                    <span class="badge bg-success">In Stock</span>
                                                    @if($product->average_rating >= 4.5)
                                                    <span class="badge bg-primary">Top Rated</span>
                                                    @endif
                                                </div>
                                                
                                                <h4 class="product-modal-name">{{ $product->name }}</h4>

                                                <div class="product-rating mb-3">
                                                    <div class="stars">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            @if ($i <= floor($product->average_rating))
                                                                <i class="bi bi-star-fill"></i>
                                                            @elseif ($i - 0.5 <= $product->average_rating)
                                                                <i class="bi bi-star-half"></i>
                                                            @else
                                                                <i class="bi bi-star"></i>
                                                            @endif
                                                        @endfor
                                                        <span class="rating-value ms-2">{{ number_format($product->average_rating, 1) }}</span>
                                                        @if($product->review_count > 0)
                                                            <span class="review-count ms-2">({{ $product->review_count }} {{ Str::plural('review', $product->review_count) }})</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="product-price-container mb-3">
                                                    <h4 class="product-modal-price">₱{{ number_format($product->price, 2) }}</h4>
                                                </div>

                                                <div class="product-description mb-4">
                                                    {{ $product->description }}
                                                </div>

                                                <div class="product-modal-divider"></div>

                                                <!-- Variations with Better UI -->
                                                <div class="form-group mb-4">
                                                    <label for="variations" class="variation-label">Choose Variation:</label>
                                                    <div class="variation-options" id="variations{{ $product->id }}">
                                                        @foreach($product->variations as $variation)
                                                            <label class="variation-option">
                                                                <input type="radio" name="variation_id_{{ $product->id }}" 
                                                                       value="{{ $variation->id }}" autocomplete="off" required> 
                                                                <span class="variation-name">{{ ucfirst($variation->name) }}</span>
                                                                <span class="variation-price">₱{{ number_format($variation->price, 2) }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <!-- Quantity Selector with Modern Design -->
                                                <div class="form-group mb-4">
                                                    <label for="quantity" class="variation-label">Quantity:</label>
                                                    <div class="quantity-selector">
                                                        <button class="quantity-btn decreaseQuantity" type="button">
                                                            <i class="bi bi-dash-lg"></i>
                                                        </button>
                                                        <input type="number" class="form-control quantity" value="1" min="1">
                                                        <button class="quantity-btn increaseQuantity" type="button">
                                                            <i class="bi bi-plus-lg"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Action Buttons with Improved Layout -->
                                                <div class="modal-action-buttons">
                                                    <form action="{{ route('cart.add', $product->id) }}" method="POST" class="w-100 mb-2" onsubmit="return validateVariation({{ $product->id }})">
                                                        @csrf
                                                        <input type="hidden" name="variation_id" class="selectedVariation" data-product-id="{{ $product->id }}">
                                                        <input type="hidden" name="quantity" class="selectedQuantity" data-product-id="{{ $product->id }}" value="1">
                                                        <button type="submit" class="btn-modal-action btn-add-cart">
                                                            <i class="bi bi-cart-plus me-2"></i> Add to Cart
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('buy-now') }}" method="POST" class="w-100 mb-2" onsubmit="return validateVariation({{ $product->id }})">
                                                        @csrf
                                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                        <input type="hidden" name="variation_id" class="selectedVariation" data-product-id="{{ $product->id }}">
                                                        <input type="hidden" name="quantity" class="selectedQuantity" data-product-id="{{ $product->id }}" value="1">
                                                        <button type="submit" class="btn-modal-action btn-buy-now">
                                                            <i class="bi bi-credit-card me-2"></i> Buy Now
                                                        </button>
                                                    </form>

                                                    <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">
                                                        <i class="bi bi-arrow-left-circle me-2"></i> Continue Shopping
                                                    </button>
                                                </div>
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

    <!-- JavaScript -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Handle variation selection with visual feedback
        document.querySelectorAll("[id^='variations']").forEach(variationGroup => {
            const radioButtons = variationGroup.querySelectorAll('input[type="radio"]');
            
            radioButtons.forEach(radio => {
                radio.addEventListener("change", function(event) {
                    let productId = variationGroup.id.replace("variations", "");
                    let selectedVariation = event.target.value;

                    // Update hidden inputs
                    document.querySelectorAll(".selectedVariation[data-product-id='" + productId + "']").forEach(input => {
                        input.value = selectedVariation;
                    });
                    
                    // Add visual selected class
                    radioButtons.forEach(btn => {
                        if (btn.parentElement) {
                            if (btn.checked) {
                                btn.parentElement.classList.add('selected');
                            } else {
                                btn.parentElement.classList.remove('selected');
                            }
                        }
                    });
                });
            });
        });

        // Handle quantity buttons with improved interaction
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', function () {
                const decreaseButton = modal.querySelector('.decreaseQuantity');
                const increaseButton = modal.querySelector('.increaseQuantity');
                const quantityInput = modal.querySelector('.quantity');
                const productId = modal.id.replace("quickViewModal", "");

                // Select first variation by default
                const firstVariation = modal.querySelector('input[type="radio"]');
                if (firstVariation) {
                    firstVariation.checked = true;
                    firstVariation.dispatchEvent(new Event('change'));
                }

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
                        
                        // Add animation class for feedback
                        decreaseButton.classList.add('btn-animate');
                        setTimeout(() => decreaseButton.classList.remove('btn-animate'), 200);
                    }
                };

                increaseButton.onclick = () => {
                    let currentQuantity = parseInt(quantityInput.value);
                    quantityInput.value = currentQuantity + 1;
                    updateHiddenQuantity();
                    
                    // Add animation class for feedback
                    increaseButton.classList.add('btn-animate');
                    setTimeout(() => increaseButton.classList.remove('btn-animate'), 200);
                };

                quantityInput.oninput = updateHiddenQuantity;
                
                // Add animation to buttons for better feedback
                const actionButtons = modal.querySelectorAll('.btn-modal-action, .btn-modal-secondary');
                actionButtons.forEach(button => {
                    button.addEventListener('mousedown', function() {
                        this.classList.add('btn-pressed');
                    });
                    button.addEventListener('mouseup', function() {
                        this.classList.remove('btn-pressed');
                    });
                    button.addEventListener('mouseleave', function() {
                        this.classList.remove('btn-pressed');
                    });
                });
            });
        });
    });

    function validateVariation(productId) {
        let selected = document.querySelector(".selectedVariation[data-product-id='" + productId + "']");
        if (!selected || !selected.value) {
            // Enhanced error notification
            const modalBody = document.querySelector(`#quickViewModal${productId} .modal-body`);
            const errorAlert = document.createElement('div');
            errorAlert.className = 'variation-error-alert';
            errorAlert.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Please select a variation before proceeding.';
            
            // Remove any existing alerts
            const existingAlert = modalBody.querySelector('.variation-error-alert');
            if (existingAlert) existingAlert.remove();
            
            // Add the new alert with animation
            modalBody.prepend(errorAlert);
            
            // Add animation and auto-remove after delay
            setTimeout(() => {
                errorAlert.classList.add('show');
                setTimeout(() => {
                    errorAlert.classList.remove('show');
                    setTimeout(() => errorAlert.remove(), 300);
                }, 3000);
            }, 10);
            
            return false;
        }
        return true;
    }

    function openQuickView(productId) {
        var modal = new bootstrap.Modal(document.getElementById("quickViewModal" + productId));
        modal.show();
    }
    </script>
    
    <style>
    /* Enhanced Modal Styling */
    .product-modal {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .modal-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        background-color: #f8f9fa;
        padding: 15px 20px;
    }
    
    .product-modal-title {
        font-weight: 600;
        color: #5D6E54;
    }
    
    .product-modal-image-container {
        position: relative;
        height: 100%;
        background-color: #f9f9f9;
        overflow: hidden;
    }
    
    .product-modal-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    
    .product-image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.05);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .product-modal-image-container:hover .product-image-overlay {
        opacity: 1;
    }
    
    .product-modal-image-container:hover .product-modal-image {
        transform: scale(1.05);
    }
    
    .zoom-icon {
        background-color: #5D6E54;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transform: scale(0.8);
        transition: transform 0.3s ease;
    }
    
    .zoom-icon:hover {
        transform: scale(1);
    }
    
    .product-modal-details {
        max-height: 500px;
        overflow-y: auto;
    }
    
    .product-modal-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }
    
    .product-badges {
        display: flex;
        gap: 8px;
    }
    
    .badge {
        padding: 5px 10px;
        font-weight: 500;
        border-radius: 4px;
    }
    
    .stars {
        color: #f8ce0b;
        display: flex;
        align-items: center;
    }
    
    .stars i {
        margin-right: 2px;
    }
    
    .rating-value {
        color: #666;
        font-weight: 600;
    }
    
    .product-modal-price {
        color: #5D6E54;
        font-weight: 700;
        font-size: 24px;
    }
    
    .product-description {
        color: #666;
        line-height: 1.6;
    }
    
    .product-modal-divider {
        height: 1px;
        background-color: rgba(0, 0, 0, 0.1);
        margin: 15px 0;
    }
    
    /* Variation Options Styling */
    .variation-label {
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
        display: block;
    }
    
    .variation-options {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .variation-option {
        position: relative;
        display: flex;
        flex-direction: column;
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: white;
        min-width: 100px;
        text-align: center;
    }
    
    .variation-option input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }
    
    .variation-option:hover {
        border-color: #5D6E54;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .variation-option.selected {
        border-color: #5D6E54;
        background-color: rgba(93, 110, 84, 0.05);
        box-shadow: 0 4px 12px rgba(93, 110, 84, 0.2);
    }
    
    .variation-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .variation-price {
        color: #5D6E54;
        font-weight: 500;
    }
    
    /* Quantity Selector Styling - Improved Visibility */
    .quantity-selector {
        display: flex;
        align-items: center;
        max-width: 180px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .quantity-btn {
        width: 50px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 18px;
    }
    
    .decreaseQuantity {
        background-color: #ff6b6b;
    }
    
    .decreaseQuantity:hover {
        background-color: #ff5252;
    }
    
    .increaseQuantity {
        background-color: #4dabf7;
    }
    
    .increaseQuantity:hover {
        background-color: #339af0;
    }
    
    .quantity-btn:hover {
        transform: scale(1.05);
    }
    
    .quantity-btn:active {
        transform: scale(0.95);
    }
    
    .quantity {
        width: 80px;
        height: 45px;
        border: none;
        text-align: center;
        font-weight: 600;
        font-size: 18px;
        background-color: white;
        color: #333;
    }
    
    /* Add focus styling to quantity input */
    .quantity:focus {
        outline: none;
        background-color: #f5f5f5;
    }
    
    /* Additional quantity button styles for better usability */
    .quantity-btn i {
        font-weight: bold;
    }
    
    /* Action Buttons Styling */
    .modal-action-buttons {
        margin-top: 25px;
    }
    
    .btn-modal-action {
        width: 100%;
        padding: 12px 15px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-add-cart {
        background-color: #5D6E54;
        color: white;
    }
    
    .btn-add-cart:hover {
        background-color: #495C42;
    }
    
    .btn-buy-now {
        background-color: #FFAA8A;
        color: white;
    }
    
    .btn-buy-now:hover {
        background-color: #FF8C66;
    }
    
    .btn-modal-secondary {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background-color: transparent;
        color: #666;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-modal-secondary:hover {
        background-color: #f5f5f5;
        border-color: #5D6E54;
        color: #5D6E54;
    }
    
    .btn-pressed {
        transform: scale(0.98);
    }
    
    /* Error message styling */
    .variation-error-alert {
        background-color: #ffeaea;
        color: #dc3545;
        padding: 10px 15px;
        border-radius: 8px;
        margin: 0 15px 15px;
        display: flex;
        align-items: center;
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    
    .variation-error-alert.show {
        opacity: 1;
        transform: translateY(0);
    }
    
    .variation-error-alert i {
        margin-right: 10px;
    }
    
    /* Scrollbar styling for the details section */
    .product-modal-details::-webkit-scrollbar {
        width: 5px;
    }
    
    .product-modal-details::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .product-modal-details::-webkit-scrollbar-thumb {
        background: #b8c0b5;
        border-radius: 10px;
    }
    
    .product-modal-details::-webkit-scrollbar-thumb:hover {
        background: #5D6E54;
    }
    </style>
@endsection
