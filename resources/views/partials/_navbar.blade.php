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
                <li class="nav-item"><a class="nav-link" href="{{ route('seller.dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('seller.products') }}">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('seller.about') }}">About</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('seller.contact') }}">Contact</a></li>
            @else
                <!-- General Navigation -->
                <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/about') }}">About</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/products') }}">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/contact') }}">Contact</a></li>
            @endif

            <!--    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/about') }}">About</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/products') }}">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('/contact') }}">Contact</a></li> -->
            </ul>
        </div>

        <!-- Right Section: Search Bar, Cart, Profile -->
        <div class="d-flex align-items-center gap-3">
            <!-- Modern Search Bar -->
            <div class="search-bar position-relative">
                <input type="text" class="form-control modern-search ps-5" placeholder="Search...">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            </div>

            <!-- Cart Icon with Item Count -->
             @auth
            <a href="{{ route('cart.index') }}" class="cart-icon position-relative text-dark">
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
                    <a href="{{ route('user.profile') }}">
                        <i class="bi bi-person-circle fs-3"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}">
                        <i class="bi bi-person-circle fs-3"></i>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
