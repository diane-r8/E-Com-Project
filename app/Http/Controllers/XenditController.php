<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class XenditController extends Controller
{
    protected $apiKey;
    protected $xenditApiUrl = 'https://api.xendit.co';

    public function __construct()
    {
        // Set your Xendit API key from the environment
        $this->apiKey = env('XENDIT_API_KEY');
        
        if (!$this->apiKey) {
            Log::error('Xendit API key is not set.');
        }
    }

    /**
     * Process payment with GCash via Xendit
     *
     * @param array $data Payment data (can be received directly or from previous controller)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function payWithGCash($data = null)
    {
        // Allow accepting data from request or from another controller
        $paymentData = $data ?? request()->all();
        
        try {
            // Prepare data for Xendit API
            $invoiceData = [
                'external_id' => $paymentData['external_id'] ?? 'order_' . time(),
                'amount' => $paymentData['amount'],
                'description' => $paymentData['description'] ?? 'Payment for order',
                'invoice_email' => $paymentData['invoice_email'] ?? null,
                'customer' => [
                    'given_names' => $paymentData['customer_name'] ?? 'Customer',
                    'email' => $paymentData['invoice_email'] ?? null,
                    'mobile_number' => $paymentData['customer_phone'] ?? null,
                ],
                'success_redirect_url' => $paymentData['success_redirect_url'] ?? route('order.success', ['status' => 'success']),
                'failure_redirect_url' => $paymentData['failure_redirect_url'] ?? route('order.success', ['status' => 'failed']),
                'payment_methods' => ['GCASH'],
                'currency' => 'PHP',
            ];

            // Create the invoice using Xendit API
            $response = Http::withBasicAuth($this->apiKey, '')
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->xenditApiUrl . '/v2/invoices', $invoiceData);

            if ($response->successful()) {
                $invoiceData = $response->json();
                
                // Record the payment transaction in your database
                $this->recordPaymentTransaction($invoiceData);
                
                // Extract order ID from the external_id format "order_123"
                $orderId = str_replace('order_', '', $invoiceData['external_id']);
                
                // Update order with payment details
                $this->updateOrderPayment($orderId, $invoiceData);
                
                // Redirect the user to the Xendit payment page
                return redirect()->away($invoiceData['invoice_url']);
            } else {
                // Handle API error
                Log::error('Xendit API Error: ' . $response->body());
                
                return redirect()->route('order.success', [
                    'order_id' => str_replace('order_', '', $paymentData['external_id']),
                    'status' => 'failed'
                ])->with('error', 'Unable to process payment at this time. Please try again later.');
            }
        } catch (\Exception $e) {
            Log::error('Xendit payment error: ' . $e->getMessage());
            
            return redirect()->route('order.success', [
                'order_id' => str_replace('order_', '', $paymentData['external_id']),
                'status' => 'failed'
            ])->with('error', 'An error occurred while processing your payment.');
        }
    }

    /**
     * Record the payment transaction in the database
     *
     * @param array $invoiceData
     * @return void
     */
    private function recordPaymentTransaction($invoiceData)
    {
        // Extract order ID from the external_id format "order_123"
        $orderId = str_replace('order_', '', $invoiceData['external_id']);
        
        // Create new payment transaction record
        PaymentTransaction::create([
            'order_id' => $orderId,
            'payment_id' => $invoiceData['id'],
            'external_id' => $invoiceData['external_id'],
            'amount' => $invoiceData['amount'],
            'status' => $invoiceData['status'],
            'payment_method' => 'GCASH',
            'payment_channel' => 'XENDIT',
            'payment_url' => $invoiceData['invoice_url'],
            'expiry_date' => $invoiceData['expiry_date'],
            'payload' => json_encode($invoiceData),
        ]);
    }

    /**
     * Update order with payment details
     *
     * @param int $orderId
     * @param array $invoiceData
     * @return void
     */
    private function updateOrderPayment($orderId, $invoiceData)
    {
        $order = Order::find($orderId);
        if ($order) {
            $order->payment_method = 'gcash';
            $order->payment_status = 'pending';
            $order->payment_id = $invoiceData['id'];
            $order->save();
        }
    }

    /**
     * Handle Xendit webhook callback
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function webhook(Request $request)
    {
        // Validate the webhook payload
        $payload = $request->all();
        
        // Log the webhook payload for debugging
        Log::info('Xendit webhook received', $payload);
        
        // Verify that this is a valid webhook from Xendit
        // You should implement proper validation here, such as checking the callback token

        if (!isset($payload['id']) || !isset($payload['status'])) {
            return response()->json(['error' => 'Invalid webhook payload'], 400);
        }

        // Find the payment transaction using the Xendit payment ID
        $transaction = PaymentTransaction::where('payment_id', $payload['id'])->first();
        if (!$transaction) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Update the transaction status
        $transaction->status = $payload['status'];
        $transaction->payload = json_encode($payload);
        $transaction->save();

        // Update the order status based on the payment status
        $order = Order::find($transaction->order_id);
        if ($order) {
            if ($payload['status'] === 'PAID' || $payload['status'] === 'SETTLED') {
                $order->payment_status = 'paid';
                $order->status = 'processing';
            } else if ($payload['status'] === 'EXPIRED') {
                $order->payment_status = 'failed';
                $order->status = 'cancelled';
            }
            $order->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get payment status from Xendit
     *
     * @param string $paymentId
     * @return array
     */
    public function getPaymentStatus($paymentId)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->get($this->xenditApiUrl . '/v2/invoices/' . $paymentId);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('Xendit API Error when checking status: ' . $response->body());
                return ['status' => 'UNKNOWN', 'error' => $response->body()];
            }
        } catch (\Exception $e) {
            Log::error('Xendit status check error: ' . $e->getMessage());
            return ['status' => 'ERROR', 'error' => $e->getMessage()];
        }
    }
}