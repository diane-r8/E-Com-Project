<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Crafts N' Wraps</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Icons & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    
    <!-- Additional seller dashboard styles -->
    @if(auth()->check() && auth()->user()->user_type === 'seller')
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .seller-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 260px;
            background-color: #BDB76B;
            color: #333;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed {
            width: 70px;
            overflow: hidden;
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: #A9A759;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .logo-container {
            display: block;
            text-align: center;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .sidebar-logo {
            max-width: 150px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .sidebar.collapsed .sidebar-header {
            padding: 20px 10px;
        }
        
        .sidebar.collapsed .sidebar-logo {
            max-width: 40px;
            transform: scale(0.8);
        }
        
        .sidebar-content {
            padding: 15px 0;
        }
        
        .nav-link-sidebar {
            padding: 12px 20px;
            color: #333;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .nav-link-sidebar:hover, .nav-link-sidebar.active {
            background-color: #D6CF9F;
            color: #000;
            text-decoration: none;
        }
        
        .nav-link-sidebar i {
            margin-right: 10px;
            font-size: 20px;
            width: 25px;
            text-align: center;
        }
        
        .sidebar.collapsed .nav-text {
            display: none;
        }
        
        .sidebar-collapse-btn {
            position: absolute;
            right: -15px;
            top: 20px;
            width: 30px;
            height: 30px;
            background-color: #BDB76B;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            cursor: pointer;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
            z-index: 1001;
            transition: all 0.3s ease;
        }
        
        .main-content {
            flex: 1;
            margin-left: 260px;
            transition: all 0.3s ease;
            width: calc(100% - 260px);
        }
        
        .main-content.expanded {
            margin-left: 70px;
            width: calc(100% - 70px);
        }
        
        .seller-navbar {
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding: 12px 25px;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .seller-navbar .navbar-brand img {
            max-height: 40px;
        }
        
        .content-wrapper {
            padding: 25px;
        }
        
        .notification-icon {
            font-size: 22px;
            color: #6c757d;
            position: relative;
            cursor: pointer;
            margin-right: 25px;
        }
        
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 10px;
            padding: 2px 5px;
        }
        
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }
        
        .user-dropdown .dropdown-toggle {
            cursor: pointer;
        }
        
        .user-dropdown .dropdown-menu {
            min-width: 200px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
            border-radius: 8px;
            right: 0;
            left: auto;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
            
            .sidebar-header {
                padding: 20px 10px;
            }
            
            .sidebar-logo {
                max-width: 40px;
            }
            
            .nav-text {
                display: none;
            }
            
            .sidebar-collapse-btn {
                display: none;
            }
        }
    </style>
    @endif
