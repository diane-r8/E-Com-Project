<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Show customer chat interface
     */
    public function customerChat(Request $request)
    {
        // Get the authenticated user (customer)
        $user = Auth::user();
        $orderId = $request->order_id;
        $order = null;
        
        // If an order ID is specified, find it and verify it belongs to the customer
        if ($orderId) {
            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();
                
            if (!$order) {
                return redirect()->back()->with('error', 'Order not found or does not belong to you.');
            }
        }
        
        // Find existing active chat sessions for this customer
        $chatSession = ChatSession::where('customer_id', $user->id)
            ->where('is_active', true)
            ->when($orderId, function($query) use ($orderId) {
                return $query->where('order_id', $orderId);
            })
            ->first();
        
        // If no active session exists, create a new one in open status
        if (!$chatSession) {
            $chatSession = ChatSession::create([
                'customer_id' => $user->id,
                'status' => 'open',
                'order_id' => $orderId,
                'title' => $orderId ? 'Chat about Order #' . $orderId : 'Chat with Seller',
                'last_message_at' => now(),
            ]);
            
            // Add initial welcome message from system
            ChatMessage::create([
                'chat_session_id' => $chatSession->id,
                'user_id' => $user->id, // We're using the customer's ID but marking it as a system message
                'message' => $orderId 
                    ? "You've started a chat about Order #$orderId. A seller will respond shortly." 
                    : "You've started a new chat. A seller will respond shortly.",
                'is_bot_message' => false,
                'is_system_message' => true,
            ]);
        }
        
        // Get chat messages for this session
        $messages = $chatSession->messages;
        
        return view('chat.customer', compact('chatSession', 'messages', 'order'));
    }
    
    /**
     * Create a new chat about an order
     */
    public function createOrderChat($orderId)
    {
        $user = Auth::user();
        
        // Verify the order exists and belongs to the user
        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$order) {
            return redirect()->back()->with('error', 'Order not found or does not belong to you.');
        }
        
        // Check if an active chat for this order already exists
        $existingChat = ChatSession::where('customer_id', $user->id)
            ->where('is_active', true)
            ->where('order_id', $orderId)
            ->first();
            
        if ($existingChat) {
            return redirect()->route('customer.chat', ['order_id' => $orderId]);
        }
        
        // Create a new chat session in open status
        $chatSession = ChatSession::create([
            'customer_id' => $user->id,
            'status' => 'open',
            'order_id' => $orderId,
            'title' => 'Chat about Order #' . $orderId,
            'last_message_at' => now(),
        ]);
        
        // Add initial system message
        ChatMessage::create([
            'chat_session_id' => $chatSession->id,
            'user_id' => $user->id,
            'message' => "You've started a chat about Order #$orderId. A seller will respond shortly.",
            'is_bot_message' => false,
            'is_system_message' => true,
        ]);
        
        return redirect()->route('customer.chat', ['order_id' => $orderId]);
    }
    
    /**
     * Show seller chat dashboard
     */
    public function sellerChat()
    {
        // Get the authenticated user (seller)
        $seller = Auth::user();
        
        // Get all active chat sessions assigned to this seller
        $assignedSessions = ChatSession::where('seller_id', $seller->id)
            ->where('is_active', true)
            ->orderBy('last_message_at', 'desc')
            ->get();
        
        // Get unassigned active chat sessions
        $unassignedSessions = ChatSession::whereNull('seller_id')
            ->where('is_active', true)
            ->where('status', '!=', 'bot') // Don't show sessions still in bot mode
            ->orderBy('last_message_at', 'desc')
            ->get();
            
        // Count unread messages
        $unreadCount = ChatMessage::whereHas('session', function($query) use ($seller) {
                $query->where('seller_id', $seller->id)
                    ->where('is_active', true);
            })
            ->where('is_read', false)
            ->where('user_id', '!=', $seller->id)
            ->count();
        
        return view('chat.seller', compact('assignedSessions', 'unassignedSessions', 'unreadCount'));
    }
    
    /**
     * Get unread message count for a seller
     */
    public function getUnreadCount()
    {
        $seller = Auth::user();
        
        if (!$seller) {
            return response()->json(['count' => 0]);
        }
        
        $unreadCount = ChatMessage::whereHas('session', function($query) use ($seller) {
                $query->where('seller_id', $seller->id)
                    ->where('is_active', true);
            })
            ->where('is_read', false)
            ->where('user_id', '!=', $seller->id)
            ->count();
            
        return response()->json(['count' => $unreadCount]);
    }
    
    /**
     * Show specific chat session for seller
     */
    public function viewSession($sessionId)
    {
        $seller = Auth::user();
        $session = ChatSession::with('order')->findOrFail($sessionId);
        $messages = $session->messages;
        
        // If this session is unassigned, assign it to the current seller
        if (!$session->seller_id) {
            $session->seller_id = $seller->id;
            $session->status = 'open'; // Change from bot mode if necessary
            $session->save();
        }
        
        // Mark all unread messages as read
        ChatMessage::where('chat_session_id', $sessionId)
            ->where('is_read', false)
            ->where('user_id', '!=', $seller->id)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now()
            ]);
        
        return view('chat.session', compact('session', 'messages'));
    }
    
    /**
     * Send a message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:chat_sessions,id',
            'message' => 'required|string',
        ]);
        
        $user = Auth::user();
        $sessionId = $request->session_id;
        $session = ChatSession::findOrFail($sessionId);
        
        // Security check - only the customer or assigned seller can send messages
        if ($user->id !== $session->customer_id && $user->id !== $session->seller_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Standard message handling
        $message = ChatMessage::create([
            'chat_session_id' => $sessionId,
            'user_id' => $user->id,
            'message' => $request->message,
            'is_read' => false,
        ]);
        
        // If this is a customer message and no seller is assigned, notify available sellers
        if ($user->id === $session->customer_id && !$session->seller_id) {
            // Make sure the session is in open status
            if ($session->status !== 'open') {
                $session->status = 'open';
            }
        }
        
        // Update session last message time
        $session->last_message_at = now();
        $session->save();
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
    
    /**
     * Close a chat session
     */
    public function closeSession($sessionId)
    {
        $user = Auth::user();
        $session = ChatSession::findOrFail($sessionId);
        
        // Security check - only the assigned seller can close a session
        if ($user->id !== $session->seller_id) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $session->is_active = false;
        $session->status = 'closed';
        $session->save();
        
        return redirect()->route('seller.chat')->with('success', 'Chat session closed successfully');
    }
    
    /**
     * Generate bot response based on customer message
     */
    private function generateBotResponse($message, $orderId = null)
    {
        // Lowercase the message for easier keyword matching
        $lowercaseMessage = strtolower($message);
        
        // If this is an order-specific chat, add some order-specific responses
        if ($orderId) {
            if (str_contains($lowercaseMessage, 'status') || str_contains($lowercaseMessage, 'where')) {
                $order = Order::find($orderId);
                if ($order) {
                    return "Your order #$orderId is currently " . strtolower($order->status) . ". Would you like to know more details or speak with a representative?";
                }
            }
            
            if (str_contains($lowercaseMessage, 'cancel')) {
                return "If you'd like to cancel your order #$orderId, please note that cancellation is only possible if the order is still in 'pending' status. Would you like to speak with a representative to discuss cancellation options?";
            }
            
            if (str_contains($lowercaseMessage, 'change') || str_contains($lowercaseMessage, 'modify')) {
                return "To modify order #$orderId, you'll need to speak with a customer service representative. Would you like me to connect you now?";
            }
        }
        
        // Basic keyword matching for common questions
        if (str_contains($lowercaseMessage, 'shipping') || str_contains($lowercaseMessage, 'delivery')) {
            return "We typically ship orders within 1-2 business days. Delivery time depends on your location, usually 3-5 days within the country. Would you like to speak with a representative for more specific information?";
        }
        
        if (str_contains($lowercaseMessage, 'return') || str_contains($lowercaseMessage, 'refund')) {
            return "We have a 30-day return policy. Items must be in original condition with tags attached. For more details or to initiate a return, would you like to speak with a customer service representative?";
        }
        
        if (str_contains($lowercaseMessage, 'payment') || str_contains($lowercaseMessage, 'pay')) {
            return "We accept credit cards, PayPal, and bank transfers. All payments are secure and encrypted. Do you have specific questions about payment methods?";
        }
        
        if (str_contains($lowercaseMessage, 'order status') || str_contains($lowercaseMessage, 'track')) {
            return "You can track your order status in your account dashboard under 'Orders'. Would you like me to connect you with a representative to check a specific order?";
        }
        
        if (str_contains($lowercaseMessage, 'speak') || str_contains($lowercaseMessage, 'human') || str_contains($lowercaseMessage, 'representative') || str_contains($lowercaseMessage, 'agent')) {
            return "I'll connect you with a customer service representative shortly. Please wait a moment while I transfer your chat.";
        }
        
        // More diverse responses for unrecognized inputs
        $defaultResponses = [
            "I'm not sure I understand completely. Would you like to speak with a seller for more personalized assistance?",
            "That's a good question. To better assist you, I'll need to connect you with one of our sellers. Would you like me to do that?",
            "I'd be happy to help with that, but I think a seller would provide better assistance. Shall I connect you?",
            "I'm a simple bot with limited capabilities. For this specific question, a seller would be better equipped to help you. Would you like to chat with a seller?",
            "To ensure you get the best help possible, would you like to speak directly with one of our sellers?"
        ];
        
        // Choose a random response
        return $defaultResponses[array_rand($defaultResponses)];
    }
    
    /**
     * Check for new messages in a chat session
     */
    public function checkMessages(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:chat_sessions,id',
            'last_time' => 'required',
        ]);
        
        $user = Auth::user();
        $sessionId = $request->session_id;
        $lastTime = Carbon::createFromTimestamp($request->last_time);
        
        // Verify that user has access to this chat session
        $session = ChatSession::findOrFail($sessionId);
        if ($user->id !== $session->customer_id && $user->id !== $session->seller_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Get all messages since the last timestamp
        $messages = ChatMessage::where('chat_session_id', $sessionId)
            ->where('created_at', '>', $lastTime)
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Mark messages as read if they're from the other party
        ChatMessage::where('chat_session_id', $sessionId)
            ->where('is_read', false)
            ->where('user_id', '!=', $user->id)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now()
            ]);
        
        return response()->json([
            'success' => true,
            'messages' => $messages,
            'session_status' => $session->status,
            'has_seller' => $session->seller_id !== null
        ]);
    }
    
    /**
     * Get unread message count for a specific order
     */
    public function getUnreadCountByOrder($orderId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['count' => 0]);
        }
        
        $unreadCount = ChatMessage::whereHas('session', function($query) use ($user, $orderId) {
                $query->where('customer_id', $user->id)
                    ->where('order_id', $orderId)
                    ->where('is_active', true);
            })
            ->where('is_read', false)
            ->where('user_id', '!=', $user->id)
            ->count();
            
        return response()->json(['count' => $unreadCount]);
    }
}
