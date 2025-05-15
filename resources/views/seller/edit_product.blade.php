@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Back button and navigation breadcrumbs -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('seller.products') }}">Products</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
                <a href="{{ route('seller.products') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Products
                </a>
            </div>

            <!-- Error handling -->
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Please check the form for errors.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Success message -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Main Content -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h4 class="card-title mb-0">Edit Product</h4>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">General Information</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="variations-tab" data-bs-toggle="tab" data-bs-target="#variations" type="button" role="tab" aria-controls="variations" aria-selected="false">Variations</button>
                        </li>
                    </ul>

                    <div class="tab-content pt-4" id="productTabContent">
                        <!-- General Product Info Tab -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <form action="{{ route('seller.update_product', $product->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
                @csrf
                @method('PUT')

                                <div class="row">
                                    <div class="col-md-8">
                <div class="mb-3">
                                            <label for="productName" class="form-label">Product Name</label>
                                            <input type="text" id="productName" name="name" class="form-control" value="{{ $product->name }}" required>
                </div>

                <div class="mb-3">
                                            <label for="productDescription" class="form-label">Description</label>
                                            <textarea id="productDescription" name="description" class="form-control" rows="5" required>{{ $product->description }}</textarea>
                                            <div class="form-text">Provide a detailed description of your product. This helps customers make informed decisions.</div>
                </div>

                                        <div class="row">
                                            <div class="col-md-6">
                <div class="mb-3">
                                                    <label for="productPrice" class="form-label">Price (₱)</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" id="productPrice" name="price" step="0.01" min="0" class="form-control" value="{{ $product->price }}" required>
                                                    </div>
                                                </div>
                </div>
                                            <div class="col-md-6">
                <div class="mb-3">
                                                    <label for="productStock" class="form-label">Stock</label>
                                                    <input type="number" id="productStock" name="stock" min="0" class="form-control" value="{{ $product->stock }}" required>
                                                </div>
                                            </div>
                </div>

                <div class="mb-3">
                                            <label for="productAvailability" class="form-label">Availability</label>
                                            <select id="productAvailability" name="availability" class="form-select">
                        <option value="1" {{ $product->availability ? 'selected' : '' }}>Available</option>
                        <option value="0" {{ !$product->availability ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <div class="mb-3">
                                            <label for="productCategory" class="form-label">Category</label>
                                            <select id="productCategory" name="category_id" class="form-select">
                                                <option value="">Select a category</option>
                                                @foreach(\App\Models\Category::all() as $category)
                                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Current Image</label>
                                                    <div class="text-center mb-3">
                                                        <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid img-thumbnail" 
                                                            style="max-height: 200px; width: auto;"
                                                            onerror="this.onerror=null; this.src='{{ asset('images/placeholder.png') }}';">
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="productImage" class="form-label">Change Image</label>
                                                    <input type="file" id="productImage" name="image" class="form-control" accept="image/*">
                                                    <div class="form-text">Recommended size: 800x800 pixels, max 2MB</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Variations Tab -->
                        <div class="tab-pane fade" id="variations" role="tabpanel" aria-labelledby="variations-tab">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Product Variations</h5>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addVariationModal">
                                    <i class="bi bi-plus-circle"></i> Add Variation
                                </button>
                            </div>

                            @if(count($product->variations) > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Price (₱)</th>
                                                <th>Stock</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($product->variations as $variation)
                                                <tr>
                                                    <td>{{ $variation->name }}</td>
                                                    <td>₱{{ number_format($variation->price, 2) }}</td>
                                                    <td>{{ $variation->stock }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $variation->stock > 0 ? 'success' : 'danger' }}">
                                                            {{ $variation->stock > 0 ? 'Available' : 'Out of Stock' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-primary edit-variation" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editVariationModal"
                                                                data-id="{{ $variation->id }}"
                                                                data-name="{{ $variation->name }}"
                                                                data-price="{{ $variation->price }}"
                                                                data-stock="{{ $variation->stock }}">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger delete-variation" 
                                                                data-id="{{ $variation->id }}"
                                                                data-name="{{ $variation->name }}">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> No variations added yet. Click the "Add Variation" button to create variations.
                                </div>
                            @endif

                            <div class="card bg-light mt-4">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-lightbulb"></i> Tip</h6>
                                    <p class="card-text">Use variations for different sizes, colors, or styles of the same product. This helps customers choose exactly what they want.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Variation Modal -->
<div class="modal fade" id="addVariationModal" tabindex="-1" aria-labelledby="addVariationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('seller.store_variation') }}" method="POST" id="addVariationForm">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="addVariationModalLabel">Add Variation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="variationName" class="form-label">Variation Name</label>
                        <input type="text" class="form-control" id="variationName" name="name" placeholder="e.g. Small, Red, etc." required>
                    </div>
                    <div class="mb-3">
                        <label for="variationPrice" class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" id="variationPrice" name="price" min="0" step="0.01" value="{{ $product->price }}" required>
                        <div class="form-text">You can set a different price for this variation</div>
                    </div>
                <div class="mb-3">
                        <label for="variationStock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="variationStock" name="stock" min="0" value="10" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Variation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Variation Modal -->
<div class="modal fade" id="editVariationModal" tabindex="-1" aria-labelledby="editVariationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editVariationForm">
                @csrf
                @method('PUT')
                
                <div class="modal-header">
                    <h5 class="modal-title" id="editVariationModalLabel">Edit Variation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editVariationName" class="form-label">Variation Name</label>
                        <input type="text" class="form-control" id="editVariationName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editVariationPrice" class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" id="editVariationPrice" name="price" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="editVariationStock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="editVariationStock" name="stock" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Variation Confirmation Form (Hidden) -->
<form id="deleteVariationForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit variation button handler
    document.querySelectorAll('.edit-variation').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const price = this.getAttribute('data-price');
            const stock = this.getAttribute('data-stock');
            
            document.getElementById('editVariationName').value = name;
            document.getElementById('editVariationPrice').value = price;
            document.getElementById('editVariationStock').value = stock;
            
            const form = document.getElementById('editVariationForm');
            form.action = `/seller/variations/${id}/update`;
        });
    });
    
    // Delete variation button handler
    document.querySelectorAll('.delete-variation').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            if (confirm(`Are you sure you want to delete the variation "${name}"? This action cannot be undone.`)) {
                const form = document.getElementById('deleteVariationForm');
                form.action = `/seller/variation/delete/${id}`;
                form.submit();
            }
        });
    });
    
    // Image preview on file selection
    const imageInput = document.getElementById('productImage');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgPreview = imageInput.closest('.card').querySelector('img');
                    imgPreview.src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});
</script>

<style>
.nav-tabs .nav-link {
    color: #495057;
    font-weight: 500;
}
.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
}
.table th {
    font-weight: 600;
}
.form-label {
    font-weight: 500;
}
</style>
@endsection
