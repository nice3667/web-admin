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
                
                // Log problematic country data
                if (empty($country)) {
                    Log::info('Trader with missing country:', [
                        'trader_id' => $trader['traderId'] ?? 'unknown',
                        'raw_country_value' => $trader['country'] ?? 'null'
                    ]);
                }

                return [
                    'traderId' => $trader['traderId'] ?? null,
                    'clientId' => $trader['clientId'] ?? null,
                    'country' => $this->getCountryName($country),
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
} 