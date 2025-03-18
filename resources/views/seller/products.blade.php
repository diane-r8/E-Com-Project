@extends('layouts.app')

@section('content')
<div class="container">
    <h1>My Products</h1>

    <a href="{{ route('seller.create_product') }}" class="btn btn-primary mb-3">Add Product</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
            <tr>
                <td>
                    @if ($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}"  width="50" height="50" style="object-fit: cover;">
                    @else
                        No Image
                    @endif
                </td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->description }}</td>
                <td>{{ $product->price }}</td>
                <td>{{ $product->stock }}</td>
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

<!-- JavaScript for Modal -->
<script>
    function confirmDelete(productId) {
        var form = document.getElementById('deleteForm');
        form.action = "{{ route('seller.delete_product', ':id') }}".replace(':id', productId);
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
</script>

@endsection
