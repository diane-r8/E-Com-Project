<!-- resources/views/partials/_navbar.blade.php -->
<nav class="navbar navbar-expand-lg navbar-light">
    <!-- Logo Holder -->
    <a class="navbar-brand" href="{{ url('/') }}">
        <img src="{{ asset('images/logo.png') }}" alt="Crafts N' Wraps Logo" class="img-fluid" style="max-width: 250px;">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <!-- Navbar items (Home, About, etc.) -->
        <ul class="navbar-nav custom-nav-links ms-auto"> <!-- Align navbar items -->
            <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('/about') }}">About</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('/products') }}">Products</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('/contact') }}">Contact</a></li>
        </ul>
        
        <!-- Search Bar and Cart (Right-aligned) -->
        <div class="d-flex align-items-center ms-auto"> <!-- Separate container for the search and cart -->
            <div class="search-bar me-3">
                <input type="text" class="form-control" placeholder="Search...">
            </div>
            <div class="cart-icon">
                <i class="bi bi-cart3 fs-3"></i>
            </div>
        </div>
    </div>
</nav>
