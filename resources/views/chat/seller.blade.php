@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Chat Support Dashboard</h1>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="row mt-4">
        <!-- Assigned Chats -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Your Active Chats</h5>
                </div>
                <div class="card-body">
                    @if($assignedSessions->count() > 0)
                        <div class="list-group">
                            @foreach($assignedSessions as $session)
                                <a href="{{ route('seller.chat.session', $session->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">Chat with {{ $session->customer->name }}</h5>
                                        <small>{{ $session->last_message_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">
                                        @if($session->latestMessage)
                                            {{ \Illuminate\Support\Str::limit($session->latestMessage->message, 50) }}
                                        @else
                                            No messages yet
                                        @endif
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            Status: <span class="badge {{ $session->status == 'open' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($session->status) }}</span>
                                            @if($session->order_id)
                                                • Order: <a href="{{ route('seller.view_order', $session->order_id) }}" class="text-primary">#{{ $session->order_id }}</a>
                                            @endif
                                        </small>
                                        
                                        @php
                                            $unreadCount = App\Models\ChatMessage::where('chat_session_id', $session->id)
                                                ->where('is_read', false)
                                                ->where('user_id', '!=', Auth::id())
                                                ->count();
                                        @endphp
                                        
                                        @if($unreadCount > 0)
                                            <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center">No active chats assigned to you</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Unassigned Chats -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Unassigned Customer Chats</h5>
                </div>
                <div class="card-body">
                    @if($unassignedSessions->count() > 0)
                        <div class="list-group">
                            @foreach($unassignedSessions as $session)
                                <a href="{{ route('seller.chat.session', $session->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">Chat with {{ $session->customer->name }}</h5>
                                        <small>{{ $session->last_message_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">
                                        @if($session->latestMessage)
                                            {{ \Illuminate\Support\Str::limit($session->latestMessage->message, 50) }}
                                        @else
                                            No messages yet
                                        @endif
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            Started {{ $session->created_at->diffForHumans() }}
                                            @if($session->order_id)
                                                • Order: <span class="text-primary">#{{ $session->order_id }}</span>
                                            @endif
                                        </small>
                                        <span class="badge bg-warning text-dark">New</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center">No unassigned chats waiting</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Poll for new chats
    function checkForNewChats() {
        // TODO: Implement polling or WebSockets to check for new chats
        // This is a placeholder for real-time updates
    }
    
    // Update every 10 seconds
    setInterval(checkForNewChats, 10000);
</script>
@endpush
@endsection 