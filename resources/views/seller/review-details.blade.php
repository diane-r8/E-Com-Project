@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Review Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('seller.reviews') }}">Reviews</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Review #{{ $review->id }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('seller.reviews') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>Back to Reviews
        </a>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Product Information</h5>
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset('images/' . $review->product->image) }}" 
                                         alt="{{ $review->product->name }}" 
                                         class="rounded me-3"
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1">{{ $review->product->name }}</h6>
                                        <a href="{{ route('products.index', ['product_id' => $review->product_id]) }}" 
                                           class="text-primary text-decoration-none">
                                            View Product
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Customer Information</h5>
                                <p class="mb-1"><strong>Username:</strong> {{ $review->user->username }}</p>
                                <p class="mb-1"><strong>Order ID:</strong> #{{ $review->order_id }}</p>
                                <p class="mb-1"><strong>Review Date:</strong> {{ $review->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Rating</h5>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rating-stars me-3">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-warning"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="fs-4 fw-bold text-warning">{{ number_format($review->rating, 1) }}</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Status</h5>
                                <span class="badge bg-{{ $review->status == 'approved' ? 'success' : ($review->status == 'pending' ? 'warning' : 'danger') }} p-2">
                                    {{ ucfirst($review->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Review Content</h5>
                        <div class="p-4 bg-light rounded">
                            {{ $review->review ?? 'No written review provided.' }}
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        @if($review->status != 'approved')
                            <form action="{{ route('seller.reviews.approve', $review->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this review?')">
                                    <i class="bi bi-check-lg me-1"></i>Approve Review
                                </button>
                            </form>
                        @endif
                        
                        @if($review->status != 'rejected')
                            <form action="{{ route('seller.reviews.reject', $review->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this review?')">
                                    <i class="bi bi-x-lg me-1"></i>Reject Review
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.rating-stars {
    font-size: 1.5rem;
}

.rating-stars .fas,
.rating-stars .far {
    margin-right: 2px;
}

.badge {
    font-size: 0.9rem;
}

.bg-warning { background-color: #FFE5A3 !important; color: #97722A !important; }
.bg-success { background-color: #A8E6B4 !important; color: #0F6A1F !important; }
.bg-danger { background-color: #FFB3B3 !important; color: #A12828 !important; }
</style>
@endpush
@endsection 