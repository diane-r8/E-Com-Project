@extends('layouts.app')

@section('content')
<style>
    /* Professional Profile Page Styling */
    .profile-section {
        background-color: #f8f9fa;
        border-radius: 12px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .profile-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }
    
    .profile-card:hover {
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.12);
    }
    
    .card-header-gradient {
        background-color:rgb(255, 216, 195);
        color: white;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
    }
    
    .profile-sidebar {
        background-color:rgb(254, 247, 243);
        border-right: 1px solid rgba(0,0,0,0.05);
    }
    
    .profile-avatar {
        width: 160px;
        height: 160px;
        object-fit: cover;
        border: 5px solid #fff;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .profile-info-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .profile-info-item:last-child {
        border-bottom: none;
    }
    
    .profile-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 50rem;
        font-weight: 600;
        font-size: 0.75rem;
    }
    
    .btn-profile {
        border-radius: 0.5rem;
        padding: 0.6rem 1.2rem;
        font-weight: 500;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.2s;
        position: relative;
    }
    
    .btn-profile:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    
    .order-list-item {
        transition: all 0.2s;
    }
    
    .order-list-item:hover {
        background-color: rgba(0,0,0,0.02);
    }
    
    .modal-content {
        border-radius: 0.75rem;
    }
    
    .modal-header.gradient-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
    }
    
    .order-item {
        transition: all 0.2s;
    }
    
    .order-item:hover {
        background-color: rgba(0,0,0,0.01);
    }
    
    .form-control, .form-select {
        border-radius: 0.5rem;
        padding: 0.6rem 1rem;
    }
    
    .input-group-text {
        border-radius: 0.5rem 0 0 0.5rem;
    }
    
    .input-group .form-control {
        border-radius: 0 0.5rem 0.5rem 0;
    }
    
    .img-thumbnail.profile-image {
        padding: 0.25rem;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 50%;
        max-width: 100%;
    }
    
    /* Animated Star Rating System */
    .rating-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .star-rating-animated {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        position: relative;
    }
    
    .star-input {
        display: none;
    }
    
    .star {
        font-size: 2.5rem;
        cursor: pointer;
        margin: 0 5px;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .star i {
        color: #e0e0e0;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .star:hover i,
    .star:hover ~ .star i,
    .star-input:checked ~ .star i {
        color: #FFD700;
    }
    
    .star:hover i,
    .star-input:checked ~ .star i {
        transform: scale(1.3);
        text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
    }
    
    /* Animation when hovering */
    .star i {
        animation-duration: 0.5s;
        animation-fill-mode: both;
    }
    
    .star:hover i {
        animation-name: star-pulse;
    }
    
    @keyframes star-pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.3); }
        100% { transform: scale(1.2); }
    }
    
    /* Tooltip styling */
    .rating-tooltip {
        position: absolute;
        bottom: -30px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #333;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .rating-tooltip::before {
        content: '';
        position: absolute;
        top: -5px;
        left: 50%;
        transform: translateX(-50%);
        border-width: 0 5px 5px 5px;
        border-style: solid;
        border-color: transparent transparent #333 transparent;
    }
    
    .star:hover .rating-tooltip {
        visibility: visible;
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
    
    .rating-message {
        font-weight: 500;
        height: 20px;
        color:rgb(168, 182, 161);
    }

    /* Add notification animation */
    @keyframes notification-pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    .badge:not(.d-none) {
        animation: notification-pulse 1s infinite;
    }

    /* Position the badge properly */
    .position-relative {
        display: inline-flex !important;
    }

    .badge {
        font-size: 0.65rem;
        min-width: 1.25rem;
        height: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.25rem;
        transform: translate(-50%, -50%) scale(0.9);
    }

    .badge:not(.d-none) {
        animation: badge-pop 0.3s ease-out;
    }
    
    @keyframes badge-pop {
        0% { transform: translate(-50%, -50%) scale(0); }
        50% { transform: translate(-50%, -50%) scale(1.2); }
        100% { transform: translate(-50%, -50%) scale(0.9); }
    }
    
    .bi-chat-dots-fill {
        color: #0d6efd;
    }
</style>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Success message -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Profile Main Card -->
            <div class="card profile-card mb-4">
                <div class="card-header card-header-gradient d-flex align-items-center">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <h4 class="mb-0">User Profile</h4>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <!-- Profile Sidebar -->
                        <div class="col-md-4 profile-sidebar">
                            <div class="p-4 text-center">
                                <div class="position-relative d-inline-block mb-3">
                                    <img src="{{ $user->profile && $user->profile->profile_picture 
                                        ? asset('storage/' . $user->profile->profile_picture) 
                                        : asset('images/default-profile.jpg') }}" 
                                        class="profile-avatar rounded-circle img-thumbnail shadow" alt="Profile Picture">
                                </div>
                                
                                <h4 class="fw-bold mb-1">{{ $user->fname }} {{ $user->lname }}</h4>
                                <p class="text-muted mb-3"><i class="bi bi-person me-1"></i><span class="fst-italic">@</span>{{ $user->username }}</p>
                                
                                @if(!session('edit_mode') && !session('password_mode'))
                                    <div class="d-grid gap-2 mt-4">
                                        <a href="{{ route('user.profile.edit') }}" class="btn btn-primary btn-profile">
                                            <i class="bi bi-pencil-square me-2"></i>Edit Profile
                                        </a>
                                        
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger w-100 btn-profile">
                                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('user.profile.destroy') }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100 btn-profile" 
                                                onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                                                <i class="bi bi-trash me-2"></i>Delete Account
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Profile Info / Edit Forms -->
                        <div class="col-md-8">
                            @if(session('edit_mode') || session('password_mode'))
                                <!-- Profile Edit Form -->
                                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form"
                                    class="p-4" style="{{ session('password_mode') ? 'display: none;' : '' }}">
                                    @csrf
                                    @method('PUT')
                                    
                                    <h5 class="border-bottom pb-2 mb-4">Edit Your Profile</h5>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text">@</span>
                                                <input type="text" class="form-control" name="username" value="{{ old('username', $user->username) }}" required>
                                            </div>
                                            @error('username') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Email</label>
                                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">First Name</label>
                                            <input type="text" class="form-control" name="fname" value="{{ old('fname', $user->fname) }}" required>
                                            @error('fname') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Last Name</label>
                                            <input type="text" class="form-control" name="lname" value="{{ old('lname', $user->lname) }}" required>
                                            @error('lname') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Gender</label>
                                            <select class="form-select" name="gender">
                                                <option value="" {{ is_null($user->profile->gender) ? 'selected' : '' }}>Not specified</option>
                                                <option value="male" {{ $user->profile->gender === 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ $user->profile->gender === 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ $user->profile->gender === 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Profile Picture</label>
                                            <input type="file" class="form-control" name="profile_picture" accept="image/*">
                                            
                                            <div class="form-check mt-2">
                                                <input type="checkbox" class="form-check-input" id="remove_profile_picture" name="remove_profile_picture" value="1">
                                                <label class="form-check-label" for="remove_profile_picture">Remove current picture</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="two_factor_enabled" value="0">
                                            <input type="checkbox" class="form-check-input" name="two_factor_enabled" id="two_factor_enabled" value="1"
                                                {{ $user->two_factor_enabled ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="two_factor_enabled">Enable Two-Factor Authentication (2FA)</label>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-1"></i> Save Changes
                                        </button>
                                        <a href="{{ route('user.profile') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-lg me-1"></i> Cancel
                                        </a>
                                        <button type="button" class="btn btn-outline-dark ms-auto" id="toggle-password-form">
                                            <i class="bi bi-key me-1"></i> Change Password
                                        </button>
                                    </div>
                                </form>

                                <!-- Change Password Form -->
                                <form action="{{ route('user.profile.password.update') }}" method="POST" id="password-form"
                                    class="p-4" style="{{ session('password_mode') ? '' : 'display: none;' }}">
                                    @csrf
                                    @method('PUT')
                                    
                                    <h5 class="border-bottom pb-2 mb-4">Change Your Password</h5>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Current Password</label>
                                        <input type="password" class="form-control" name="current_password">
                                        @error('current_password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">New Password</label>
                                        <input type="password" class="form-control" name="password">
                                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Confirm New Password</label>
                                        <input type="password" class="form-control" name="password_confirmation">
                                        @error('password_confirmation') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="d-flex gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-1"></i> Update Password
                                        </button>
                                        <a href="{{ route('user.profile') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-lg me-1"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            @else
                                <!-- Profile Information Display -->
                                <div class="p-4">
                                    <h5 class="fw-bold border-bottom pb-2 mb-4">
                                        <i class="bi bi-info-circle me-2 text-primary"></i>Account Information
                                    </h5>
                                    
                                    <div class="row mb-4">
                                        <div class="col-lg-6">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item profile-info-item d-flex justify-content-between px-0 py-3 border-0">
                                                    <span class="text-muted"><i class="bi bi-person me-2"></i>Username:</span>
                                                    <span class="fw-semibold">{{ $user->username }}</span>
                                                </li>
                                                <li class="list-group-item profile-info-item d-flex justify-content-between px-0 py-3 border-0">
                                                    <span class="text-muted"><i class="bi bi-envelope me-2"></i>Email:</span>
                                                    <span class="fw-semibold">{{ $user->email }}</span>
                                                </li>
                                                <li class="list-group-item profile-info-item d-flex justify-content-between px-0 py-3 border-0">
                                                    <span class="text-muted"><i class="bi bi-gender-ambiguous me-2"></i>Gender:</span>
                                                    <span class="fw-semibold">{{ ucfirst($user->profile->gender ?? 'Not specified') }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-lg-6">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item profile-info-item d-flex justify-content-between px-0 py-3 border-0">
                                                    <span class="text-muted"><i class="bi bi-shield-lock me-2"></i>Two-Factor Auth:</span>
                                                    <span class="fw-semibold">
                                                        @if($user->two_factor_enabled)
                                                            <span class="profile-badge bg-success">Enabled</span>
                                                        @else
                                                            <span class="profile-badge bg-secondary">Disabled</span>
                                                        @endif
                                                    </span>
                                                </li>
                                                <li class="list-group-item profile-info-item d-flex justify-content-between px-0 py-3 border-0">
                                                    <span class="text-muted"><i class="bi bi-bag me-2"></i>Total Orders:</span>
                                                    <span class="fw-semibold">{{ $user->orders->count() }}</span>
                                                </li>
                                                <li class="list-group-item profile-info-item d-flex justify-content-between px-0 py-3 border-0">
                                                    <span class="text-muted"><i class="bi bi-calendar3 me-2"></i>Member Since:</span>
                                                    <span class="fw-semibold">{{ $user->created_at->format('M d, Y') }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase History Card -->
            <div class="card profile-card">
                <div class="card-header card-header-gradient d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-bag-check fs-4 me-2"></i>
                        <h4 class="mb-0">Purchase History</h4>
                    </div>
                    <span class="profile-badge bg-light text-dark">{{ $user->orders->count() }} Orders</span>
                </div>
                
                <div class="card-body p-0">
                    @if($user->orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="ps-3">Order ID</th>
                                        <th>Date</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->orders as $index => $order)
                                        <tr class="order-list-item">
                                            <td class="ps-3">
                                                <span class="fw-semibold">#{{ $order->id }}</span>
                                            </td>
                                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                                            <td>₱{{ number_format($order->total_price, 2) }}</td>
                                            <td>
                                                <span class="profile-badge 
                                                    @if($order->status == 'pending') bg-warning
                                                    @elseif($order->status == 'processing') bg-info
                                                    @elseif($order->status == 'shipped') bg-primary
                                                    @elseif($order->status == 'delivered') bg-success
                                                    @elseif($order->status == 'cancelled') bg-danger
                                                    @else bg-secondary @endif">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button class="btn btn-sm btn-primary btn-profile" data-bs-toggle="modal" data-bs-target="#orderModal{{ $order->id }}">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <a href="{{ route('chat.order', $order->id) }}" class="btn btn-sm btn-outline-info btn-profile position-relative">
                                                        <i class="bi bi-chat-dots" id="chat-icon-{{ $order->id }}"></i>
                                                        <span id="chat-badge-{{ $order->id }}" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                                                            0
                                                        </span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center p-5">
                            <i class="bi bi-cart text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">No purchase history found.</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary btn-profile mt-2">
                                <i class="bi bi-shop me-2"></i>Browse Products
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals: Order Details -->
@foreach($user->orders as $order)
    <div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1" aria-labelledby="orderModalLabel{{ $order->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header gradient-header">
                    <h5 class="modal-title" id="orderModalLabel{{ $order->id }}">
                        <i class="bi bi-bag me-2"></i>Order #{{ $order->id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-calendar3"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">Order Date</p>
                                    <p class="fw-bold mb-0">{{ $order->created_at->format('F d, Y h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-truck"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">Status</p>
                                    <span class="profile-badge 
                                        @if($order->status == 'pending') bg-warning
                                        @elseif($order->status == 'processing') bg-info
                                        @elseif($order->status == 'shipped') bg-primary
                                        @elseif($order->status == 'delivered') bg-success
                                        @elseif($order->status == 'cancelled') bg-danger
                                        @else bg-secondary @endif py-2 px-3">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <div class="icon-circle bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px;">
                            <i class="bi bi-box"></i>
                        </div>
                        Order Items
                    </h6>
                    <ul class="list-group mb-4">
                        @foreach($order->items as $item)
                            @php
                                $imagePath = asset("images/default.png");
                                $filename = $item->product->image ?? null;

                                if ($filename) {
                                    $extensions = ['jpg', 'jpeg', 'png', 'webp'];
                                    foreach ($extensions as $ext) {
                                        if (file_exists(public_path("images/{$filename}.{$ext}"))) {
                                            $imagePath = asset("images/{$filename}.{$ext}");
                                            break;
                                        }
                                    }
                                }
                            @endphp

                            <li class="list-group-item p-3 order-item">
                                <div class="d-flex align-items-center">
                                    <img 
                                        src="{{ $imagePath }}" 
                                        alt="{{ $item->product->name }}" 
                                        class="me-3 rounded" 
                                        style="width: 60px; height: 60px; object-fit: cover;"
                                    >

                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">
                                            {{ $item->product->name }}
                                            @if($item->variation)
                                                <span class="profile-badge bg-light text-dark">{{ $item->variation->name }}</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small">₱{{ number_format($item->price, 2) }} × {{ $item->quantity }}</div>
                                    </div>

                                    <div class="text-end fw-semibold">
                                        ₱{{ number_format($item->price * $item->quantity, 2) }}
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="card bg-light border-0 p-3 mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span>₱{{ number_format($order->total_price - $order->delivery_fee, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivery Fee:</span>
                            <span>₱{{ number_format($order->delivery_fee, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top">
                            <span class="fw-bold">Total Amount:</span>
                            <span class="fw-bold">₱{{ number_format($order->total_price, 2) }}</span>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <!-- Cancel Order: show but disable if status is not 'pending' -->
                        <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button 
                                type="submit" 
                                class="btn btn-outline-danger btn-profile"
                                onclick="return confirm('Are you sure you want to cancel this order?')"
                                @if($order->status !== 'Pending') disabled @endif>
                                <i class="bi bi-x-circle me-1"></i>Cancel Order
                            </button>
                        </form>

                        <!-- Mark as Received: show but disable if status is not 'delivered' -->
                        <form action="{{ route('orders.received', $order->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button 
                                type="submit" 
                                class="btn btn-outline-success btn-profile"
                                onclick="return confirm('Mark this order as received?')"
                                @if(strtolower($order->status) !== 'delivered' && $order->status !== 'Delivered') disabled @endif>
                                <i class="bi bi-check-circle me-1"></i>Mark as Received
                            </button>
                        </form>

                        <!-- Rate Button: show but disable if status is not 'received' -->
                        <button type="button"
                                class="btn btn-outline-primary btn-profile"
                                data-bs-toggle="modal"
                                data-bs-target="#rateModal{{ $order->id }}"
                                @if(strtolower($order->status) !== 'received')
                                    style="pointer-events: none; opacity: 0.5; cursor: not-allowed;"
                                    title="You can only rate once the order is marked as 'Received'"
                                @endif>
                            <i class="bi bi-star me-1"></i>Rate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Modals: Rate Order -->
@foreach ($user->orders as $order)
    <div class="modal fade" id="rateModal{{ $order->id }}" tabindex="-1" aria-labelledby="rateModalLabel{{ $order->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('orders.rate', $order->id) }}" method="POST">
                    @csrf
                    <div class="modal-header gradient-header">
                        <h5 class="modal-title" id="rateModalLabel{{ $order->id }}">
                            <i class="bi bi-star me-2"></i>Rate Your Order
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <div class="icon-circle bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-star-fill fs-4"></i>
                            </div>
                            <h5>Share Your Experience</h5>
                            <p class="text-muted">Your feedback helps improve our products and services</p>
                            <p class="text-info"><small>Note: Your review will be applied to all products in this order</small></p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-3">Your Rating:</label>
                            <div class="rating-container">
                                <div class="star-rating-animated">
                                    <input type="radio" name="rating" id="star5-{{ $order->id }}" value="5" class="star-input" required>
                                    <label for="star5-{{ $order->id }}" class="star" title="Excellent">
                                        <i class="bi bi-star-fill"></i>
                                        <span class="rating-tooltip">Excellent</span>
                                    </label>
                                    
                                    <input type="radio" name="rating" id="star4-{{ $order->id }}" value="4" class="star-input">
                                    <label for="star4-{{ $order->id }}" class="star" title="Very Good">
                                        <i class="bi bi-star-fill"></i>
                                        <span class="rating-tooltip">Very Good</span>
                                    </label>
                                    
                                    <input type="radio" name="rating" id="star3-{{ $order->id }}" value="3" class="star-input">
                                    <label for="star3-{{ $order->id }}" class="star" title="Good">
                                        <i class="bi bi-star-fill"></i>
                                        <span class="rating-tooltip">Good</span>
                                    </label>
                                    
                                    <input type="radio" name="rating" id="star2-{{ $order->id }}" value="2" class="star-input">
                                    <label for="star2-{{ $order->id }}" class="star" title="Fair">
                                        <i class="bi bi-star-fill"></i>
                                        <span class="rating-tooltip">Fair</span>
                                    </label>
                                    
                                    <input type="radio" name="rating" id="star1-{{ $order->id }}" value="1" class="star-input">
                                    <label for="star1-{{ $order->id }}" class="star" title="Poor">
                                        <i class="bi bi-star-fill"></i>
                                        <span class="rating-tooltip">Poor</span>
                                    </label>
                                </div>
                                
                                <div class="rating-message mt-2 text-center">
                                    <span id="rating-text-{{ $order->id }}">Select your rating</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="review" class="form-label fw-semibold">Your Feedback:</label>
                            <textarea name="review" class="form-control" rows="4" placeholder="Tell us more about your experience..."></textarea>
                            <div class="form-text">Your honest feedback helps other customers and improves our service.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-profile" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-profile">
                            <i class="bi bi-send me-1"></i>Submit Rating
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password form
        const togglePasswordFormBtn = document.getElementById('toggle-password-form');
        const profileForm = document.getElementById('profile-form');
        const passwordForm = document.getElementById('password-form');
        
        if (togglePasswordFormBtn && profileForm && passwordForm) {
            togglePasswordFormBtn.addEventListener('click', function() {
                profileForm.style.display = 'none';
                passwordForm.style.display = 'block';
            });
        }
        
        // Enhanced star rating functionality
        const ratingLabels = {
            5: ['Excellent', '#FFD700'],
            4: ['Very Good', '#FFD700'],
            3: ['Good', '#FFD700'],
            2: ['Fair', '#FFD700'],
            1: ['Poor', '#FFD700']
        };
        
        // Get all rating modals
        const ratingModals = document.querySelectorAll('[id^="rateModal"]');
        
        ratingModals.forEach(modal => {
            const orderId = modal.id.replace('rateModal', '');
            const stars = modal.querySelectorAll('.star-input');
            const ratingText = document.getElementById(`rating-text-${orderId}`);
            
            // Add event listeners to each star
            stars.forEach(star => {
                star.addEventListener('change', function() {
                    const ratingValue = this.value;
                    const [text, color] = ratingLabels[ratingValue];
                    
                    if (ratingText) {
                        ratingText.textContent = text;
                        ratingText.style.color = color;
                    }
                    
                    // Apply animations to selected stars
                    const starLabels = modal.querySelectorAll('.star i');
                    starLabels.forEach((starLabel, index) => {
                        const starPosition = 5 - index; // Reverse index due to flex-direction
                        
                        if (starPosition <= ratingValue) {
                            // Add a staggered animation for selected stars
                            starLabel.style.animationDelay = `${(starPosition - 1) * 0.05}s`;
                            starLabel.style.animationName = 'star-pulse';
                        } else {
                            starLabel.style.animationName = '';
                        }
                    });
                });
            });
            
            // Interactive hover effects
            const starLabels = modal.querySelectorAll('.star');
            
            starLabels.forEach(label => {
                label.addEventListener('mouseover', function() {
                    const starValue = this.getAttribute('for').split('-')[0].replace('star', '');
                    const [text, _] = ratingLabels[starValue];
                    
                    if (ratingText) {
                        ratingText.textContent = text;
                    }
                });
                
                label.addEventListener('mouseout', function() {
                    const checkedStar = modal.querySelector('.star-input:checked');
                    
                    if (checkedStar) {
                        const ratingValue = checkedStar.value;
                        const [text, _] = ratingLabels[ratingValue];
                        
                        if (ratingText) {
                            ratingText.textContent = text;
                        }
                    } else if (ratingText) {
                        ratingText.textContent = 'Select your rating';
                        ratingText.style.color = '';
                    }
                });
            });
        });

        // Function to check unread messages for each order
        function checkUnreadMessagesPerOrder() {
            const orders = @json($user->orders->pluck('id'));
            console.log('Checking messages for orders:', orders);
            
            orders.forEach(orderId => {
                console.log('Checking messages for order:', orderId);
                fetch(`/chat/unread-count/${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Received data for order', orderId, ':', data);
                        const badge = document.getElementById(`chat-badge-${orderId}`);
                        const icon = document.getElementById(`chat-icon-${orderId}`);
                        
                        if (badge && icon) {
                            if (data.count > 0) {
                                console.log('Showing badge for order', orderId, 'with count', data.count);
                                badge.textContent = data.count > 99 ? '99+' : data.count;
                                badge.classList.remove('d-none');
                                icon.classList.remove('bi-chat-dots');
                                icon.classList.add('bi-chat-dots-fill');
                            } else {
                                console.log('Hiding badge for order', orderId);
                                badge.classList.add('d-none');
                                icon.classList.remove('bi-chat-dots-fill');
                                icon.classList.add('bi-chat-dots');
                            }
                        } else {
                            console.log('Badge or icon elements not found for order:', orderId);
                        }
                    })
                    .catch(error => {
                        console.error('Error checking messages for order:', orderId, error);
                    });
            });
        }

        // Check messages initially and every 15 seconds
        console.log('Initializing chat notification system...');
        checkUnreadMessagesPerOrder();
        setInterval(checkUnreadMessagesPerOrder, 15000);
    });
</script>
@endsection
