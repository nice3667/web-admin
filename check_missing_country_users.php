<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Configuration
$baseUrl = 'https://mypartners.xm.com/api';
$apiToken = 'cb3Sq0knGOc1Vq8yxNz1oeXoG8MonNGfAbinMBSJVV0=';

// Country mapping function
function getCountryName($code) {
    if (empty($code) || trim($code) === '') {
        return 'ไม่ระบุ';
    }

    $code = strtoupper(trim($code));

    $codeMap = [
        'THAILAND' => 'TH',
        'THAI' => 'TH',
        'MALAYSIA' => 'MY',
        'SINGAPORE' => 'SG',
        'INDONESIA' => 'ID',
        'VIETNAM' => 'VN',
        'PHILIPPINES' => 'PH',
        'CAMBODIA' => 'KH',
        'LAOS' => 'LA',
        'MYANMAR' => 'MM',
        'BRUNEI' => 'BN'
    ];

    if (isset($codeMap[$code])) {
        $code = $codeMap[$code];
    }

    $countries = [
        'TH' => 'ประเทศไทย',
        'MY' => 'มาเลเซีย',
        'SG' => 'สิงคโปร์',
        'ID' => 'อินโดนีเซีย',
        'VN' => 'เวียดนาม',
        'PH' => 'ฟิลิปปินส์',
        'KH' => 'กัมพูชา',
        'LA' => 'ลาว',
        'MM' => 'พม่า',
        'BN' => 'บรูไน',
        'CN' => 'จีน',
        'JP' => 'ญี่ปุ่น',
        'KR' => 'เกาหลีใต้',
        'IN' => 'อินเดีย',
        'AU' => 'ออสเตรเลีย',
        'NZ' => 'นิวซีแลนด์',
        'GB' => 'สหราชอาณาจักร',
        'US' => 'สหรัฐอเมริกา',
        'CA' => 'แคนาดา',
        'FR' => 'ฝรั่งเศส',
        'DE' => 'เยอรมนี',
        'IT' => 'อิตาลี',
        'ES' => 'สเปน',
        'PT' => 'โปรตุเกส',
        'NL' => 'เนเธอร์แลนด์',
        'BE' => 'เบลเยียม',
        'CH' => 'สวิตเซอร์แลนด์',
        'AT' => 'ออสเตรีย',
        'SE' => 'สวีเดน',
        'NO' => 'นอร์เวย์',
        'DK' => 'เดนมาร์ก',
        'FI' => 'ฟินแลนด์',
        'IE' => 'ไอร์แลนด์',
        'PL' => 'โปแลนด์',
        'RU' => 'รัสเซีย',
        'BR' => 'บราซิล',
        'MX' => 'เม็กซิโก',
        'AR' => 'อาร์เจนตินา',
        'CL' => 'ชิลี',
        'CO' => 'โคลอมเบีย',
        'PE' => 'เปรู',
        'VE' => 'เวเนซุเอลา',
        'ZA' => 'แอฟริกาใต้',
        'NG' => 'ไนจีเรีย',
        'KE' => 'เคนยา',
        'EG' => 'อียิปต์',
        'SA' => 'ซาอุดีอาระเบีย',
        'AE' => 'สหรัฐอาหรับเอมิเรตส์',
        'TR' => 'ตุรกี',
        'IL' => 'อิสราเอล',
        'PK' => 'ปากีสถาน',
        'BD' => 'บังกลาเทศ',
        'LK' => 'ศรีลังกา',
        'NP' => 'เนปาล',
        'AF' => 'อัฟกานิสถาน'
    ];

    return $countries[$code] ?? $code;
}

