@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Order Management</h1>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
    <!-- Filter Bar -->
    <div class="mb-3 card p-3">
        <form action="{{ route('seller.order_management') }}" method="GET" class="row">
            <div class="col-md-3">
                <label for="status">Filter by Status</label>
                <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="search">Search by Order ID or Customer</label>
                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Order ID or Customer Name">
            </div>
            <div class="col-md-3">
                <label for="date_from">Date From</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to">Date To</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-12 mt-3 text-end">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="{{ route('seller.order_management') }}" class="btn btn-secondary">Clear Filters</a>
            </div>
        </form>
    </div>
    
    <!-- Bulk Actions -->
    <div class="mb-3">
        <form id="bulk-action-form" action="{{ route('seller.bulk_update_order_status') }}" method="POST">
            @csrf
            <div class="input-group">
                <select name="status" class="form-select" style="max-width: 200px;">
                    <option value="">Bulk Update Status</option>
                    <option value="pending">Mark as Pending</option>
                    <option value="processing">Mark as Processing</option>
                    <option value="shipped">Mark as Shipped</option>
                    <option value="delivered">Mark as Delivered</option>
                    <option value="cancelled">Mark as Cancelled</option>
                </select>
                <button type="button" onclick="submitBulkAction()" class="btn btn-primary">Apply to Selected</button>
            </div>
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="table-responsive">
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Order Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="order-row" data-order-id="{{ $order->id }}">
                    <td onclick="event.stopPropagation();">
                        <input type="checkbox" class="order-checkbox" value="{{ $order->id }}">
                    </td>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->full_name }}</td>
                    <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
                    <td>₱{{ number_format($order->total_price, 2) }}</td>
                    <td>
                        <span id="status-badge-{{ $order->id }}" class="badge 
                            @if($order->status == 'pending') bg-warning 
                            @elseif($order->status == 'processing') bg-info 
                            @elseif($order->status == 'shipped') bg-primary 
                            @elseif($order->status == 'delivered') bg-success 
                            @elseif($order->status == 'cancelled') bg-danger 
                            @else bg-secondary @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge 
                            @if($order->payment_status == 'paid') bg-success 
                            @elseif($order->payment_status == 'pending') bg-warning 
                            @elseif($order->payment_status == 'failed') bg-danger 
                            @else bg-secondary @endif">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td onclick="event.stopPropagation();">
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Update Status
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus({{ $order->id }}, 'pending')">Pending</a></li>
                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus({{ $order->id }}, 'processing')">Processing</a></li>
                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus({{ $order->id }}, 'shipped')">Shipped</a></li>
                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus({{ $order->id }}, 'delivered')">Delivered</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="updateOrderStatus({{ $order->id }}, 'cancelled')">Cancelled</a></li>
                            </ul>
                        </div>
                        <a href="{{ route('seller.view_order', $order->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('seller.generate_invoice', $order->id) }}" class="btn btn-secondary btn-sm">Invoice</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const orderCheckboxes = document.querySelectorAll('.order-checkbox');
        
        orderCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }

    function updateOrderStatus(orderId, status) {
        if (confirm('Are you sure you want to update the status to ' + status + '?')) {
            fetch('/seller/order/update-status/' + orderId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const statusBadge = document.getElementById('status-badge-' + orderId);
                    statusBadge.innerHTML = status.charAt(0).toUpperCase() + status.slice(1);
                    statusBadge.className = 'badge';
                    if (status === 'pending') statusBadge.classList.add('bg-warning');
                    else if (status === 'processing') statusBadge.classList.add('bg-info');
                    else if (status === 'shipped') statusBadge.classList.add('bg-primary');
                    else if (status === 'delivered') statusBadge.classList.add('bg-success');
                    else if (status === 'cancelled') statusBadge.classList.add('bg-danger');
                    else statusBadge.classList.add('bg-secondary');

                    const container = document.querySelector('.container');
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.innerHTML = 'Order status updated successfully. <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    container.insertBefore(alertDiv, container.firstChild);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update order status. Please try again.');
            });
        }
    }

    function submitBulkAction() {
        const selectedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
        const bulkActionForm = document.getElementById('bulk-action-form');
        const statusSelect = bulkActionForm.querySelector('select[name="status"]');
        
        if (selectedCheckboxes.length === 0) {
            alert('Please select at least one order.');
            return;
        }
        
        if (statusSelect.value === '') {
            alert('Please select a status to apply.');
            return;
        }
        
        if (confirm('Are you sure you want to update the status of ' + selectedCheckboxes.length + ' order(s)?')) {
            document.querySelectorAll('input[name="order_ids[]"]').forEach(el => el.remove());
            selectedCheckboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'order_ids[]';
                input.value = checkbox.value;
                bulkActionForm.appendChild(input);
            });
            bulkActionForm.submit();
        }
    }
</script>
@endsection
