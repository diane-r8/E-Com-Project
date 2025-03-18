@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Add Product</h1>
        <form action="{{ route('seller.store_product') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label>Price</label>
                <input type="number" name="price" step="0.01" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Stock</label>
                <input type="number" name="stock" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Availability</label>
                <select name="availability" class="form-control">
                    <option value="1">Available</option>
                    <option value="0">Out of Stock</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Product Image</label>
                <input type="file" name="image" class="form-control">
            </div>
            <button type="submit" class="btn btn-success">Add Product</button>
        </form>
    </div>
@endsection
