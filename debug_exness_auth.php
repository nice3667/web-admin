<?php

echo "🔍 Testing Direct Exness API Authentication\n";
echo "=========================================\n\n";

$email = 'kantapong0592@gmail.com';
$password = 'Kantapong.0592z';

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://my.exnessaffiliates.com/api/v2/auth/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'login' => $email,          // Use login instead of email
        'password' => $password,
        'remember' => true,
        'client_id' => 'web_app',
        'grant_type' => 'password'  // Add grant_type
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Origin: https://my.exnessaffiliates.com',
        'Referer: https://my.exnessaffiliates.com/',
        'Accept-Language: en-US,en;q=0.9,th;q=0.8',
        'Accept-Encoding: gzip, deflate, br',
        'Connection: keep-alive',
        'X-Requested-With: XMLHttpRequest'
    ],
    CURLOPT_VERBOSE => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_ENCODING => 'gzip, deflate',
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 5
]);

// Create a temporary file to store verbose output
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

echo "Sending request with updated format...\n";
echo "URL: https://my.exnessaffiliates.com/api/v2/auth/\n";
echo "Login: $email\n";
echo "Password: " . str_repeat('*', strlen($password)) . "\n\n";

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Get verbose information
rewind($verbose);
$verboseLog = stream_get_contents($verbose);

echo "HTTP Status: " . $httpCode . "\n\n";
echo "Response:\n";
echo $response . "\n\n";

echo "Detailed Request/Response:\n";
echo $verboseLog . "\n";

// Check for errors
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
}

curl_close($ch);

// Try to decode response
$data = json_decode($response, true);
if ($data) {
    echo "\nDecoded Response:\n";
    print_r($data);
    
    if (isset($data['error'])) {
        echo "\nError Details:\n";
        echo "Code: " . ($data['error']['code'] ?? 'N/A') . "\n";
        echo "Message: " . ($data['error']['message'] ?? 'N/A') . "\n";
    }
} 