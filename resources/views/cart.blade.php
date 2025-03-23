@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Your Shopping Cart</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(count($cartItems) > 0)
    <div class="cart-header d-flex justify-content-between">
        <button id="editCartBtn" class="btn btn-warning">Edit</button>
    </div>

    <form action="{{ route('checkout') }}" method="POST">
        @csrf
        <table class="table">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cartItems as $id => $item)
                <tr>
                    <td>
                        <input type="checkbox" class="product-checkbox" name="selected_products[]" value="{{ $id }}">
                    </td>
                    <td>
                        <img src="{{ asset('storage/' . $item['image']) }}" width="50" height="50" alt="{{ $item['name'] }}">
                        {{ $item['name'] }}
                    </td>
                    <td>₱{{ number_format($item['price'], 2) }}</td>
                    <td>
                        <div class="quantity-control">
                            <button type="button" class="btn btn-sm btn-danger decrease-qty" data-id="{{ $id }}">-</button>
                            <span class="qty-text" id="qty-{{ $id }}">{{ $item['quantity'] }}</span>
                            <button type="button" class="btn btn-sm btn-success increase-qty" data-id="{{ $id }}">+</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Select All, Total Price, and Checkout Button in the same row -->
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <input type="checkbox" id="selectAllBottom"> 
                <label for="selectAllBottom">All</label>
            </div>
            <div class="d-flex gap-3">
                <h4>Total: <span id="totalPrice">₱0.00</span></h4>
                <button type="submit" class="btn btn-primary" id="checkoutBtn">Checkout</button>
            </div>
        </div>

    </form>

    <form action="{{ route('cart.removeMultiple') }}" method="POST" id="removeCartForm" class="d-none">
        @csrf
        <button type="submit" class="btn btn-danger" id="removeSelectedBtn">Remove Selected</button>
    </form>

    @else
        <p>Your cart is empty.</p>
    @endif
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const editBtn = document.getElementById('editCartBtn');
    const removeForm = document.getElementById('removeCartForm');
    const removeBtn = document.getElementById('removeSelectedBtn');
    const checkoutBtn = document.getElementById("checkoutBtn");
    const totalPrice = document.getElementById("totalPrice");
    const selectAllCheckbox = document.getElementById("selectAllBottom");
    const productCheckboxes = document.querySelectorAll(".product-checkbox");

    let isEditMode = false;

    // Toggle edit mode
    editBtn.addEventListener('click', function() {
        isEditMode = !isEditMode;
        removeForm.classList.toggle('d-none', !isEditMode);
        checkoutBtn.classList.toggle('d-none', isEditMode);
        totalPrice.parentElement.classList.toggle('d-none', isEditMode);
        selectAllCheckbox.parentElement.classList.toggle('d-none', !isEditMode);
    });

    // Select All Checkbox functionality
    selectAllCheckbox.addEventListener("change", function() {
        productCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateTotalPrice();
        updateCheckoutButton();
    });

    productCheckboxes.forEach(checkbox => {
        checkbox.addEventListener("change", function() {
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            } else if (document.querySelectorAll(".product-checkbox:checked").length === productCheckboxes.length) {
                selectAllCheckbox.checked = true;
            }
            updateTotalPrice();
            updateCheckoutButton();
        });
    });

    // Quantity buttons
    document.querySelectorAll(".increase-qty").forEach(btn => {
        btn.addEventListener("click", function() {
            updateQuantity(this.dataset.id, 1);
        });
    });

    document.querySelectorAll(".decrease-qty").forEach(btn => {
        btn.addEventListener("click", function() {
            updateQuantity(this.dataset.id, -1);
        });
    });

    function updateQuantity(id, change) {
        fetch(`/cart/update/${id}`, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ change: change })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById(`qty-${id}`).textContent = data.quantity;
            updateTotalPrice();
        });
    }

    // Update total price when selecting products
    function updateTotalPrice() {
        let total = 0;
        document.querySelectorAll(".product-checkbox:checked").forEach(checkbox => {
            const id = checkbox.value;
            const quantity = parseInt(document.getElementById(`qty-${id}`).textContent);
            const price = parseFloat(checkbox.closest("tr").querySelector("td:nth-child(3)").textContent.replace('₱', ''));
            total += price * quantity;
        });
        totalPrice.textContent = `₱${total.toFixed(2)}`;
    }

    // Handle product removal
    removeBtn.addEventListener("click", function(event) {
        event.preventDefault();
        const selectedProducts = Array.from(document.querySelectorAll(".product-checkbox:checked"))
            .map(cb => cb.value);

        fetch("{{ route('cart.removeMultiple') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ selected_products: selectedProducts })
        })
        .then(() => location.reload());
    });

    // Update checkout button to show the number of selected products
    function updateCheckoutButton() {
        let selectedCount = document.querySelectorAll(".product-checkbox:checked").length;
        checkoutBtn.textContent = selectedCount > 0 ? `Checkout (${selectedCount})` : "Checkout";
    }

    document.querySelectorAll(".product-checkbox").forEach(checkbox => {
        checkbox.addEventListener("change", updateCheckoutButton);
    });

    // Initialize checkout button count on page load
    updateCheckoutButton();
});
</script>

@endsection
