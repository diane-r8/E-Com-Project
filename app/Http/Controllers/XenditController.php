<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class XenditController extends Controller
{

    
public function payWithGCash(Request $request)
{
    $amount = $request->amount;
    
    $response = Http::withBasicAuth(config('services.xendit.secret_key'), '')
        ->post('https://api.xendit.co/ewallets/charges', [
            'reference_id' => 'order_' . time(),
            'currency' => 'PHP',
            'amount' => $amount,
            'checkout_method' => 'ONE_TIME_PAYMENT',
            'channel_code' => 'GCASH',
            'channel_properties' => [
                'mobile_number' => '09171234567', 
                'success_redirect_url' => route('payment.success'),
                'failure_redirect_url' => route('payment.failed'),
            ],
        ]);

    $responseData = $response->json();

    if ($response->successful()) {
        return response()->json([
            'status' => $responseData['status'],
            'actions' => $responseData['actions']
        ]);
    } else {
        return response()->json(['error' => 'Payment request failed'], 400);
    }
}
}