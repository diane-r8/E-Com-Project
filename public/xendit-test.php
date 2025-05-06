<?php
// xendit-test.php
require_once '../vendor/autoload.php';

// Set up error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Try to load Laravel .env file
if (file_exists('../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable('../');
    $dotenv->load();
}

// Use Laravel's env() helper if available, otherwise getenv()
if (function_exists('env')) {
    $xenditApiKey = env('XENDIT_API_KEY');
} else {
    $xenditApiKey = getenv('XENDIT_API_KEY');
    // Also try $_ENV and $_SERVER
    if (!$xenditApiKey) {
        $xenditApiKey = $_ENV['XENDIT_API_KEY'] ?? null;
    }
    if (!$xenditApiKey) {
        $xenditApiKey = $_SERVER['XENDIT_API_KEY'] ?? null;
    }
}

// Manually set the API key for testing
if (!$xenditApiKey) {
    // IMPORTANT: Replace this with your actual key for testing
    $xenditApiKey = 'xnd_development_fGVT9DLG8ExQkbrSKDhXArvlLFU0F6YH1bmyMVrFCmOchdE2E96fsTqWKktLs';
    echo "Using hardcoded API key for testing.<br>";
}

if (!$xenditApiKey) {
    die("Xendit API key not found in environment variables or hardcoded value");
}

echo "API Key found (length: " . strlen($xenditApiKey) . "). Testing Xendit connection...<br>";

// Create a simple test invoice
$externalId = 'test-' . uniqid();

// Set up the payment request body
// Update the payload to remove reminder_time
$payload = [
    'external_id' => $externalId,
    'amount' => 100, // Just a test amount of 100 PHP
    'description' => 'Test payment',
    'invoice_duration' => 86400, // 24 hours
    'currency' => 'PHP',
    // Remove the reminder_time parameter
    'success_redirect_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/xendit-success.php',
    'failure_redirect_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/xendit-failure.php',
    'payment_methods' => ['GCASH']
];

echo "Payload prepared: <pre>" . print_r($payload, true) . "</pre><br>";

// Call Xendit API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.xendit.co/v2/invoices');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode($xenditApiKey . ':'),
    'Content-Type: application/json'
]);

echo "Making API call to Xendit...<br>";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for cURL errors
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch) . "<br>";
} else {
    echo "HTTP Code: " . $httpCode . "<br>";
    echo "Response: <pre>" . print_r(json_decode($response, true), true) . "</pre><br>";
    
    if ($httpCode >= 200 && $httpCode < 300) {
        $responseData = json_decode($response, true);
        
        if (isset($responseData['invoice_url'])) {
            echo "Success! Click here to test the payment: <a href='" . $responseData['invoice_url'] . "' target='_blank'>Go to Xendit</a>";
        } else {
            echo "Error: No invoice_url in the response";
        }
    } else {
        echo "Error response from Xendit";
    }
}

curl_close($ch);