@extends('layouts.app')

@section('content')

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Seller Dashboard</h1>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('seller.chat') }}" class="btn btn-soft-primary position-relative">
                <i class="bi bi-chat-dots me-1"></i>
                Messages
                <span id="unread-message-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                    0
                </span>
            </a>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted fw-semibold mb-1">Total Sales</h6>
                            <h2 class="mb-0">₱{{ number_format($totalSales ?? 0, 2) }}</h2>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: #E8F0FE;">
                            <i class="bi bi-wallet text-primary fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3 mb-0 text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Total from all completed orders
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted fw-semibold mb-1">Total Orders</h6>
                            <h2 class="mb-0">{{ $totalOrders }}</h2>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: #E0F8E9;">
                            <i class="bi bi-cart-check text-success fs-3"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $processingOrders + $shippedOrders + $deliveredOrders > 0 ? (($processingOrders + $shippedOrders + $deliveredOrders) / $totalOrders * 100) : 0 }}%"></div>
                    </div>
                    <div class="mt-2 mb-0 text-muted small d-flex justify-content-between">
                        <span>
                            <i class="bi bi-circle-fill text-warning me-1 small"></i> Pending: {{ $pendingOrders }}
                        </span>
                        <span>
                            <i class="bi bi-circle-fill text-success me-1 small"></i> Delivered: {{ $deliveredOrders }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted fw-semibold mb-1">Products</h6>
                            <h2 class="mb-0">{{ $totalProducts }}</h2>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: #E0F4FF;">
                            <i class="bi bi-box-seam text-info fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('seller.products') }}" class="btn btn-soft-primary w-100">
                            <i class="bi bi-eye me-1"></i> View Products
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted fw-semibold mb-1">In Cart</h6>
                            <h2 class="mb-0">{{ $totalAddedToCart }}</h2>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: #FFF3CD;">
                            <i class="bi bi-cart-plus text-warning fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3 mb-0 text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Products currently in customer carts
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="filterForm" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="period" class="form-label fw-semibold">Time Period</label>
                            <select id="period" name="period" class="form-select">
                                <option value="7days">Last 7 Days</option>
                                <option value="30days" selected>Last 30 Days</option>
                                <option value="90days">Last 90 Days</option>
                                <option value="ytd">Year to Date</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label fw-semibold">Order Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="all" selected>All Orders</option>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-soft-primary w-100">
                                <i class="bi bi-filter me-1"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Graphs Row -->
    <div class="row g-3 mb-4">
        <!-- Sales Trend Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0">Sales Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesTrendChart" height="300"></canvas>
        </div>
    </div>
</div> 

        <!-- Order Status Pie Chart -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0">Order Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="orderStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Analytics Row -->
    <div class="row g-3 mb-4">
        <!-- Popular Products Bar Chart -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0">Top Selling Products</h5>
                </div>
                <div class="card-body">
                    <canvas id="productsChart" height="250"></canvas>
                </div>
    </div>
</div>

        <!-- Recent Activity -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Orders</h5>
                    <div>
                        <a href="{{ route('seller.reviews') }}" class="btn btn-soft-info me-2">
                            <i class="bi bi-star me-1"></i> Reviews
                        </a>
                        <a href="{{ route('seller.order_management') }}" class="btn btn-soft-primary">
                            <i class="bi bi-list me-1"></i> All Orders
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @if(count($recentOrders) > 0)
                            @foreach($recentOrders as $order)
                                <div class="list-group-item list-group-item-action py-3">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <h6 class="mb-1">Order #{{ $order->id }}</h6>
                                        <span class="badge bg-{{ strtolower($order->status) == 'pending' ? 'warning' : 
                                                              (strtolower($order->status) == 'processing' ? 'info' : 
                                                              (strtolower($order->status) == 'shipped' ? 'primary' : 
                                                              (strtolower($order->status) == 'delivered' ? 'success' : 
                                                              (strtolower($order->status) == 'cancelled' ? 'danger' : 'secondary')))) }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>
                                    <p class="mb-1 text-muted">{{ $order->full_name }} - ₱{{ number_format($order->total_price, 2) }}</p>
                                    <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                </div>
                                    @endforeach
                                @else
                            <div class="list-group-item py-4 text-center">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="mb-0 mt-2">No recent orders</p>
            </div>
        @endif
    </div>
</div>
            </div>
        </div>
        </div>
        
    <!-- Loading overlay -->
    <div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background-color: rgba(255,255,255,0.7); z-index: 1050;">
        <div class="position-absolute top-50 start-50 translate-middle">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>

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

