<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function createPayment(Order $order)
    {
        \Log::info('Creating payment for order', ['order_id' => $order->id, 'total_price' => $order->total_price]);
        
        // Get Xendit API key from environment variables
        $xenditApiKey = env('XENDIT_API_KEY');

        // Add fallbacks
        if (!$xenditApiKey) {
            // Try $_ENV and $_SERVER as fallbacks
            $xenditApiKey = $_ENV['XENDIT_API_KEY'] ?? null;
            if (!$xenditApiKey) {
                $xenditApiKey = $_SERVER['XENDIT_API_KEY'] ?? null;
            }
        }

        \Log::info('Xendit API key access check', ['key_exists' => !empty($xenditApiKey), 'key_length' => strlen($xenditApiKey ?? '')]);
        
        if (!$xenditApiKey) {
            \Log::error('Xendit API key not found in environment variables');
            return redirect()->back()->with('error', 'Payment gateway configuration error. Please try again later or choose a different payment method.');
        }
        
        // Create a unique external ID for this payment
        $externalId = 'order_' . $order->id . '_' . Str::random(8);
        \Log::info('Generated external ID', ['external_id' => $externalId]);
        
        // Set up the payment request body
        $payload = [
            'external_id' => $externalId,
            'amount' => $order->total_price,
            'description' => 'Payment for Order #' . $order->id,
            'invoice_duration' => 86400, // 24 hours
            'currency' => 'PHP',
            // Removed reminder_time parameter
            'success_redirect_url' => route('payment.callback', ['order_id' => $order->id, 'status' => 'success']),
            'failure_redirect_url' => route('payment.callback', ['order_id' => $order->id, 'status' => 'failure']),
            'payment_methods' => ['GCASH']
        ];
        
        \Log::info('Payment payload prepared', ['payload' => $payload]);
        
        // Save payment info to database if you have a Payment model
        if (class_exists('App\Models\Payment')) {
            try {
                // Save payment info to database
                $payment = new Payment();
                $payment->order_id = $order->id;
                $payment->payment_method = 'GCash'; // Use 'GCash' instead of 'gcash' to match your enum

                // Only set status if the column exists
                if (Schema::hasColumn('payments', 'status')) {
                    $payment->status = 'pending';
                }

                // Only set external_id if the column exists
                if (Schema::hasColumn('payments', 'external_id')) {
                    $payment->external_id = $externalId;
                }

                $payment->save();
                \Log::info('Payment record created', ['payment_id' => $payment->id]);
            } catch (\Exception $e) {
                \Log::error('Error creating payment record', ['error' => $e->getMessage()]);
                // Continue anyway - failing to create a payment record shouldn't stop the actual payment
            }
        }
        
        \Log::info('Making API call to Xendit');
        
        // Call Xendit API to create invoice
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.xendit.co/v2/invoices');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode($xenditApiKey . ':'),
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Check for cURL errors
            if (curl_errno($ch)) {
                $errorMsg = curl_error($ch);
                \Log::error('cURL error in Xendit API call', ['error' => $errorMsg]);
                curl_close($ch);
                return redirect()->back()->with('error', 'Error connecting to payment gateway: ' . $errorMsg);
            }
            
            curl_close($ch);
            
            \Log::info('Xendit API response received', ['http_code' => $httpCode, 'response' => $response]);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                $responseData = json_decode($response, true);
                \Log::info('Xendit API response decoded', ['data' => $responseData]);
                
                // Update order with payment ID if needed
                if (Schema::hasColumn('orders', 'payment_id')) {
                    $order->payment_id = $responseData['id'] ?? null;
                    $order->save();
                    \Log::info('Updated order with payment ID', ['order_id' => $order->id, 'payment_id' => $responseData['id'] ?? null]);
                }
                
                // Redirect to Xendit checkout URL
                if (isset($responseData['invoice_url'])) {
                    \Log::info('Redirecting to Xendit payment page', ['url' => $responseData['invoice_url']]);
                    
                    // Use away() method for external URLs and immediately return
                    return redirect()->away($responseData['invoice_url']);
                } else {
                    \Log::error('No invoice_url in Xendit response', ['response' => $responseData]);
                    return redirect()->back()->with('error', 'Payment gateway error: No checkout URL provided');
                }
            } else {
                \Log::error('Xendit API error response', ['http_code' => $httpCode, 'response' => $response]);
                $errorData = json_decode($response, true);
                $errorMessage = isset($errorData['message']) ? $errorData['message'] : 'Unknown error';
                return redirect()->back()->with('error', 'Payment gateway error: ' . $errorMessage);
            }
        } catch (\Exception $e) {
            \Log::error('Exception during Xendit API call', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Exception during payment processing: ' . $e->getMessage());
        }
        
        // If we get here, something went wrong
        \Log::error('Reached end of createPayment method without returning - this should not happen');
        return redirect()->route('checkout')->with('error', 'Unable to process payment at this time. Please try again later.');
    }

    public function process(Request $request, $order_id)
    {
        \Log::info('Payment process called', ['order_id' => $order_id]);
        
        try {
            $order = Order::findOrFail($order_id);
            return $this->createPayment($order);
        } catch (\Exception $e) {
            \Log::error('Error in process method', ['error' => $e->getMessage()]);
            return redirect()->route('checkout')->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }
    
    public function handleCallback(Request $request)
    {
        $orderId = $request->order_id;
        $status = $request->status;
        
        \Log::info('Payment callback received', ['order_id' => $orderId, 'status' => $status]);
        
        try {
            $order = Order::findOrFail($orderId);
            
            if ($status === 'success') {
                \Log::info('Payment successful for order', ['order_id' => $orderId]);
                
                if (Schema::hasColumn('orders', 'payment_status')) {
                    $order->payment_status = 'Paid';
                    $order->save();
                    \Log::info('Updated order payment status to Paid', ['order_id' => $orderId]);
                }
                
                return redirect()->route('order.success', ['order_id' => $orderId])
                                ->with('success', 'Your payment was successful! Thank you for your order.');
            } else {
                \Log::info('Payment failed for order', ['order_id' => $orderId]);
                
                if (Schema::hasColumn('orders', 'payment_status')) {
                    $order->payment_status = 'Failed';
                    $order->save();
                    \Log::info('Updated order payment status to Failed', ['order_id' => $orderId]);
                }
                
                return redirect()->route('order.success', ['order_id' => $orderId])
                                ->with('error', 'Payment was not completed. Please try again or contact our support team.');
            }
        } catch (\Exception $e) {
            \Log::error('Error in handleCallback', ['error' => $e->getMessage(), 'order_id' => $orderId]);
            return redirect()->route('home')->with('error', 'Error processing payment callback: ' . $e->getMessage());
        }
    }

    /**
     * Process payment for an order
     */
    public function processPayment($orderId)
    {
        \Log::info('processPayment called', ['order_id' => $orderId]);
        
        try {
            $order = Order::findOrFail($orderId);
            \Log::info('Order found, proceeding with payment', ['order_id' => $orderId, 'total_price' => $order->total_price]);
            
            // Call the payment gateway (Xendit)
            return $this->createPayment($order);
            
        } catch (\Exception $e) {
            \Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('order.success', ['order_id' => $orderId])
                            ->with('warning', 'Order created but payment processing had an issue: ' . $e->getMessage());
        }
    }
}