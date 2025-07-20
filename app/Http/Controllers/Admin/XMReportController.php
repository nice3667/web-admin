<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class XMReportController extends Controller
{
    private $baseUrl;
    private $apiToken;

    private function getCountryName($code)
    {
        // If code is null, empty string, or just whitespace, return 'ไม่ระบุ'
        if (empty($code) || trim($code) === '') {
            return 'ไม่ระบุ';
        }

        // Convert to uppercase and trim for consistency
        $code = strtoupper(trim($code));

        // Special handling for common variations
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

        // If the input is a full country name, convert it to code
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

        // Log country code for debugging
        Log::info('Processing country code', [
            'original_input' => $code,
            'processed_code' => $code,
            'found_in_map' => isset($countries[$code])
        ]);

        return $countries[$code] ?? $code;
    }

    public function __construct()
    {
        $this->baseUrl = config('services.xm.base_url', 'https://mypartners.xm.com/api');
        $this->apiToken = config('services.xm.api_token', 'cb3Sq0knGOc1Vq8yxNz1oeXoG8MonNGfAbinMBSJVV0=');
    }

    public function index()
    {
        return Inertia::render('Admin/XM/Index');
    }

    public function getTraderList(Request $request)
    {
        try {
            $startTime = $request->get('startTime', date('Y-m-d', strtotime('-30 days')));
            $endTime = $request->get('endTime', date('Y-m-d'));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(30)->get($this->baseUrl . '/trader-statistics/trader-list', [
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]);

            if (!$response->successful()) {
                Log::error('XM API Error: Failed to fetch trader list', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'startTime' => $startTime,
                    'endTime' => $endTime
                ]);
                
                return response()->json([
                    'error' => 'Failed to fetch data from XM API',
                    'message' => $response->json()['message'] ?? 'Authentication failed. Please check API credentials.',
                    'status' => $response->status()
                ], $response->status());
            }

            $data = $response->json();
            
            // Transform and validate data
            $traders = collect($data)->map(function ($trader) {
                // Extract and clean country data
                $country = isset($trader['country']) ? trim($trader['country']) : null;
                $finalCountry = null;
                $countrySource = 'api';
                
                if (empty($country)) {
                    // Log problematic country data
                    Log::info('Trader with missing country - attempting detection:', [
                        'trader_id' => $trader['traderId'] ?? 'unknown',
                        'raw_country_value' => $trader['country'] ?? 'null',
                        'campaign' => $trader['campaign'] ?? 'none'
                    ]);
                    
                    // Try to detect country from other data
                    $detectedCountry = $this->detectCountryFromTraderData($trader);
                    $finalCountry = $detectedCountry['country_name'];
                    $countrySource = 'detected';
                    
                    // Log the detection result
                    Log::info('Country detection result:', [
                        'trader_id' => $trader['traderId'] ?? 'unknown',
                        'detected_country' => $detectedCountry['country_code'],
                        'confidence' => $detectedCountry['confidence'],
                        'method' => $detectedCountry['detection_method']
                    ]);
                } else {
                    $finalCountry = $this->getCountryName($country);
                }

                return [
                    'traderId' => $trader['traderId'] ?? null,
                    'clientId' => $trader['clientId'] ?? null,
                    'country' => $finalCountry,
                    'countrySource' => $countrySource, // เพิ่มข้อมูลแหล่งที่มาของประเทศ
                    'accountType' => $trader['accountType'] ?? 'ไม่ระบุ',
                    'campaign' => $trader['campaign'] ?? 'ไม่มี',
                    'tradingPlatform' => $trader['tradingPlatform'] ?? 'ไม่ระบุ',
                    'signUpDate' => $trader['signUpDate'] ?? null,
                    'activationDate' => $trader['activationDate'] ?? null,
                    'archived' => $trader['archived'] ?? false,
                    'valid' => $trader['valid'] ?? false
                ];
            })->filter(function ($trader) {
                return !is_null($trader['traderId']);
            })->values();

            return response()->json($traders);
            
        } catch (\Exception $e) {
            Log::error('XM API Exception: ' . $e->getMessage(), [
                'exception' => $e,
                'startTime' => $request->get('startTime'),
                'endTime' => $request->get('endTime')
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch data from XM API',
                'message' => 'An unexpected error occurred. Please try again later.',
                'debug_message' => $e->getMessage()
            ], 500);
        }
    }

    public function getTraderTransactions(Request $request)
    {
        try {
            $startTime = $request->get('startTime', date('Y-m-d', strtotime('-30 days')));
            $endTime = $request->get('endTime', date('Y-m-d'));
            $traderIds = $request->get('traderIds');

            $params = [
                'startTime' => $startTime,
                'endTime' => $endTime,
            ];

            if ($traderIds) {
                $params['traderIds'] = $traderIds;
            }

            // Log the request for debugging
            Log::info('XM API Request - Trader Transactions', [
                'params' => $params,
                'token' => substr($this->apiToken, 0, 10) . '...'
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(30)->get($this->baseUrl . '/trader-statistics/trades', $params);

            // Log the response status and headers for debugging
            Log::info('XM API Response - Trader Transactions', [
                'status' => $response->status(),
                'headers' => $response->headers(),
            ]);

            if (!$response->successful()) {
                Log::error('XM API Error: Failed to fetch trader transactions', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'params' => $params
                ]);
                
                return response()->json([
                    'error' => 'Failed to fetch transaction data',
                    'message' => $response->json()['message'] ?? 'Authentication failed. Please check API credentials.',
                    'status' => $response->status()
                ], $response->status());
            }

            return response()->json($response->json());
            
        } catch (\Exception $e) {
            Log::error('XM API Exception: ' . $e->getMessage(), [
                'exception' => $e,
                'params' => $request->all()
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch transaction data',
                'message' => 'An unexpected error occurred. Please try again later.',
                'debug_message' => $e->getMessage()
            ], 500);
        }
    }

    public function getLotRebateStatistics(Request $request)
    {
        try {
            $startTime = $request->get('startTime', date('Y-m-d', strtotime('-30 days')));
            $endTime = $request->get('endTime', date('Y-m-d'));

            // Log the request for debugging
            Log::info('XM API Request - Lot Rebate', [
                'startTime' => $startTime,
                'endTime' => $endTime,
                'token' => substr($this->apiToken, 0, 10) . '...'
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(30)->get($this->baseUrl . '/trader-statistics/lot-rebate', [
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]);

            // Log the response status and headers for debugging
            Log::info('XM API Response - Lot Rebate', [
                'status' => $response->status(),
                'headers' => $response->headers(),
            ]);

            if (!$response->successful()) {
                Log::error('XM API Error: Failed to fetch rebate statistics', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'startTime' => $startTime,
                    'endTime' => $endTime
                ]);
                
                return response()->json([
                    'error' => 'Failed to fetch rebate data',
                    'message' => $response->json()['message'] ?? 'Authentication failed. Please check API credentials.',
                    'status' => $response->status()
                ], $response->status());
            }

            return response()->json($response->json());
            
        } catch (\Exception $e) {
            Log::error('XM API Exception: ' . $e->getMessage(), [
                'exception' => $e,
                'startTime' => $request->get('startTime'),
                'endTime' => $request->get('endTime')
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch rebate data',
                'message' => 'An unexpected error occurred. Please try again later.',
                'debug_message' => $e->getMessage()
            ], 500);
        }
    }

    public function checkMissingCountryData(Request $request)
    {
        try {
            $startTime = $request->get('startTime', date('Y-m-d', strtotime('-30 days')));
            $endTime = $request->get('endTime', date('Y-m-d'));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(30)->get($this->baseUrl . '/trader-statistics/trader-list', [
                'startTime' => $startTime,
                'endTime' => $endTime,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Failed to fetch data from XM API',
                    'message' => $response->json()['message'] ?? 'Authentication failed.',
                    'status' => $response->status()
                ], $response->status());
            }

            $data = $response->json();
            
            // Find users with missing country data
            $missingCountryUsers = [];
            $totalUsers = count($data);
            $missingCount = 0;

            foreach ($data as $trader) {
                $country = isset($trader['country']) ? trim($trader['country']) : null;
                
                if (empty($country)) {
                    $missingCount++;
                    
                    // Try to detect country from other data
                    $detectedCountry = $this->detectCountryFromTraderData($trader);
                    
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
                        'allData' => $trader // เก็บข้อมูลทั้งหมดเพื่อตรวจสอบ
                    ];
                }
            }

            // Log detailed information
            Log::info('Missing country data analysis', [
                'total_users' => $totalUsers,
                'missing_country_count' => $missingCount,
                'percentage' => round(($missingCount / $totalUsers) * 100, 2) . '%',
                'date_range' => $startTime . ' to ' . $endTime
            ]);

            return response()->json([
                'summary' => [
                    'total_users' => $totalUsers,
                    'missing_country_count' => $missingCount,
                    'percentage' => round(($missingCount / $totalUsers) * 100, 2),
                    'date_range' => $startTime . ' to ' . $endTime
                ],
                'missing_country_users' => $missingCountryUsers,
                'country_detection_stats' => $this->getCountryDetectionStats($missingCountryUsers)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Missing country check failed: ' . $e->getMessage(), [
                'exception' => $e,
                'startTime' => $request->get('startTime'),
                'endTime' => $request->get('endTime')
            ]);
            
            return response()->json([
                'error' => 'Failed to check missing country data',
                'message' => 'An unexpected error occurred. Please try again later.',
                'debug_message' => $e->getMessage()
            ], 500);
        }
    }

    private function detectCountryFromTraderData($trader)
    {
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
            
            // Some platforms might have regional indicators
            if (strpos($platform, 'ASIA') !== false) {
                // Default to Thailand for Asian platforms (most common in our system)
                $detectedCountry = 'TH';
                $confidence = 30;
                $method = 'platform_region';
            }
        }

        // Method 4: Check account type for regional patterns
        if (!$detectedCountry && isset($trader['accountType'])) {
            $accountType = strtoupper($trader['accountType']);
            
            // Some account types might have regional patterns
            if (strpos($accountType, 'MICRO') !== false) {
                // Micro accounts are popular in Southeast Asia
                $detectedCountry = 'TH';
                $confidence = 20;
                $method = 'account_type_pattern';
            }
        }

        // Method 5: Check client ID patterns (if any regional encoding exists)
        if (!$detectedCountry && isset($trader['clientId'])) {
            $clientId = $trader['clientId'];
            
            // Check if client ID has any regional patterns
            if (preg_match('/^66/', $clientId)) { // 66 is Thailand country code
                $detectedCountry = 'TH';
                $confidence = 60;
                $method = 'client_id_pattern';
            } elseif (preg_match('/^60/', $clientId)) { // 60 is Malaysia country code
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
            'country_name' => $detectedCountry ? $this->getCountryName($detectedCountry) : 'ไม่สามารถตรวจหาได้',
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

    private function getCountryDetectionStats($missingCountryUsers)
    {
        $stats = [
            'total_missing' => count($missingCountryUsers),
            'detected_countries' => [],
            'detection_methods' => [],
            'confidence_levels' => [
                'high' => 0,    // 60+ confidence
                'medium' => 0,  // 30-59 confidence
                'low' => 0,     // 1-29 confidence
                'none' => 0     // 0 confidence
            ]
        ];

        foreach ($missingCountryUsers as $user) {
            $detection = $user['detectedCountry'];
            
            if ($detection['country_code']) {
                // Count detected countries
                $country = $detection['country_code'];
                if (!isset($stats['detected_countries'][$country])) {
                    $stats['detected_countries'][$country] = 0;
                }
                $stats['detected_countries'][$country]++;

                // Count detection methods
                $method = $detection['detection_method'];
                if (!isset($stats['detection_methods'][$method])) {
                    $stats['detection_methods'][$method] = 0;
                }
                $stats['detection_methods'][$method]++;

                // Count confidence levels
                $confidence = $detection['confidence'];
                if ($confidence >= 60) {
                    $stats['confidence_levels']['high']++;
                } elseif ($confidence >= 30) {
                    $stats['confidence_levels']['medium']++;
                } elseif ($confidence > 0) {
                    $stats['confidence_levels']['low']++;
                } else {
                    $stats['confidence_levels']['none']++;
                }
            } else {
                $stats['confidence_levels']['none']++;
            }
        }

        return $stats;
    }
} 