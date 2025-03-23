@extends('layouts.app')

@section('content')
<div class="container">
    <h1>My Products</h1>

    <a href="{{ route('seller.create_product') }}" class="btn btn-primary mb-3">Add Product</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Bulk Delete Button -->
    <button class="btn btn-danger mb-3" onclick="deleteSelected()">Delete Selected</button>

    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                <th>Image</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Availability</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="productTable">
            @foreach ($products as $product)
            <tr data-product-id="{{ $product->id }}">
                <td><input type="checkbox" class="product-checkbox" value="{{ $product->id }}"></td>
                <td> 
                    <img src="{{ asset('images/' . $product->image . '.jpg') }}" 
                         onerror="this.onerror=null; this.src='{{ asset('images/' . $product->image . '.png') }}';"
                         width="50" height="50" style="object-fit: cover;" 
                         alt="{{ $product->name }}">
                </td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->description }}</td>
                <td>₱{{ number_format($product->price, 2) }}</td>

                <!-- Stock Adjustment -->
                <td>
                    <button class="btn btn-sm btn-secondary" onclick="adjustStock({{ $product->id }}, -1)">-</button>
                    <span id="stock-{{ $product->id }}">{{ $product->stock }}</span>
                    <button class="btn btn-sm btn-secondary" onclick="adjustStock({{ $product->id }}, 1)">+</button>
                </td>

                <!-- Availability Status -->
                <td>
                    <span class="availability" id="availability-{{ $product->id }}">
                        {{ $product->stock > 0 ? '✅ Available' : '❌ Not Available' }}
                    </span>
                </td>

                <td>
                    <a href="{{ route('seller.edit_product', $product->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $product->id }})">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this product?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Stock, Availability, and Filters -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.head.insertAdjacentHTML("beforeend", `<meta name="csrf-token" content="{{ csrf_token() }}">`);
});

// Confirm delete for single product
function confirmDelete(productId) {
    let form = document.getElementById('deleteForm');
    form.action = "/seller/products/" + productId + "/delete";  // ✅ Corrected URL

    let deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Adjust stock via AJAX
function adjustStock(productId, amount) {
    let stockElement = document.getElementById('stock-' + productId);
    let newStock = parseInt(stockElement.innerText) + amount;
    if (newStock < 0) return; // Prevent negative stock

    stockElement.innerText = newStock;

    fetch(`/seller/product/${productId}/adjust-stock`, { 
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ stock: newStock })
    }).then(response => response.json()).then(data => {
        document.getElementById('availability-' + productId).innerText = 
            newStock > 0 ? '✅ Available' : '❌ Not Available';
    }).catch(error => console.error("Stock update failed:", error));
}

// Select all checkboxes
function toggleSelectAll() {
    let checkboxes = document.querySelectorAll('.product-checkbox');
    let selectAll = document.getElementById('selectAll').checked;
    checkboxes.forEach(checkbox => checkbox.checked = selectAll);
}

// Bulk delete products

function deleteSelected() {
    let selectedProducts = Array.from(document.querySelectorAll('.product-checkbox:checked'))
        .map(checkbox => checkbox.value);

    if (selectedProducts.length === 0) {
        alert("Please select at least one product to delete.");
        return;
    }

    if (!confirm("Are you sure you want to delete the selected products?")) return;

    fetch(`/seller/products/delete-multiple`, { 
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ product_ids: selectedProducts })
    }).then(response => response.json()).then(data => {
        if (data.success) {
            // ✅ Remove selected rows from the table
            selectedProducts.forEach(id => {
                let row = document.querySelector(`tr[data-product-id="${id}"]`);
                if (row) row.remove();
            });

            // ✅ Uncheck "Select All" checkbox
            document.getElementById('selectAll').checked = false;
        } else {
            alert("Failed to delete selected products. Please try again.");
        }
    }).catch(error => console.error("Bulk delete failed:", error));
}

</script>

@endsection                      