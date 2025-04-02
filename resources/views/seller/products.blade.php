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
            <tr class="product-row" data-product-id="{{ $product->id }}" onclick="toggleVariations({{ $product->id }})">
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
                    <button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); adjustStock({{ $product->id }}, -1)">-</button>
                    <span id="stock-{{ $product->id }}">{{ $product->stock }}</span>
                    <button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); adjustStock({{ $product->id }}, 1)">+</button>
                </td>

                <!-- Availability Status -->
                <td>
                    <span class="availability" id="availability-{{ $product->id }}">
                        {{ $product->stock > 0 ? '✅ Available' : '❌ Not Available' }}
                    </span>
                </td>

                <td>
                    <a href="{{ route('seller.edit_product', $product->id) }}" class="btn btn-warning btn-sm" onclick="event.stopPropagation();">Edit</a>
                    <button type="button" class="btn btn-danger btn-sm" onclick="event.stopPropagation(); confirmDelete({{ $product->id }})">Delete</button>
                </td>
            </tr>

            <!-- Display Variations (Initially Hidden) -->
            <tr id="variations-{{ $product->id }}" class="variation-row" style="display: none;">
                <td colspan="8">
                    <table class="table table-sm">
                        <tbody>
                            @foreach ($product->variations as $variation)
                            <tr>
                                <td></td>
                                <td colspan="2"><i>— {{ $variation->name }}</i></td>
                                <td>-</td>
                                <td>₱{{ number_format($variation->price, 2) }}</td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); adjustStock({{ $variation->id }}, -1, true)">-</button>
                                    <span id="stock-{{ $variation->id }}">{{ $variation->stock }}</span>
                                    <button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); adjustStock({{ $variation->id }}, 1, true)">+</button>
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
                        </tbody>
                    </table>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
function toggleVariations(productId) {
    let variationsRow = document.getElementById('variations-' + productId);
    if (variationsRow.style.display === 'none') {
        variationsRow.style.display = 'table-row';
    } else {
        variationsRow.style.display = 'none';
    }
}
</script>

@endsection
