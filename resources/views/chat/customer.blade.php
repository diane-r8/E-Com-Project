@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ $chatSession->title }}</span>
                    <span class="badge bg-info" id="chat-status">{{ ucfirst($chatSession->status) }}</span>
                </div>
                
                @if($order)
                <div class="card-body bg-light border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Order #{{ $order->id }}</h6>
                            <p class="small mb-0">{{ $order->created_at->format('M d, Y') }} • 
                                <span class="badge 
                                    @if($order->status == 'pending') bg-warning 
                                    @elseif($order->status == 'processing') bg-info 
                                    @elseif($order->status == 'shipped') bg-primary 
                                    @elseif($order->status == 'delivered') bg-success 
                                    @elseif($order->status == 'cancelled') bg-danger 
                                    @else bg-secondary @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="text-end">
                            <p class="mb-0"><strong>Total: ₱{{ number_format($order->total_price, 2) }}</strong></p>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="card-body">
                    <div class="chat-container" id="chat-messages" style="height: 400px; overflow-y: auto; padding: 15px;">
                        @foreach($messages as $message)
                            <div class="message-container mb-3 {{ $message->user_id == Auth::id() && !$message->is_bot_message ? 'text-end' : 'text-start' }}">
                                <div class="message-bubble p-2 rounded d-inline-block 
                                    {{ $message->user_id == Auth::id() && !$message->is_bot_message ? 'bg-primary text-white' : 'bg-light' }}
                                    {{ $message->is_bot_message ? 'bot-message bg-warning' : '' }}"
                                    style="max-width: 80%; word-break: break-word;">
                                    <div class="message-content">{{ $message->message }}</div>
                                    <div class="message-time small text-{{ $message->user_id == Auth::id() && !$message->is_bot_message ? 'light' : 'muted' }}">
                                        {{ $message->created_at->format('g:i A') }}
                                        @if($message->is_bot_message)
                                            <span class="badge bg-secondary ms-1">Bot</span>
                                        @endif
                                    </div>
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
        const chatStatus = document.getElementById('chat-status');
        
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
            
            // Add customer message to UI immediately
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
                    session_id: {{ $chatSession->id }},
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // No bot responses to handle anymore
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
        
        // Add a message to the UI
        function addMessage(messageText, isUser, isSystem = false) {
            const messageContainer = document.createElement('div');
            messageContainer.className = `message-container mb-3 ${isUser ? 'text-end' : 'text-start'}`;
            
            const messageBubble = document.createElement('div');
            
            if (isSystem) {
                // System messages are centered with a different style
                messageContainer.className = 'message-container mb-3 text-center';
                messageBubble.className = 'message-bubble p-2 rounded d-inline-block bg-light text-muted';
                messageBubble.style.maxWidth = '90%';
            } else {
                messageBubble.className = `message-bubble p-2 rounded d-inline-block ${isUser ? 'bg-primary text-white' : 'bg-light'}`;
                messageBubble.style.maxWidth = '80%';
            }
            
            messageBubble.style.wordBreak = 'break-word';
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            messageContent.textContent = messageText;
            
            const messageTime = document.createElement('div');
            messageTime.className = `message-time small text-${isUser ? 'light' : 'muted'}`;
            
            const now = new Date();
            const hours = now.getHours() % 12 || 12;
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const ampm = now.getHours() >= 12 ? 'PM' : 'AM';
            messageTime.textContent = `${hours}:${minutes} ${ampm}`;
            
            messageBubble.appendChild(messageContent);
            
            if (!isSystem) {
                messageBubble.appendChild(messageTime);
            }
            
            messageContainer.appendChild(messageBubble);
            chatContainer.appendChild(messageContainer);
            
            scrollToBottom();
        }
        
        // Function to check for new messages periodically
        function pollMessages() {
            // Get timestamp of the most recent message
            const lastMessageTime = '{{ $messages->count() > 0 ? $messages->last()->created_at->timestamp : now()->timestamp }}';
            
            // Make an AJAX request to check for new messages
            fetch(`/chat/check-messages?session_id={{ $chatSession->id }}&last_time=${lastMessageTime}`, {
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
                    
                    // Update status if it has changed
                    if (data.session_status && data.session_status !== chatStatus.textContent.toLowerCase()) {
                        chatStatus.textContent = data.session_status.charAt(0).toUpperCase() + data.session_status.slice(1);
                        
                        // Update status badge color
                        chatStatus.className = 'badge';
                        if (data.session_status === 'open') {
                            chatStatus.classList.add('bg-success');
                        } else if (data.session_status === 'closed') {
                            chatStatus.classList.add('bg-secondary');
                        }
                    }
                    
                    // If session now has a seller when it didn't before, show a notification
                    if (data.has_seller && !window.chatHasSeller) {
                        window.chatHasSeller = true;
                        addMessage("A seller has joined the chat and will respond to your message soon.", false, true);
                    }
                }
            })
            .catch(error => {
                console.error('Error checking for new messages:', error);
            });
        }
        
        // Initialize seller status
        window.chatHasSeller = {{ $chatSession->seller_id ? 'true' : 'false' }};
        
        // Set up polling for new messages (every 5 seconds)
        setInterval(pollMessages, 5000);
    });
</script>
@endpush
@endsection 