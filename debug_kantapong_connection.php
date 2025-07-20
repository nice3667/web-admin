<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Log;

echo "🔍 Testing Kantapong Exness Connection\n";
echo "=====================================\n\n";

$baseUrl = 'https://my.exnessaffiliates.com';
$email = 'kantapong0592@gmail.com';

// Test direct API connection first
echo "1. Testing direct API connection...\n";
$ch = curl_init($baseUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_NOBODY => true,
    CURLOPT_HEADER => true
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Base URL Status: " . $httpCode . "\n";
if ($httpCode !== 200) {
    echo "❌ Cannot connect to Exness API. Please check:\n";
    echo "1. Internet connection\n";
    echo "2. VPN status (if using)\n";
    echo "3. DNS resolution\n";
    exit(1);
}

echo "\n✅ API connection successful\n";

// Test authentication
echo "\n2. Testing authentication...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $baseUrl . '/api/v2/auth/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'login' => $email,
        'password' => 'Kantapong@2025'
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Origin: https://my.exnessaffiliates.com',
        'Referer: https://my.exnessaffiliates.com/'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Auth Status: " . $httpCode . "\n";
$data = json_decode($response, true);

if ($httpCode === 200 && isset($data['token'])) {
    echo "✅ Authentication successful!\n";
    echo "Token: " . substr($data['token'], 0, 20) . "...\n";
    
    // Test token with clients API
    echo "\n3. Testing clients API access...\n";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $baseUrl . '/api/reports/clients/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $data['token'],
            'Accept: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Clients API Status: " . $httpCode . "\n";
    if ($httpCode === 200) {
        $clientsData = json_decode($response, true);
        if (isset($clientsData['data'])) {
            echo "✅ Found " . count($clientsData['data']) . " clients\n";
        } else {
            echo "❌ No client data in response\n";
            echo "Response: " . substr($response, 0, 200) . "...\n";
        }
    } else {
        echo "❌ Failed to access clients API\n";
        echo "Response: " . $response . "\n";
    }
} else {
    echo "❌ Authentication failed!\n";
    echo "Error: " . ($data['message'] ?? 'Unknown error') . "\n";
    echo "\nPlease verify:\n";
    echo "1. Email is correct\n";
    echo "2. Password is correct\n";
    echo "3. Account is active\n";
    echo "4. No IP restrictions\n";
    
    // Additional error details
    if (isset($data['error_description'])) {
        echo "\nError description: " . $data['error_description'] . "\n";
    }
    if (isset($data['errors'])) {
        echo "\nDetailed errors:\n";
        print_r($data['errors']);
    }
} 