// Country detection function
function detectCountryFromTraderData($trader) {
    $detectedCountry = null;
    $confidence = 0;
    $method = null;

    // Method 1: Check campaign data for country hints
    if (isset($trader['campaign']) && !empty($trader['campaign'])) {
        $campaign = strtoupper($trader['campaign']);
        
        // Enhanced campaign patterns with more specific Thai indicators
        $campaignPatterns = [
            'TH' => [
                'THAILAND', 'THAI', 'TH_', '_TH',
                'KAENG', 'KAN', 'K.KAN', 'KAENGIB', // Thai names/patterns
                'PROFIT', 'LOW', 'RB YOU', 'AUTO CONNECT' // Common Thai campaigns
            ],
            'MY' => ['MALAYSIA', 'MALAYSIAN', 'MY_', '_MY'],
            'SG' => ['SINGAPORE', 'SG_', '_SG'],
            'ID' => ['INDONESIA', 'INDONESIAN', 'ID_', '_ID'],
            'VN' => ['VIETNAM', 'VIETNAMESE', 'VN_', '_VN'],
            'PH' => ['PHILIPPINES', 'FILIPINO', 'PH_', '_PH']
        ];

        foreach ($campaignPatterns as $countryCode => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($campaign, $pattern) !== false) {
                    $detectedCountry = $countryCode;
                    $confidence = 70;
                    $method = 'campaign_analysis';
                    break 2;
                }
            }
        }
    }

    // Method 2: Check for Thai-specific patterns in campaign names
    if (!$detectedCountry && isset($trader['campaign']) && !empty($trader['campaign'])) {
        $campaign = strtolower($trader['campaign']);
        
        // Thai-specific patterns
        $thaiPatterns = [
            'kaeng', 'kan', 'profit', 'low', 'rb you', 'auto connect'
        ];
        
        foreach ($thaiPatterns as $pattern) {
            if (strpos($campaign, $pattern) !== false) {
                $detectedCountry = 'TH';
                $confidence = 65;
                $method = 'thai_campaign_pattern';
                break;
            }
        }
    }

    // Method 3: Check trading platform for regional hints
    if (!$detectedCountry && isset($trader['tradingPlatform'])) {
        $platform = strtoupper($trader['tradingPlatform']);
        
        if (strpos($platform, 'ASIA') !== false) {
            $detectedCountry = 'TH';
            $confidence = 30;
            $method = 'platform_region';
        }
    }

    // Method 4: Check account type for regional patterns
    if (!$detectedCountry && isset($trader['accountType'])) {
        $accountType = strtoupper($trader['accountType']);
        
        if (strpos($accountType, 'MICRO') !== false) {
            $detectedCountry = 'TH';
            $confidence = 20;
            $method = 'account_type_pattern';
        }
    }

    // Method 5: Check client ID patterns
    if (!$detectedCountry && isset($trader['clientId'])) {
        $clientId = $trader['clientId'];
        
        if (preg_match('/^66/', $clientId)) {
            $detectedCountry = 'TH';
            $confidence = 60;
            $method = 'client_id_pattern';
        } elseif (preg_match('/^60/', $clientId)) {
            $detectedCountry = 'MY';
            $confidence = 60;
            $method = 'client_id_pattern';
        }
    }

    // Method 6: Default assumption for Southeast Asian region
    if (!$detectedCountry) {
        // Based on the data pattern, most users without country info seem to be from Thailand
        $detectedCountry = 'TH';
        $confidence = 15;
        $method = 'regional_default';
    }

    return [
        'country_code' => $detectedCountry,
        'country_name' => $detectedCountry ? getCountryName($detectedCountry) : 'ไม่สามารถตรวจหาได้',
        'confidence' => $confidence,
        'detection_method' => $method,
        'raw_data_analyzed' => [
            'campaign' => $trader['campaign'] ?? null,
            'tradingPlatform' => $trader['tradingPlatform'] ?? null,
            'accountType' => $trader['accountType'] ?? null,
            'clientId' => $trader['clientId'] ?? null
        ]
    ];
}

