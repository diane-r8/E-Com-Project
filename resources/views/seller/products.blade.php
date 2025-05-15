@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>My Products</h1>
        <a href="{{ route('seller.create_product') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Product
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <button class="btn btn-danger" onclick="deleteSelected()" id="bulkDeleteBtn" disabled>
                        <i class="bi bi-trash"></i> Delete Selected
                    </button>
                    <span id="selectedCount" class="ms-2 text-muted d-none">(0 selected)</span>
                </div>
                <div class="input-group" style="max-width: 300px;">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search products...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productTable">
                        @foreach ($products as $product)
                        <tr class="product-row" data-product-id="{{ $product->id }}">
                            <td onclick="event.stopPropagation();">
                                <input type="checkbox" class="product-checkbox" value="{{ $product->id }}">
                            </td>
                            <td onclick="toggleVariations({{ $product->id }}, event)"> 
                                <img src="{{ asset('images/' . $product->image . '.jpg') }}" 
                                    onerror="this.onerror=null; this.src='{{ asset('images/' . $product->image . '.png') }}';"
                                    width="50" height="50" class="rounded" style="object-fit: cover;" 
                                    alt="{{ $product->name }}">
                            </td>
                            <td onclick="toggleVariations({{ $product->id }}, event)">
                                <strong>{{ $product->name }}</strong>
                                @if(count($product->variations) > 0)
                                <span class="badge bg-info text-white ms-2">{{ count($product->variations) }} variants</span>
                                @endif
                            </td>
                            <td onclick="toggleVariations({{ $product->id }}, event)">
                                {{ Str::limit($product->description, 50) }}
                            </td>
                            <td onclick="toggleVariations({{ $product->id }}, event)">₱{{ number_format($product->price, 2) }}</td>

                            <!-- Stock Adjustment -->
                            <td onclick="event.stopPropagation();">
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustStock({{ $product->id }}, -1)">-</button>
                                    <input type="number" class="form-control text-center" id="stock-input-{{ $product->id }}" value="{{ $product->stock }}" min="0" onchange="updateStockFromInput({{ $product->id }})">
                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustStock({{ $product->id }}, 1)">+</button>
                                </div>
                                <div class="d-none" id="stock-loading-{{ $product->id }}">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Availability Status -->
                            <td onclick="toggleVariations({{ $product->id }}, event)">
                                <span class="badge {{ $product->stock > 0 ? 'bg-success' : 'bg-danger' }}" id="availability-{{ $product->id }}">
                                    {{ $product->stock > 0 ? 'Available' : 'Out of Stock' }}
                                </span>
                            </td>

                            <td onclick="event.stopPropagation();">
                                <div class="btn-group">
                                    <a href="{{ route('seller.edit_product', $product->id) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $product->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Display Variations (Initially Hidden) -->
                        <tr id="variations-{{ $product->id }}" class="variation-row" style="display: none;">
                            <td colspan="8" class="p-0">
                                <div class="bg-light p-3">
                                    <h6 class="mb-3">Variations for {{ $product->name }}</h6>
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th></th>
                                                <th>Name</th>
                                                <th>Price</th>
                                                <th>Stock</th>
                                                <th>Status</th>
                                              
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($product->variations as $variation)
                                            <tr>
                                                <td width="5%"></td>
                                                <td width="25%"><i class="bi bi-tag-fill me-2 text-secondary"></i>{{ $variation->name }}</td>
                                                <td width="15%">₱{{ number_format($variation->price, 2) }}</td>
                                                <td width="20%">
                                                    <div class="input-group input-group-sm" style="width: 120px;">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="adjustVariationStock({{ $variation->id }}, -1)">-</button>
                                                        <input type="number" class="form-control text-center" id="variation-stock-input-{{ $variation->id }}" value="{{ $variation->stock }}" min="0" onchange="updateVariationStockFromInput({{ $variation->id }})">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="adjustVariationStock({{ $variation->id }}, 1)">+</button>
                                                    </div>
                                                    <div class="d-none" id="variation-stock-loading-{{ $variation->id }}">
                                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td width="15%">
                                                    <span class="badge {{ $variation->stock > 0 ? 'bg-success' : 'bg-danger' }}" id="variation-availability-{{ $variation->id }}">
                                                        {{ $variation->stock > 0 ? 'Available' : 'Out of Stock' }}
                                                    </span>
                                                </td>
                                             
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="modal-title">Processing...</h5>
                <p class="text-muted">Please wait while we update your product.</p>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Toggle variation rows visibility
 */
function toggleVariations(productId, event) {
    if (event) {
        event.preventDefault();
    }
    
    let variationsRow = document.getElementById('variations-' + productId);
    let productRow = document.querySelector(`tr.product-row[data-product-id="${productId}"]`);
    
    if (variationsRow.style.display === 'none') {
        variationsRow.style.display = 'table-row';
        productRow.classList.add('table-active');
    } else {
        variationsRow.style.display = 'none';
        productRow.classList.remove('table-active');
    }
}

/**
 * Update product stock from input field
 */
function updateStockFromInput(productId) {
    const inputElement = document.getElementById('stock-input-' + productId);
    const newStock = parseInt(inputElement.value) || 0;
    
    // Ensure stock isn't negative
    if (newStock < 0) {
        inputElement.value = 0;
        return;
    }
    
    updateProductStock(productId, newStock);
}

/**
 * Update variation stock from input field
 */
function updateVariationStockFromInput(variationId) {
    const inputElement = document.getElementById('variation-stock-input-' + variationId);
    const newStock = parseInt(inputElement.value) || 0;
    
    // Ensure stock isn't negative
    if (newStock < 0) {
        inputElement.value = 0;
        return;
    }
    
    updateVariationStock(variationId, newStock);
}

