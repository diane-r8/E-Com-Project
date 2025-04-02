@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <!-- Form Wrapper -->
        <div class="card shadow-sm p-4" style="background-color: #FFFFFF; border-radius: 10px;">
            <h1 class="text-center mb-4" style="color: #5D6E54;">Add Product</h1>

            <!-- Product Form -->
            <form action="{{ route('seller.store_product') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="name">Product Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="price">Price</label>
                    <input type="number" name="price" id="price" step="0.01" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="stock">Stock</label>
                    <input type="number" name="stock" id="stock" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="availability">Availability</label>
                    <select name="availability" id="availability" class="form-control">
                        <option value="1">Available</option>
                        <option value="0">Out of Stock</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="category">Category</label>
                    <select name="category_id" id="category" class="form-control" required>
                        <option value="">Select a Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Variations Section -->
                <div class="mb-3">
                    <h4>Variations</h4>
                    <div id="variations-container">
                        <div class="variation d-flex gap-2 mb-2">
                            <input type="text" name="variations[0][name]" class="form-control" placeholder="Size/Color" required>
                            <input type="number" name="variations[0][price]" class="form-control" placeholder="Price">
                            <input type="number" name="variations[0][stock]" class="form-control" placeholder="Stock" required>
                            <button type="button" class="btn btn-danger remove-variation" onclick="removeVariation(this)">X</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" onclick="addVariation()">+ Add Variation</button>
                </div>

                <div class="mb-3">
                    <label for="image">Product Image</label>
                    <input type="file" name="image" id="image" class="form-control">
                </div>

                <button type="submit" class="btn" style="background-color: #5D6E54; color: white;">Add Product</button>
            </form>
        </div>
    </div>

    <!-- JavaScript for Adding Variations Dynamically -->
    <script>
        let variationCount = 1;

        function addVariation() {
            let container = document.getElementById('variations-container');
            let newVariation = document.createElement('div');
            newVariation.classList.add('variation', 'd-flex', 'gap-2', 'mb-2');
            newVariation.innerHTML = `
                <input type="text" name="variations[${variationCount}][name]" class="form-control" placeholder="Size/Color" required>
                <input type="number" name="variations[${variationCount}][price]" class="form-control" placeholder="Price">
                <input type="number" name="variations[${variationCount}][stock]" class="form-control" placeholder="Stock" required>
                <button type="button" class="btn btn-danger remove-variation" onclick="removeVariation(this)">X</button>
            `;
            container.appendChild(newVariation);
            variationCount++;
        }

        function removeVariation(button) {
            button.parentElement.remove();
        }
    </script>
@endsection