// Main execution
try {
    echo "🔍 กำลังตรวจสอบข้อมูล user ที่ไม่มีข้อมูลประเทศ...\n";
    echo "=" . str_repeat("=", 60) . "\n";

    $startTime = date('Y-m-d', strtotime('-30 days'));
    $endTime = date('Y-m-d');

    // Initialize HTTP client (using Guzzle for standalone script)
    $client = new \GuzzleHttp\Client();

    $response = $client->request('GET', $baseUrl . '/trader-statistics/trader-list', [
        'headers' => [
            'Authorization' => 'Bearer ' . $apiToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ],
        'query' => [
            'startTime' => $startTime,
            'endTime' => $endTime,
        ],
        'timeout' => 30
    ]);

    if ($response->getStatusCode() !== 200) {
        throw new Exception('Failed to fetch data from XM API. Status: ' . $response->getStatusCode());
    }

    $data = json_decode($response->getBody()->getContents(), true);
    
    if (!$data) {
        throw new Exception('Invalid response from XM API');
    }

    // Analyze data
    $missingCountryUsers = [];
    $totalUsers = count($data);
    $missingCount = 0;

    echo "📊 สรุปข้อมูลทั้งหมด:\n";
    echo "   - ช่วงเวลา: {$startTime} ถึง {$endTime}\n";
    echo "   - จำนวน user ทั้งหมด: {$totalUsers} คน\n";
    echo "\n";

    foreach ($data as $trader) {
        $country = isset($trader['country']) ? trim($trader['country']) : null;
        
        if (empty($country)) {
            $missingCount++;
            
            // Try to detect country from other data
            $detectedCountry = detectCountryFromTraderData($trader);
            
            $missingCountryUsers[] = [
                'traderId' => $trader['traderId'] ?? 'unknown',
                'clientId' => $trader['clientId'] ?? 'unknown',
                'originalCountry' => $trader['country'] ?? null,
                'detectedCountry' => $detectedCountry,
                'accountType' => $trader['accountType'] ?? 'ไม่ระบุ',
                'campaign' => $trader['campaign'] ?? 'ไม่มี',
                'tradingPlatform' => $trader['tradingPlatform'] ?? 'ไม่ระบุ',
                'signUpDate' => $trader['signUpDate'] ?? null,
                'activationDate' => $trader['activationDate'] ?? null,
            ];
        }
    }

    $percentage = round(($missingCount / $totalUsers) * 100, 2);

    echo "❌ ผลการตรวจสอบ:\n";
    echo "   - จำนวน user ที่ไม่มีข้อมูลประเทศ: {$missingCount} คน\n";
    echo "   - เปอร์เซ็นต์: {$percentage}%\n";
    echo "\n";

    if ($missingCount > 0) {
        echo "🔍 รายละเอียด user ที่ไม่มีข้อมูลประเทศ:\n";
        echo str_repeat("-", 80) . "\n";
        
        $detectedCount = 0;
        $highConfidenceCount = 0;
        $mediumConfidenceCount = 0;
        $lowConfidenceCount = 0;

        foreach ($missingCountryUsers as $index => $user) {
            $num = $index + 1;
            $detection = $user['detectedCountry'];
            
            echo "{$num}. Trader ID: {$user['traderId']}\n";
            echo "   Client ID: {$user['clientId']}\n";
            echo "   Account Type: {$user['accountType']}\n";
            echo "   Campaign: {$user['campaign']}\n";
            echo "   Trading Platform: {$user['tradingPlatform']}\n";
            echo "   Sign Up Date: {$user['signUpDate']}\n";
            
            if ($detection['country_code']) {
                $detectedCount++;
                $confidence = $detection['confidence'];
                
                if ($confidence >= 60) {
                    $highConfidenceCount++;
                    $confidenceText = "สูง";
                } elseif ($confidence >= 30) {
                    $mediumConfidenceCount++;
                    $confidenceText = "ปานกลาง";
                } else {
                    $lowConfidenceCount++;
                    $confidenceText = "ต่ำ";
                }
                
                echo "   🎯 ประเทศที่ตรวจพบ: {$detection['country_name']} ({$detection['country_code']})\n";
                echo "   📊 ความเชื่อมั่น: {$confidence}% ({$confidenceText})\n";
                echo "   🔍 วิธีการตรวจหา: {$detection['detection_method']}\n";
            } else {
                echo "   ❌ ไม่สามารถตรวจหาประเทศได้\n";
            }
            
            echo "\n";
        }

        echo "📈 สถิติการตรวจหาประเทศ:\n";
        echo "   - ตรวจหาได้: {$detectedCount} คน\n";
        echo "   - ความเชื่อมั่นสูง (60%+): {$highConfidenceCount} คน\n";
        echo "   - ความเชื่อมั่นปานกลาง (30-59%): {$mediumConfidenceCount} คน\n";
        echo "   - ความเชื่อมั่นต่ำ (1-29%): {$lowConfidenceCount} คน\n";
        echo "   - ตรวจหาไม่ได้: " . ($missingCount - $detectedCount) . " คน\n";
        echo "\n";

        // Count detected countries
        $countryStats = [];
        foreach ($missingCountryUsers as $user) {
            $countryCode = $user['detectedCountry']['country_code'];
            if ($countryCode) {
                if (!isset($countryStats[$countryCode])) {
                    $countryStats[$countryCode] = 0;
                }
                $countryStats[$countryCode]++;
            }
        }

        if (!empty($countryStats)) {
            echo "🌍 การกระจายตัวของประเทศที่ตรวจพบ:\n";
            arsort($countryStats);
            foreach ($countryStats as $countryCode => $count) {
                $countryName = getCountryName($countryCode);
                echo "   - {$countryName}: {$count} คน\n";
            }
        }
    } else {
        echo "✅ ไม่พบ user ที่ไม่มีข้อมูลประเทศ\n";
    }

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ การตรวจสอบเสร็จสิ้น\n";

} catch (Exception $e) {
    echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "\n";
    echo "กรุณาตรวจสอบ API Token และการเชื่อมต่อ\n";
} 