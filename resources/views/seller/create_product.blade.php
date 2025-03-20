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
                    <label for="image">Product Image</label>
                    <input type="file" name="image" id="image" class="form-control">
                </div>
                <button type="submit" class="btn" style="background-color: #5D6E54; color: white;">Add Product</button>
            </form>
        </div>
    </div>
@endsection
