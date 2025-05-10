@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="text-center fw-bold mb-4"><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>  <!-- Display error message from backend -->
    @endif

    @if(count($cartItems) > 0)
    <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="form-check">
        <input type="checkbox" id="selectAll" class="form-check-input me-2">
        <label for="selectAll" class="form-check-label">Select All</label>
    </div>
    <div>
    <button id="deleteSelectedBtn" type="button" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">🗑 Delete Selected</button>
    </div>

    </div>


    <form action="{{ route('checkout') }}" method="GET" id="checkoutForm">
        @csrf
        <input type="hidden" name="selected_items" id="selected_items_input">
        
        <div class="cart-items shadow-xs">
        @foreach($cartItems as $item)
            <div class="cart-item d-flex align-items-center p-3 mb-3 rounded shadow-sm bg-white">
                <input type="checkbox" class="product-checkbox me-3" name="selected_items[]" value="{{ 	$item->id }}" data-price="{{ $item->price * $item->quantity }}">
                
                @php
                $imagePath = asset("images/default.png");
                $filename = $item->product->image ?? null;

                if ($filename) {
                    $extensions = ['jpg', 'jpeg', 'png', 'webp'];
                    foreach ($extensions as $ext) {
                        if (file_exists(public_path("images/{$filename}.{$ext}"))) {
                            $imagePath = asset("images/{$filename}.{$ext}");
                            break;
                        }
                    }
                }
                @endphp

                
                <img src="{{ $imagePath }}" alt="{{ $item['name'] ?? 'Product' }}" 
                     class="img-fluid rounded" style="width: 100px; height: 100px; object-fit: cover;">

                <div class="ms-3 flex-grow-1">
                    <h5 class="fw-semibold mb-1">{{ $item->product->name ?? 'Unnamed Product' }}</h5>
                    
                    @if(!empty($item->productVariation->name))
                        <p class="text-muted mb-0">Variation: <strong>{{ $item->productVariation->name }}</strong></p>
                    @endif
                    
                    <p class="fw-normal text-danger opacity-75 product-price mb-0">
                        ₱{{ number_format(	$item->price ?? 0, 2) }}
                    </p>
                </div>
                
                <div class="quantity-control d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-danger decrease-qty px-2 py-1" data-id="{{ 	$item->id }}">-</button>
                    <span class="qty-text mx-2 fw-semibold" id="qty-{{ 	$item->id }}">{{ $item->quantity ?? 1 }}</span>
                    <button type="button" class="btn btn-sm btn-outline-success increase-qty px-2 py-1" data-id="{{ $item->id }}">+</button>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <button type="submit" class="btn btn-success px-3 py-1 rounded hover:bg-green-600 transition block text-center" id="checkoutBtn">Checkout</button>
            <div class="fw-bold">Total: ₱<span id="total-price">0.00</span></div>
        </div>
    </form>

    @else
        <p class="text-center text-muted">Your cart is empty.</p>
    @endif
</div>

<script>
 document.addEventListener("DOMContentLoaded", function () {
    // Select All functionality
    const selectAllCheckbox = document.getElementById("selectAll");
    selectAllCheckbox.addEventListener("change", function () {
        const allProductCheckboxes = document.querySelectorAll(".product-checkbox");
        allProductCheckboxes.forEach(cb => cb.checked = this.checked);
        updateTotal();
    });

    // Quantity update and total calculation
    document.querySelectorAll(".increase-qty, .decrease-qty").forEach(button => {
        button.addEventListener("click", function () {
            let productId = this.dataset.id;
            let isIncrease = this.classList.contains("increase-qty");
            let qtyElement = document.getElementById(`qty-${productId}`);
            let checkbox = document.querySelector(`input[value='${productId}']`);
            
            let currentQty = parseInt(qtyElement.innerText);
            let newQty = isIncrease ? currentQty + 1 : Math.max(1, currentQty - 1);

            fetch(`/cart/update/${productId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                },
                body: JSON.stringify({ change: isIncrease ? 1 : -1 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.quantity !== undefined) {
                    qtyElement.innerText = data.quantity;

                    // Update the total price for the item immediately after quantity change
                    updateTotal(); // This will now work correctly
                }
            })
            .catch(error => console.error("Error updating cart:", error));
        });
    });

    // Delete selected items
    document.getElementById("deleteSelectedBtn").addEventListener("click", function () {
        let selectedItems = document.querySelectorAll(".product-checkbox:checked");
        if (selectedItems.length === 0) {
            alert("Please select at least one product to delete.");
            return;
        }

        let selectedIds = Array.from(selectedItems).map(checkbox => checkbox.value);

        fetch("{{ route('cart.removeMultiple') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({ selected_products: selectedIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectedItems.forEach(checkbox => checkbox.closest(".cart-item").remove());
                updateTotal();

                const remainingItems = document.querySelectorAll(".cart-item");
                if (remainingItems.length === 0) {
                    // Hide related controls
                    document.getElementById("checkoutBtn").style.display = "none";
                    document.getElementById("selectAll").closest(".form-check").style.display = "none";
                    document.getElementById("deleteSelectedBtn").closest("div").style.display = "none";

                    // Hide total section
                    document.querySelector("#total-price").closest("div").style.display = "none";

                    // Show empty cart message
                    const cartContainer = document.querySelector(".cart-items");
                    cartContainer.innerHTML = `<p class="text-center text-muted">Your cart is empty.</p>`;
                }
            }
        })
        .catch(error => console.error("Error deleting cart items:", error));
    });

    // Update total function
    function updateTotal() {
        let total = 0;
        let selectedItems = document.querySelectorAll(".product-checkbox:checked");
        
        selectedItems.forEach(checkbox => {
            // Find the quantity and price from the corresponding cart item
            let quantity = parseInt(document.getElementById(`qty-${checkbox.value}`).innerText);
            let price = parseFloat(checkbox.dataset.price);
            
            // Total price for the selected item
            total += (price * quantity);
        });

        // Update the total price on the page
        document.getElementById("total-price").innerText = total.toFixed(2);

        // Enable or disable the checkout button based on selection
        const checkoutBtn = document.getElementById("checkoutBtn");
        checkoutBtn.disabled = selectedItems.length === 0;

        // Update hidden input with selected item IDs
        let selectedIds = Array.from(selectedItems).map(checkbox => checkbox.value);
        document.getElementById("selected_items_input").value = selectedIds.join(',');
    }

    // Update total and checkout button state on checkbox change
    document.querySelectorAll(".product-checkbox").forEach(checkbox => {
        checkbox.addEventListener("change", updateTotal);
    });

    // Initial total calculation when page loads
    updateTotal();
});


</script>
@endsection