/**
 * Adjust product stock by increment/decrement
 */
function adjustStock(productId, change) {
    // Show loading indicator
    const inputElement = document.getElementById('stock-input-' + productId);
    const loadingElement = document.getElementById('stock-loading-' + productId);
    
    const currentStock = parseInt(inputElement.value) || 0;
    const newStock = Math.max(0, currentStock + change);
    
    // Update input visually first for better UX
    inputElement.value = newStock;
    
    // Update the stock in database
    updateProductStock(productId, newStock);
}

/**
 * Adjust variation stock by increment/decrement
 */
function adjustVariationStock(variationId, change) {
    // Show loading indicator
    const inputElement = document.getElementById('variation-stock-input-' + variationId);
    const loadingElement = document.getElementById('variation-stock-loading-' + variationId);
    
    const currentStock = parseInt(inputElement.value) || 0;
    const newStock = Math.max(0, currentStock + change);
    
    // Update input visually first for better UX
    inputElement.value = newStock;
    
    // Update the stock in database
    updateVariationStock(variationId, newStock);
}

/**
 * Update product stock via AJAX
 */
function updateProductStock(productId, newStock) {
    // Show loading indicator
    const loadingElement = document.getElementById('stock-loading-' + productId);
    loadingElement.classList.remove('d-none');
    
    // Use the route that actually works from web.php
    fetch(`/seller/product/${productId}/adjust-stock`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ stock: newStock })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update display
            document.getElementById('stock-input-' + productId).value = data.stock;
            
            // Update availability status
            const availabilityElement = document.getElementById('availability-' + productId);
            if (data.stock > 0) {
                availabilityElement.innerHTML = 'Available';
                availabilityElement.className = 'badge bg-success';
            } else {
                availabilityElement.innerHTML = 'Out of Stock';
                availabilityElement.className = 'badge bg-danger';
            }
            
            // Hide loading indicator
            loadingElement.classList.add('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update stock. Please try again.');
        loadingElement.classList.add('d-none');
    });
}

/**
 * Update variation stock via AJAX
 */
function updateVariationStock(variationId, newStock) {
    // Show loading indicator
    const loadingElement = document.getElementById('variation-stock-loading-' + variationId);
    loadingElement.classList.remove('d-none');
    
    // Use the route that actually works from web.php
    fetch(`/seller/variation/${variationId}/adjust-stock`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ stock: newStock })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update display
            document.getElementById('variation-stock-input-' + variationId).value = data.stock;
            
            // Update availability status
            const availabilityElement = document.getElementById('variation-availability-' + variationId);
            if (data.stock > 0) {
                availabilityElement.innerHTML = 'Available';
                availabilityElement.className = 'badge bg-success';
            } else {
                availabilityElement.innerHTML = 'Out of Stock';
                availabilityElement.className = 'badge bg-danger';
            }
            
            // Hide loading indicator
            loadingElement.classList.add('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update variation stock. Please try again.');
        loadingElement.classList.add('d-none');
    });
}

/**
 * Confirm product deletion
 */
function confirmDelete(productId) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        // Show loading modal
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();
        
        // Create form for deletion
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/seller/products/${productId}/delete`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        // Add method field
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Append form to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Confirm variation deletion
 */
function confirmVariationDelete(variationId) {
    if (confirm('Are you sure you want to delete this variation? This action cannot be undone.')) {
        // Show loading modal
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();
        
        // Create form for deletion
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/seller/variation/delete/${variationId}`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        // Add method field
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Append form to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Toggle select all checkboxes
 */
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    
    productCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateSelectedCount();
}

/**
 * Update selected count display
 */
function updateSelectedCount() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    const selectedCount = document.getElementById('selectedCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    if (selectedCheckboxes.length > 0) {
        selectedCount.textContent = `(${selectedCheckboxes.length} selected)`;
        selectedCount.classList.remove('d-none');
        bulkDeleteBtn.removeAttribute('disabled');
    } else {
        selectedCount.classList.add('d-none');
        bulkDeleteBtn.setAttribute('disabled', 'disabled');
    }
}

/**
 * Delete selected products
 */
function deleteSelected() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one product to delete.');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selectedCheckboxes.length} selected product(s)? This action cannot be undone.`)) {
        // Show loading modal
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();
        
        // Collect all selected product IDs
        const selectedIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
        
        // Create form for deletion
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/seller/products/delete-bulk';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        // Add method field
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Add selected IDs
        selectedIds.forEach(id => {
            const idField = document.createElement('input');
            idField.type = 'hidden';
            idField.name = 'product_ids[]';
            idField.value = id;
            form.appendChild(idField);
        });
        
        // Append form to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Setup event listeners when document is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to checkboxes
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('click', function(e) {
            e.stopPropagation();
            updateSelectedCount();
        });
    });
    
    // Add search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.product-row');
            
            rows.forEach(row => {
                const productName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const productDesc = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                
                if (productName.includes(searchTerm) || productDesc.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                    
                    // Hide variation row if product is hidden
                    const productId = row.getAttribute('data-product-id');
                    const variationRow = document.getElementById(`variations-${productId}`);
                    if (variationRow) {
                        variationRow.style.display = 'none';
                    }
                }
            });
        });
    }
});
</script>

<style>
.product-row {
    cursor: pointer;
}

.product-row:hover {
    background-color: rgba(0, 0, 0, 0.03);
}

.variation-row {
    background-color: #f8f9fa;
}

.input-group-sm .form-control:focus {
    box-shadow: none !important;
    border-color: #ced4da !important;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.8em;
}
</style>
@endsection