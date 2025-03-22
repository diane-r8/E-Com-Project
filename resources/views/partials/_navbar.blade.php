<nav class="navbar navbar-expand-lg navbar-light px-4">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand me-auto" href="{{ url('/') }}">
            <img src="{{ asset('images/logo.png') }}" alt="Crafts N' Wraps Logo" class="img-fluid" style="max-width: 250px;">
        </a>

        <!-- Navbar Toggler (For Mobile View) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Items -->
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav gap-4"> <!-- Even spacing between buttons -->
                @if(auth()->check() && auth()->user()->user_type === 'seller')
                    <!-- Seller Navigation -->
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('seller/dashboard') ? 'active' : '' }}" href="{{ route('seller.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('seller/products') ? 'active' : '' }}" href="{{ route('seller.products') }}">Products</a>
                    </li>
                  
                @else
                    <!-- General Navigation -->
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('about') ? 'active' : '' }}" href="{{ url('/about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('products') ? 'active' : '' }}" href="{{ url('/products') }}">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('contact') ? 'active' : '' }}" href="{{ url('/contact') }}">Contact</a>
                    </li>
                @endif
            </ul>
        </div>

        <!-- Right Section: Search Bar, Cart, Profile -->
        <form action="{{ route('products.search') }}" method="GET">
    <div class="search-container position-relative">
        <input type="text" name="search" class="form-control modern-search ps-5" placeholder="Search..." value="{{ request('search') }}">
        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
    </div>
</form>
            <!-- Cart Icon with Item Count chagned -->
            @auth
            <a href="{{ route('cart') }}" class="cart-icon position-relative text-dark">
                <i class="bi bi-cart3 fs-3"></i>
                <span class="cart-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">    
                    {{ session('cart') ? count(session('cart')) : 0 }}
                </span>
            </a>
            @else
                <!-- Guests: Redirect to login page -->
                <a href="{{ route('login') }}" class="cart-icon position-relative text-dark">
                    <i class="bi bi-cart3 fs-3"></i>
                    <span class="cart-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ session('cart') ? count(session('cart')) : 0 }}
                    </span>
                </a>
            @endauth
            
            <!-- Profile Icon -->
            <div class="profile-icon">
                @auth
                    <a href="{{ route('user.profile') }}" class="text-dark">
                        <i class="bi bi-person-circle fs-3"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-dark">
                        <i class="bi bi-person-circle fs-3"></i>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
