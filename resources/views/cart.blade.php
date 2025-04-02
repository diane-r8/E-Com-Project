@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="text-center fw-bold mb-4"><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(count($cartItems) > 0)
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <button id="editCartBtn" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">🛠 Edit</button>
            <button id="deleteSelectedBtn" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">🗑 Delete Selected</button>
        </div>
      
    </div>

    <form action="{{ route('checkout') }}" method="POST">
        @csrf
        <div class="cart-items shadow-xs">
            @foreach($cartItems as $id => $item)
            <div class="cart-item d-flex align-items-center p-3 mb-3 rounded shadow-sm bg-white">
                <input type="checkbox" class="product-checkbox me-3" name="selected_products[]" value="{{ $id }}" data-price="{{ $item['price'] * $item['quantity'] }}">
                
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
                
                <img src="{{ $imagePath }}" alt="{{ $item['name'] ?? 'Product' }}" 
                     class="img-fluid rounded" style="width: 100px; height: 100px; object-fit: cover;">

                <div class="ms-3 flex-grow-1">
                    <h5 class="fw-semibold mb-1">{{ $item['product_name'] ?? 'Unnamed Product' }}</h5>
                    
                    @if(!empty($item['variation_name']))
                        <p class="text-muted mb-0">Variation: <strong>{{ $item['variation_name'] }}</strong></p>
                    @endif
                    
                    <p class="fw-normal text-danger opacity-75 product-price mb-0">
                        ₱{{ number_format($item['price'] ?? 0, 2) }}
                    </p>
                </div>
                
                <div class="quantity-control d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-danger decrease-qty px-2 py-1" data-id="{{ $id }}">-</button>
                    <span class="qty-text mx-2 fw-semibold" id="qty-{{ $id }}">{{ $item['quantity'] ?? 1 }}</span>
                    <button type="button" class="btn btn-sm btn-outline-success increase-qty px-2 py-1" data-id="{{ $id }}">+</button>
                </div>
            </div>
            @endforeach
        </div>
        

        <div class="d-flex justify-content-between align-items-center mb-4">
      
        <button type="submit" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 transition" id="checkoutBtn">Checkout</button>
           
             <div class="fw-bold">Total: ₱<span id="total-price">0.00</span></div>
        </div>
      
    </div>
      

    </form>
    @else
        <p class="text-center text-muted">Your cart is empty.</p>
    @endif
</div>

<script>
   document.addEventListener("DOMContentLoaded", function () {
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
                    checkbox.dataset.price = data.quantity * parseFloat(checkbox.dataset.price) / currentQty;
                    updateTotal();
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
            body: JSON.stringify({ product_ids: selectedIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectedItems.forEach(checkbox => checkbox.closest(".cart-item").remove());
                updateTotal();
            }
        })
        .catch(error => console.error("Error deleting cart items:", error));
    });

    function updateTotal() {
        let total = 0;
        document.querySelectorAll(".product-checkbox:checked").forEach(checkbox => {
            total += parseFloat(checkbox.dataset.price);
        });
        document.getElementById("total-price").innerText = total.toFixed(2);
    }

    document.querySelectorAll(".product-checkbox").forEach(checkbox => {
        checkbox.addEventListener("change", updateTotal);
    });
});

</script>
@endsection

