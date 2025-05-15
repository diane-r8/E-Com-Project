@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Product Reviews</h1>
    </div>

    <!-- Status Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted fw-semibold mb-1">Total Reviews</h6>
                            <h2 class="mb-0">{{ $reviews->count() }}</h2>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: #E8F0FE;">
                            <i class="bi bi-chat-square-text text-primary fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3 mb-0 text-muted small">
                        <i class="bi bi-info-circle me-1"></i> All reviews received
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted fw-semibold mb-1">Pending Reviews</h6>
                            <h2 class="mb-0">{{ $reviews->where('status', 'pending')->count() }}</h2>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: #FFF3CD;">
                            <i class="bi bi-clock-history text-warning fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3 mb-0 text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Reviews awaiting moderation
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted fw-semibold mb-1">Average Rating</h6>
                            <h2 class="mb-0">{{ number_format($reviews->avg('rating'), 1) }}</h2>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: #E0F8E9;">
                            <i class="bi bi-star-half text-success fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-warning">
                            @php
                                $avgRating = $reviews->avg('rating');
                                $fullStars = floor($avgRating);
                                $hasHalfStar = $avgRating - $fullStars >= 0.5;
                            @endphp
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $fullStars)
                                    <i class="fas fa-star"></i>
                                @elseif($i == $fullStars + 1 && $hasHalfStar)
                                    <i class="fas fa-star-half-alt"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted fw-semibold mb-1">Approved Reviews</h6>
                            <h2 class="mb-0">{{ $reviews->where('status', 'approved')->count() }}</h2>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: #E0F4FF;">
                            <i class="bi bi-check2-circle text-info fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3 mb-0 text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Published reviews
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-primary">All Reviews</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="reviewsDataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                            <tr>
                                <td>{{ $review->id }}</td>
                                <td>
                                    <a href="{{ route('products.index', ['product_id' => $review->product_id]) }}" target="_blank" class="text-decoration-none">
                                        {{ $review->product->name }}
                                    </a>
                                </td>
                                <td>{{ $review->user->username }}</td>
                                <td>
                                    <div class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-warning"></i>
                                            @endif
                                        @endfor
                                        <span class="ms-1 text-muted">({{ number_format($review->rating, 1) }})</span>
                                    </div>
                                </td>
                                <td>{{ Str::limit($review->review, 50) }}</td>
                                <td>{{ $review->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $review->status == 'approved' ? 'success' : ($review->status == 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($review->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('seller.reviews.show', $review->id) }}" class="btn btn-sm btn-soft-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        
                                        @if($review->status != 'approved')
                                            <form action="{{ route('seller.reviews.approve', $review->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-soft-success" onclick="return confirm('Are you sure you want to approve this review?')">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if($review->status != 'rejected')
                                            <form action="{{ route('seller.reviews.reject', $review->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-soft-danger" onclick="return confirm('Are you sure you want to reject this review?')">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        @endif
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

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
.card {
    transition: all 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

.btn-group {
    gap: 0.25rem;
}

.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
    vertical-align: middle;
}

.badge {
    padding: 0.5em 0.75em;
}

/* Pastel Colors for Status Badges */
.bg-warning { background-color: #FFE5A3 !important; color: #97722A !important; }
.bg-info { background-color: #B6E3FF !important; color: #1A6FB5 !important; }
.bg-success { background-color: #A8E6B4 !important; color: #0F6A1F !important; }
.bg-danger { background-color: #FFB3B3 !important; color: #A12828 !important; }

/* Soft Button Styles */
.btn-soft-info {
    color: #36b9cc;
    background-color: rgba(54, 185, 204, 0.1);
    border: none;
}

.btn-soft-info:hover {
    color: #fff;
    background-color: #36b9cc;
}

.btn-soft-success {
    color: #1cc88a;
    background-color: rgba(28, 200, 138, 0.1);
    border: none;
}

.btn-soft-success:hover {
    color: #fff;
    background-color: #1cc88a;
}

.btn-soft-danger {
    color: #e74a3b;
    background-color: rgba(231, 74, 59, 0.1);
    border: none;
}

.btn-soft-danger:hover {
    color: #fff;
    background-color: #e74a3b;
}

/* Star Rating Styles */
.rating-stars {
    color: #ffc107;
    font-size: 0.9rem;
}

.rating-stars .fas {
    margin-right: 1px;
}

.rating-stars .far {
    margin-right: 1px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#reviewsDataTable').DataTable({
        order: [[5, 'desc']], // Sort by date column descending
        columnDefs: [
            { orderable: false, targets: [7] } // Disable sorting on actions column
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search reviews..."
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });
});
</script>
@endpush
@endsection 