/* Pastel Colors for Status Badges */
.bg-warning { background-color: #FFE5A3 !important; color: #97722A !important; }
.bg-info { background-color: #B6E3FF !important; color: #1A6FB5 !important; }
.bg-primary { background-color: #E8F0FE !important; color: #2361CE !important; }
.bg-success { background-color: #A8E6B4 !important; color: #0F6A1F !important; }
.bg-danger { background-color: #FFB3B3 !important; color: #A12828 !important; }

/* Soft Button Styles */
.btn-soft-primary {
    color: #4e73df;
    background-color: rgba(78, 115, 223, 0.1);
    border: none;
}

.btn-soft-primary:hover {
    color: #fff;
    background-color: #4e73df;
}

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

.btn-soft-warning {
    color: #f6c23e;
    background-color: rgba(246, 194, 62, 0.1);
    border: none;
}

.btn-soft-warning:hover {
    color: #fff;
    background-color: #f6c23e;
}

/* Icon Styles */
.rounded-circle {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn {
    padding: 0.5rem 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.btn i {
    font-size: 1.1rem;
}

.progress-bar {
    background-color: #A8E6B4;
}
</style>

<!-- Add Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize charts with data from controller
    initializeCharts();
    
    // Check for unread messages
    function checkUnreadMessages() {
        fetch('{{ route("seller.chat.unread-count") }}')
            .then(response => response.json())
            .then(data => {
                const unreadBadge = document.getElementById('unread-message-count');
                if (data.count > 0) {
                    unreadBadge.textContent = data.count > 99 ? '99+' : data.count;
                    unreadBadge.classList.remove('d-none');
                } else {
                    unreadBadge.classList.add('d-none');
                }
            })
            .catch(error => console.error('Error checking unread messages:', error));
    }
    
    // Check for unread messages initially and every 30 seconds
    checkUnreadMessages();
    setInterval(checkUnreadMessages, 30000);
    
    // Filter form handling
    const filterForm = document.getElementById('filterForm');
    const loadingOverlay = document.getElementById('loadingOverlay');
    
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            loadingOverlay.classList.remove('d-none');
            
            // Get form data
            const formData = new FormData(filterForm);
            
            // Send AJAX request to get filtered data
            fetch('{{ route("seller.dashboard.filtered-data") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    period: formData.get('period'),
                    status: formData.get('status')
            })
            })
            .then(response => response.json())
            .then(data => {
                // Update charts with new data
                updateCharts(data);
                loadingOverlay.classList.add('d-none');
            })
            .catch(error => {
                console.error('Error fetching filtered data:', error);
                loadingOverlay.classList.add('d-none');
                alert('An error occurred while updating the dashboard. Please try again.');
            });
        });
    }
    
    function initializeCharts() {
        // Sales Trend Chart
        const salesTrendCanvas = document.getElementById('salesTrendChart');
        window.salesTrendChart = new Chart(salesTrendCanvas, {
            type: 'line',
            data: {
                labels: {!! json_encode($dateLabels) !!},
                datasets: [{
                    label: 'Daily Sales (₱)',
                    data: {!! json_encode($salesData) !!},
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.raw.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        },
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#6e707e',
                        bodyColor: '#6e707e',
                        titleFont: {
                            size: 12,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 12
                        },
                        padding: 12,
                        borderColor: 'rgba(78, 115, 223, 0.2)',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString('en-US');
                            },
                            font: {
                                size: 11
                            },
                            padding: 10
                            }
                        }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                elements: {
                    line: {
                        tension: 0.3
                    }
                }
            }
        });
        
        // Order Status Distribution Chart
        const orderStatusCanvas = document.getElementById('orderStatusChart');
        const statusColors = {
            'Pending': '#f6c23e',
            'Processing': '#36b9cc',
            'Shipped': '#4e73df',
            'Delivered': '#1cc88a',
            'Cancelled': '#e74a3b',
            'New': '#4e73df'
        };
        
        const statusLabels = [];
        const statusData = [];
        const statusColors1 = [];
        
        @foreach($orderStatusDistribution as $item)
            statusLabels.push('{{ $item['status'] }}');
            statusData.push({{ $item['count'] }});
            statusColors1.push(statusColors['{{ $item['status'] }}'] || '#858796');
        @endforeach
        
        window.orderStatusChart = new Chart(orderStatusCanvas, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColors1,
                    hoverBackgroundColor: statusColors1,
                    hoverBorderColor: "white",
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw;
                                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        
        // Top Products Chart
        const productsChartCanvas = document.getElementById('productsChart');
        window.productsChart = new Chart(productsChartCanvas, {
            type: 'bar',
            data: {
                labels: {!! json_encode($topProductNames) !!},
                datasets: [{
                    label: 'Units Sold',
                    data: {!! json_encode($topProductSales) !!},
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.7)',
                        'rgba(28, 200, 138, 0.7)',
                        'rgba(246, 194, 62, 0.7)',
                        'rgba(54, 185, 204, 0.7)',
                        'rgba(231, 74, 59, 0.7)'
                    ],
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    function updateCharts(data) {
        // Update Sales Trend Chart
        const salesLabels = data.salesTrend.labels;
        const salesData = data.salesTrend.data;
        
        window.salesTrendChart.data.labels = salesLabels;
        window.salesTrendChart.data.datasets[0].data = salesData;
        window.salesTrendChart.update();
        
        // Update Order Status Distribution Chart
        const statusLabels = [];
        const statusData = [];
        const statusColors = {
            'Pending': '#f6c23e',
            'Processing': '#36b9cc',
            'Shipped': '#4e73df',
            'Delivered': '#1cc88a',
            'Cancelled': '#e74a3b',
            'New': '#4e73df'
        };
        const statusColorsArray = [];
        
        data.orderStatusDistribution.forEach(item => {
            statusLabels.push(item.status);
            statusData.push(item.count);
            statusColorsArray.push(statusColors[item.status] || '#858796');
        });
        
        window.orderStatusChart.data.labels = statusLabels;
        window.orderStatusChart.data.datasets[0].data = statusData;
        window.orderStatusChart.data.datasets[0].backgroundColor = statusColorsArray;
        window.orderStatusChart.data.datasets[0].hoverBackgroundColor = statusColorsArray;
        window.orderStatusChart.update();
        
        // Update Top Products Chart
        window.productsChart.data.labels = data.topProducts.labels;
        window.productsChart.data.datasets[0].data = data.topProducts.data;
        window.productsChart.update();
        
        // Update total sales display
        document.querySelector('.total-sales').textContent = '₱' + data.totalSales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        // Update total orders display
        document.querySelector('.total-orders').textContent = data.totalOrders;
    }
});
</script>

@endsection