</head>
<body>
    @if(auth()->check() && auth()->user()->user_type === 'seller')
        <!-- Seller Layout -->
        <div class="seller-layout">
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <div class="sidebar-header">
                    <a href="{{ route('seller.dashboard') }}" class="logo-container">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="img-fluid sidebar-logo" style="max-width: 150px;">
                    </a>
                    <div class="sidebar-collapse-btn" id="sidebarCollapseBtn">
                        <i class="bi bi-chevron-left"></i>
                    </div>
                </div>
                <div class="sidebar-content">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('seller.dashboard') }}" class="nav-link-sidebar {{ Request::is('seller/dashboard') ? 'active' : '' }}">
                                <i class="bi bi-speedometer2"></i>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('seller.products') }}" class="nav-link-sidebar {{ Request::is('seller/products*') ? 'active' : '' }}">
                                <i class="bi bi-box-seam"></i>
                                <span class="nav-text">Products</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('seller.order_management') }}" class="nav-link-sidebar {{ Request::is('seller/order*') ? 'active' : '' }}">
                                <i class="bi bi-cart-check"></i>
                                <span class="nav-text">Orders</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('seller.chat') }}" class="nav-link-sidebar {{ Request::is('seller/chat*') ? 'active' : '' }}">
                                <i class="bi bi-chat-dots"></i>
                                <span class="nav-text">Messages</span>
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a href="{{ route('user.profile') }}" class="nav-link-sidebar {{ Request::is('user/profile*') ? 'active' : '' }}">
                                <i class="bi bi-person-circle"></i>
                                <span class="nav-text">Profile</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('logout') }}" class="nav-link-sidebar" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i>
                                <span class="nav-text">Logout</span>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content" id="mainContent">
                <!-- Top Navbar -->
                <nav class="seller-navbar d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ ucfirst(request()->segment(2) ?? 'Dashboard') }}</h5>
                    </div>
                    <div class="d-flex align-items-center">
                        <!-- Order Notifications -->
                        <a href="{{ route('seller.order_management') }}" class="notification-icon" id="order-notification-icon">
                            <i class="bi bi-bell"></i>
                            <span id="order-notification-badge" class="notification-badge badge rounded-pill bg-danger d-none">0</span>
                        </a>
                        
                        <!-- Chat Notifications -->
                        <a href="{{ route('seller.chat') }}" class="notification-icon position-relative">
                            <i class="bi bi-chat-dots" id="chat-icon"></i>
                            <span id="chat-notification-badge" class="notification-badge badge rounded-pill bg-danger d-none">0</span>
                        </a>
                        
                        <!-- User Dropdown -->
                        <div class="user-dropdown dropdown">
                            <div class="d-flex align-items-center" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-info d-none d-md-block me-2 text-end">
                                    <p class="mb-0 text-dark">{{ auth()->user()->name }}</p>
                                    <small class="text-muted">Seller</small>
                                </div>
                                <div class="avatar">
                                    <i class="bi bi-person-circle fs-3"></i>
                                </div>
                            </div>
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('user.profile') }}"><i class="bi bi-person me-2"></i> My Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}" 
                                       onclick="event.preventDefault(); document.getElementById('logout-form-dropdown').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </a>
                                    <form id="logout-form-dropdown" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="content-wrapper">
                    @yield('content')
                </div>
            </div>
        </div>
    @else
        <!-- Regular User Layout -->
        @include('partials._navbar')

        <!-- Main Content -->
        <div class="container mt-4">
            @yield('content')
        </div>
        
        <!-- Footer -->
        @include('partials._footer')
    @endif

    <!-- jQuery (Optional but recommended for debugging) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/custom.js') }}"></script>
    
    @if(auth()->check() && auth()->user()->user_type === 'seller')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar collapse functionality
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
            
            if (sidebarCollapseBtn) {
            sidebarCollapseBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                    this.querySelector('i').classList.toggle('bi-chevron-right');
                    this.querySelector('i').classList.toggle('bi-chevron-left');
                });
                }
            
            // Check for unread messages
            function checkUnreadMessages() {
                fetch('{{ route("seller.chat.unread-count") }}')
                    .then(response => response.json())
                    .then(data => {
                        const unreadCount = data.count;
                        const chatBadge = document.getElementById('chat-notification-badge');
                        const chatIcon = document.getElementById('chat-icon');
                        
                        if (unreadCount > 0) {
                            chatBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                            chatBadge.classList.remove('d-none');
                            chatIcon.classList.remove('bi-chat-dots');
                            chatIcon.classList.add('bi-chat-dots-fill');
                        } else {
                            chatBadge.classList.add('d-none');
                            chatIcon.classList.remove('bi-chat-dots-fill');
                            chatIcon.classList.add('bi-chat-dots');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching unread message count:', error);
                    });
            }
            
            // Check for new orders
            function checkNewOrders() {
                fetch('{{ route("seller.new_orders_count") }}')
                    .then(response => response.json())
                    .then(data => {
                        const orderCount = data.count;
                        const orderBadge = document.getElementById('order-notification-badge');
                        const orderIcon = document.querySelector('#order-notification-icon i.bi-bell');
                        
                        if (orderCount > 0) {
                            orderBadge.textContent = orderCount > 99 ? '99+' : orderCount;
                            orderBadge.classList.remove('d-none');
                            if (orderIcon) {
                                orderIcon.classList.remove('bi-bell');
                                orderIcon.classList.add('bi-bell-fill');
                            }
                        } else {
                            orderBadge.classList.add('d-none');
                            if (orderIcon) {
                                orderIcon.classList.remove('bi-bell-fill');
                                orderIcon.classList.add('bi-bell');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching new orders count:', error);
                    });
            }
            
            // Check for notifications initially and every 15 seconds
            if (document.querySelector('.seller-layout')) {
                checkUnreadMessages();
            checkNewOrders();
                setInterval(checkUnreadMessages, 15000); // Check every 15 seconds
                setInterval(checkNewOrders, 15000);
            }
        });
    </script>
    @endif

    <!-- Allow injecting scripts from other templates -->
    @stack('scripts')
</body>
</html>
