<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\HamExnessAuthService;
use Illuminate\Support\Facades\Http;

echo "🔍 Ham Exness API Debug Tool\n";
echo "============================\n\n";

// Manual credentials test
$email = 'hamsftmo@gmail.com';
$password = 'Ham@240446';

echo "1. Testing manual authentication...\n";
echo "Email: {$email}\n";

try {
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Origin' => 'https://my.exnessaffiliates.com',
        'Referer' => 'https://my.exnessaffiliates.com/'
    ])->post('https://my.exnessaffiliates.com/api/v2/auth/', [
        'login' => $email,
        'password' => $password
    ]);

    echo "Status: " . $response->status() . "\n";
    echo "Response: " . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n\n";

    if ($response->successful()) {
        $token = $response->json()['token'] ?? null;
        
        if ($token) {
            echo "✅ Authentication successful!\n";
            echo "Token preview: " . substr($token, 0, 20) . "...\n\n";

            // Test API calls with token
            echo "2. Testing V1 API endpoint...\n";
            $v1Response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get('https://my.exnessaffiliates.com/api/reports/clients/');

            echo "V1 Status: " . $v1Response->status() . "\n";
            $v1Data = $v1Response->json();
            if (isset($v1Data['data'])) {
                echo "V1 Records: " . count($v1Data['data']) . "\n";
                if (!empty($v1Data['data'])) {
                    echo "V1 Sample record fields: " . implode(', ', array_keys($v1Data['data'][0])) . "\n";
                }
            } else {
                echo "V1 Response: " . json_encode($v1Data, JSON_PRETTY_PRINT) . "\n";
            }
            echo "\n";

            echo "3. Testing V2 API endpoint...\n";
            $v2Response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get('https://my.exnessaffiliates.com/api/v2/reports/clients/');

            echo "V2 Status: " . $v2Response->status() . "\n";
            $v2Data = $v2Response->json();
            if (isset($v2Data['data'])) {
                echo "V2 Records: " . count($v2Data['data']) . "\n";
                if (!empty($v2Data['data'])) {
                    echo "V2 Sample record fields: " . implode(', ', array_keys($v2Data['data'][0])) . "\n";
                }
            } else {
                echo "V2 Response: " . json_encode($v2Data, JSON_PRETTY_PRINT) . "\n";
            }
            echo "\n";

            // Check if any data exists
            $v1Count = isset($v1Data['data']) ? count($v1Data['data']) : 0;
            $v2Count = isset($v2Data['data']) ? count($v2Data['data']) : 0;

            echo "📊 Summary:\n";
            echo "V1 API: {$v1Count} records\n";
            echo "V2 API: {$v2Count} records\n";
            echo "Total unique data sources: " . ($v1Count > 0 ? 1 : 0) + ($v2Count > 0 ? 1 : 0) . "\n\n";

            if ($v1Count === 0 && $v2Count === 0) {
                echo "⚠️  No client data found in either API endpoint.\n";
                echo "This could mean:\n";
                echo "- Account has no clients yet\n";
                echo "- Data is in a different endpoint\n";
                echo "- API permissions are limited\n";
                echo "- Account needs verification\n\n";
            } else {
                echo "✅ Client data found! The connection is working properly.\n\n";
            }

            // Test with HamExnessAuthService
            echo "4. Testing with HamExnessAuthService...\n";
            try {
                $hamService = new HamExnessAuthService();
                $serviceResult = $hamService->getClientsData();
                
                if (isset($serviceResult['error'])) {
                    echo "❌ Service error: " . $serviceResult['error'] . "\n";
                } else {
                    $serviceCount = isset($serviceResult['data']) ? count($serviceResult['data']) : 0;
                    echo "✅ Service returned {$serviceCount} records\n";
                }
            } catch (Exception $e) {
                echo "❌ Service exception: " . $e->getMessage() . "\n";
            }

        } else {
            echo "❌ No token received in response\n";
        }
    } else {
        echo "❌ Authentication failed!\n";
        echo "Response: " . $response->body() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🔧 Troubleshooting Tips:\n";
echo "1. Check if credentials are correct\n";
echo "2. Verify account status on Exness Partners\n";
echo "3. Check if account has client data\n";
echo "4. Ensure API access is enabled\n";
echo "5. Check network connectivity\n";
echo "6. Review Exness API documentation for changes\n"; 