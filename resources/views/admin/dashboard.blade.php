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
            <tr data-product-id="{{ $product->id }}" class="product-row" onclick="toggleVariations({{ $product->id }})">
                <td><input type="checkbox" class="product-checkbox" value="{{ $product->id }}"></td>
                <td> 
                    <img src="{{ asset('images/' . $product->image . '.jpg') }}" 
                         onerror="this.onerror=null; this.src='{{ asset('images/' . $product->image . '.png') }}';"
                         width="50" height="50" style="object-fit: cover;" 
                         alt="{{ $product->name }}">
                </td>
                <td><strong>{{ $product->name }}</strong></td>
                <td>{{ $product->description }}</td>
                <td>₱{{ number_format($product->price, 2) }}</td>

                <!-- Stock Adjustment -->
                <td>
                    <button class="btn btn-sm btn-secondary stock-btn" data-id="{{ $product->id }}" data-type="product" data-action="decrease">-</button>
                    <span id="stock-{{ $product->id }}">{{ $product->stock }}</span>
                    <button class="btn btn-sm btn-secondary stock-btn" data-id="{{ $product->id }}" data-type="product" data-action="increase">+</button>
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

            <!-- Display Variations (Initially Hidden) -->
            <tr id="variations-{{ $product->id }}" class="variation-row" style="display: none;">
                <td colspan="8">
                    <table class="table table-light">
                        @foreach ($product->variations as $variation)
                        <tr>
                            <td></td> <!-- Empty column for checkbox -->
                            <td colspan="2"><i>— {{ $variation->name }}</i></td>
                            <td>-</td> <!-- No description for variations -->
                            <td>₱{{ number_format($variation->price, 2) }}</td>

                            <!-- Stock Adjustment -->
                            <td>
                                <button class="btn btn-sm btn-secondary stock-btn" data-id="{{ $variation->id }}" data-type="variation" data-action="decrease">-</button>
                                <span id="stock-{{ $variation->id }}">{{ $variation->stock }}</span>
                                <button class="btn btn-sm btn-secondary stock-btn" data-id="{{ $variation->id }}" data-type="variation" data-action="increase">+</button>
                            </td>

                            <td>
                                <span class="availability" id="availability-{{ $variation->id }}">
                                    {{ $variation->stock > 0 ? '✅ Available' : '❌ Not Available' }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('seller.edit_variation', $variation->id) }}" class="btn btn-info btn-sm">Edit</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $variation->id }}, true)">Delete</button>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
function adjustStock(id, type, action) {
    let stockElement = document.getElementById('stock-' + id);
    if (!stockElement) return;
    
    let currentStock = parseInt(stockElement.innerText);
    let newStock = action === 'increase' ? currentStock + 1 : Math.max(0, currentStock - 1);
    stockElement.innerText = newStock;
    
    console.log(`Adjusting stock for ${type} ID: ${id}, Action: ${action}, New Stock: ${newStock}`);
    
    let url = type === 'variation' 
        ? `/seller/variation/${id}/adjust-stock` 
        : `/seller/product/${id}/adjust-stock`;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ stock: newStock })
    }).then(response => response.json()).then(data => {
        if (data.success) {
            document.getElementById('availability-' + id).innerText = 
                newStock > 0 ? '✅ Available' : '❌ Not Available';
        } else {
            console.error("Stock update failed:", data.error);
        }
    }).catch(error => console.error("Stock update failed:", error));
}

document.addEventListener("click", function(event) {
    if (event.target.classList.contains("stock-btn")) {
        adjustStock(event.target.dataset.id, event.target.dataset.type, event.target.dataset.action);
    }
});
</script>

@endsection
