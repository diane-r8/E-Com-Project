@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <!-- Profile Card Wrapper for Edit Product -->
        <div class="card shadow-sm p-4" style="background-color: #FFFFFF; border-radius: 10px;">
            <h1 class="text-center mb-4" style="color: #5D6E54;">Edit Product</h1>

            <form action="{{ route('seller.update_product', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                </div>

                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" required>{{ $product->description }}</textarea>
                </div>

                <div class="mb-3">
                    <label>Price</label>
                    <input type="number" name="price" step="0.01" class="form-control" value="{{ $product->price }}" required>
                </div>

                <div class="mb-3">
                    <label>Stock</label>
                    <input type="number" name="stock" class="form-control" value="{{ $product->stock }}" required>
                </div>

                <div class="mb-3">
                    <label>Availability</label>
                    <select name="availability" class="form-control">
                        <option value="1" {{ $product->availability ? 'selected' : '' }}>Available</option>
                        <option value="0" {{ !$product->availability ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Current Image</label>
                    <div>
                        <img src="{{ asset('storage/' . $product->image) }}" width="100">
                    </div>
                </div>

                <div class="mb-3">
                    <label>Change Product Image</label>
                    <input type="file" name="image" class="form-control">
                </div>

                <button type="submit" class="btn btn-success w-100 mt-3">Update Product</button>
            </form>
        </div>
    </div>
@endsection
