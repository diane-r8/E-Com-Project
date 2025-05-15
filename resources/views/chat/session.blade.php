@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Chat Info Sidebar -->
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chat Information</h5>
                </div>
                <div class="card-body">
                    <h6>Customer</h6>
                    <p>{{ $session->customer->name }}</p>
                    
                    <h6>Email</h6>
                    <p>{{ $session->customer->email }}</p>
                    
                    <h6>Started</h6>
                    <p>{{ $session->created_at->format('M d, Y g:i A') }}</p>
                    
                    <h6>Status</h6>
                    <p><span class="badge {{ $session->status == 'open' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($session->status) }}</span></p>
                    
                    @if($session->order)
                    <hr>
                    <h6>Related Order</h6>
                    <p class="mb-1">
                        <a href="{{ route('seller.view_order', $session->order->id) }}" class="text-primary">
                            <i class="bi bi-box"></i> Order #{{ $session->order->id }}
                        </a>
                    </p>
                    <p class="mb-1 small">
                        <span class="badge 
                            @if($session->order->status == 'pending') bg-warning 
                            @elseif($session->order->status == 'processing') bg-info 
                            @elseif($session->order->status == 'shipped') bg-primary 
                            @elseif($session->order->status == 'delivered') bg-success 
                            @elseif($session->order->status == 'cancelled') bg-danger 
                            @else bg-secondary @endif">
                            {{ ucfirst($session->order->status) }}
                        </span>
                    </p>
                    <p class="small text-muted">
                        {{ $session->order->created_at->format('M d, Y') }}
                    </p>
                    <p class="mb-0">
                        <strong>₱{{ number_format($session->order->total_price, 2) }}</strong>
                    </p>
                    @endif
                    
                    <form action="{{ route('seller.chat.close', $session->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to close this chat session?');">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm w-100 mt-3">Close Chat</button>
                    </form>
                    
                    <a href="{{ route('seller.chat') }}" class="btn btn-secondary btn-sm w-100 mt-2">Back to Chat List</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Quick Responses</h5>
                </div>
                <div class="card-body">
                    <div class="quick-responses">
                        <button class="btn btn-outline-secondary mb-2 w-100" onclick="insertQuickResponse('Thank you for contacting us! How can I help you today?')">Welcome</button>
                        <button class="btn btn-outline-secondary mb-2 w-100" onclick="insertQuickResponse('I\'ll check that for you right away.')">Checking</button>
                        <button class="btn btn-outline-secondary mb-2 w-100" onclick="insertQuickResponse('Is there anything else I can help you with today?')">Anything else</button>
                        <button class="btn btn-outline-secondary mb-2 w-100" onclick="insertQuickResponse('Thank you for your patience.')">Thank for patience</button>
                        <button class="btn btn-outline-secondary mb-2 w-100" onclick="insertQuickResponse('Thank you for chatting with us today. Have a great day!')">Closing</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chat Messages -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Chat with {{ $session->customer->name }}</h5>
                    <span id="typing-indicator" class="text-light small d-none">Customer is typing...</span>
                </div>
                <div class="card-body">
                    <div class="chat-container" id="chat-messages" style="height: 500px; overflow-y: auto; padding: 15px;">
                        @foreach($messages as $message)
                            <div class="message-container mb-3 {{ $message->user_id == Auth::id() ? 'text-end' : 'text-start' }} {{ $message->is_system_message ? 'text-center' : '' }}">
                                <div class="message-bubble p-2 rounded d-inline-block 
                                    {{ $message->is_system_message ? 'bg-light text-muted' : ($message->user_id == Auth::id() ? 'bg-primary text-white' : 'bg-light') }}"
                                    style="max-width: {{ $message->is_system_message ? '90%' : '80%' }}; word-break: break-word;">
                                    
                                    @if(!$message->is_system_message)
                                    <div class="message-sender small {{ $message->user_id == Auth::id() ? 'text-light' : 'text-muted' }}">
                                        {{ $message->user_id == Auth::id() ? 'You' : $session->customer->name }}
                                    </div>
                                    @endif
                                    
                                    <div class="message-content">{{ $message->message }}</div>
                                    
                                    @if(!$message->is_system_message)
                                    <div class="message-time small {{ $message->user_id == Auth::id() ? 'text-light' : 'text-muted' }}">
                                        {{ $message->created_at->format('g:i A') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <form id="chat-form" class="mt-3">
                        <div class="input-group">
                            <input type="text" id="message-input" class="form-control" placeholder="Type your message..." required>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatContainer = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');
        
        // Scroll to bottom of chat
        function scrollToBottom() {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
        
        // Scroll to bottom initially
        scrollToBottom();
        
        // Handle form submission
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const message = messageInput.value.trim();
            if (!message) return;
            
            // Add seller message to UI immediately
            addMessage(message, true);
            
            // Clear input field
            messageInput.value = '';
            
            // Send message to server
            fetch('{{ route('chat.send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    session_id: {{ $session->id }},
                    message: message
                })
            })
            .then(response => response.json())
            .catch(error => {
                console.error('Error:', error);
            });
        });
        
        // Add a message to the UI
        function addMessage(messageText, isUser, isSystem = false) {
            const messageContainer = document.createElement('div');
            
            if (isSystem) {
                messageContainer.className = 'message-container mb-3 text-center';
            } else {
                messageContainer.className = `message-container mb-3 ${isUser ? 'text-end' : 'text-start'}`;
            }
            
            const messageBubble = document.createElement('div');
            
            if (isSystem) {
                messageBubble.className = 'message-bubble p-2 rounded d-inline-block bg-light text-muted';
                messageBubble.style.maxWidth = '90%';
            } else {
                messageBubble.className = `message-bubble p-2 rounded d-inline-block ${isUser ? 'bg-primary text-white' : 'bg-light'}`;
                messageBubble.style.maxWidth = '80%';
            }
            
            messageBubble.style.wordBreak = 'break-word';
            
            if (!isSystem) {
                const messageSender = document.createElement('div');
                messageSender.className = `message-sender small ${isUser ? 'text-light' : 'text-muted'}`;
                messageSender.textContent = isUser ? 'You' : '{{ $session->customer->name }}';
                messageBubble.appendChild(messageSender);
            }
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            messageContent.textContent = messageText;
            messageBubble.appendChild(messageContent);
            
            if (!isSystem) {
                const messageTime = document.createElement('div');
                messageTime.className = `message-time small ${isUser ? 'text-light' : 'text-muted'}`;
                
                const now = new Date();
                const hours = now.getHours() % 12 || 12;
                const minutes = now.getMinutes().toString().padStart(2, '0');
                const ampm = now.getHours() >= 12 ? 'PM' : 'AM';
                messageTime.textContent = `${hours}:${minutes} ${ampm}`;
                messageBubble.appendChild(messageTime);
            }
            
            messageContainer.appendChild(messageBubble);
            chatContainer.appendChild(messageContainer);
            
            scrollToBottom();
        }
        
        // Insert quick response into message input
        window.insertQuickResponse = function(text) {
            messageInput.value = text;
            messageInput.focus();
        }
        
        // Function to check for new messages periodically
        function pollMessages() {
            // Get timestamp of the most recent message
            const lastMessageTime = '{{ $messages->count() > 0 ? $messages->last()->created_at->timestamp : now()->timestamp }}';
            
            // Make an AJAX request to check for new messages
            fetch(`/chat/check-messages?session_id={{ $session->id }}&last_time=${lastMessageTime}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages && data.messages.length > 0) {
                    // Add each new message to the UI
                    data.messages.forEach(message => {
                        const isUser = message.user_id == {{ Auth::id() }};
                        const isSystem = message.is_system_message;
                        addMessage(message.message, isUser, isSystem);
                    });
                }
            })
            .catch(error => {
                console.error('Error checking for new messages:', error);
            });
        }
        
        // Set up polling for new messages (every 3 seconds)
        setInterval(pollMessages, 3000);
    });
</script>
@endpush
@endsection 