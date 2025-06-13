<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class ExnessController extends Controller
{
    // ฟังก์ชัน login และดึงข้อมูลลูกค้า
    public function test(Request $request)
    {
        // *** ใส่อีเมลและรหัสผ่านของคุณตรงนี้ หรือรับจาก $request ***
        $loginData = [
            'login' => 'Janischa.trade@gmail.com',   // ต้องใช้ key 'login' ไม่ใช่ 'email'
            'password' => 'Janis@2025',
        ];

        // 1. ขอ JWT Token
        $res = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://my.exnessaffiliates.com/api/v2/auth/", $loginData);

        $token = $res->json()['token'] ?? null;

        if (!$token) {
            return response()->json(['error' => 'ไม่สามารถรับ token ได้'], 500);
        }

        // 2. ดึงข้อมูลลูกค้า
        $res2 = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'JWT ' . $token,
        ])->get("https://my.exnessaffiliates.com/api/reports/clients/");

        if ($res2->failed()) {
            return response()->json(['error' => 'ไม่สามารถดึงข้อมูลลูกค้าได้'], 500);
        }

        return response()->json($res2->json());
    }

    public function getClients(Request $request)
    {
        try {
            // *** ใส่อีเมลและรหัสผ่านของคุณตรงนี้ หรือรับจาก $request ***
            $loginData = [
                'login' => 'Janischa.trade@gmail.com',   // ต้องใช้ key 'login' ไม่ใช่ 'email'
                'password' => 'Janis@2025',
            ];

            // 1. ขอ JWT Token
            $res = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://my.exnessaffiliates.com/api/v2/auth/", $loginData);

            $token = $res->json()['token'] ?? null;

            if (!$token) {
                return response()->json(['error' => 'ไม่สามารถรับ token ได้'], 500);
            }

            // 2. ดึงข้อมูลลูกค้าจากทั้ง 2 API พร้อมกันด้วย GuzzleHttp
            $client = new Client([
                'headers' => [
                    'Authorization' => 'JWT ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'verify' => false
            ]);

            try {
                // ดึงข้อมูลจากทั้งสอง API พร้อมกัน
                $promises = [
                    'v1' => $client->getAsync('https://my.exnessaffiliates.com/api/reports/clients/?limit=100'),
                    'v2' => $client->getAsync('https://my.exnessaffiliates.com/api/v2/reports/clients/?limit=100')
                ];

                // รอให้ทั้งสอง request เสร็จสิ้น
                $responses = Promise\Utils::unwrap($promises);

                // แปลงข้อมูลจาก V1 API
                $v1Data = json_decode($responses['v1']->getBody(), true);
                \Log::info('V1 API Response:', ['data' => $v1Data]);

                // แปลงข้อมูลจาก V2 API
                $v2Data = json_decode($responses['v2']->getBody(), true);
                \Log::info('V2 API Response:', ['data' => $v2Data]);

                // เพิ่ม source label ให้กับข้อมูลแต่ละเส้น
                $v1Clients = [];
                if (isset($v1Data['data']) && is_array($v1Data['data'])) {
                    foreach ($v1Data['data'] as $client) {
                        $client['source'] = 'v1';
                        $v1Clients[] = $client;
                    }
                }

                $v2Clients = [];
                if (isset($v2Data['data']) && is_array($v2Data['data'])) {
                    foreach ($v2Data['data'] as $client) {
                        $client['source'] = 'v2';
                        $v2Clients[] = $client;
                    }
                }

                \Log::info('V1 Clients:', ['clients' => $v1Clients]);
                \Log::info('V2 Clients:', ['clients' => $v2Clients]);

                // ส่งข้อมูลกลับในรูปแบบที่ต้องการ
                return response()->json([
                    'data_v1' => $v1Clients,
                    'data_v2' => $v2Clients
                ]);

            } catch (\Exception $e) {
                \Log::error('API Request Error:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'เกิดข้อผิดพลาดในการเรียก API: ' . $e->getMessage()
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clients()
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                \Log::error('No token available');
                return response()->json(['error' => 'Failed to get token'], 500);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://my.exnessaffiliates.com/api/v2/reports/clients/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: JWT ' . $token
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            \Log::info('API Response:', [
                'status' => $httpCode,
                'body' => $response
            ]);

            if ($httpCode === 200) {
                return response()->json(json_decode($response, true));
            }

            \Log::error('Exness API Error:', [
                'status' => $httpCode,
                'body' => $response
            ]);

            return response()->json(['error' => 'Failed to fetch clients data'], 500);
        } catch (\Exception $e) {
            \Log::error('Exness API Exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getToken()
    {
        try {
            // First, get the CSRF token
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://my.exnessaffiliates.com/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

            $response = curl_exec($ch);
            curl_close($ch);

            // Now try to get the token
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://my.exnessaffiliates.com/api/v2/auth/token/');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'username' => 'Janischa.trade@gmail.com',
                'password' => 'Janis@2025'
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Origin: https://my.exnessaffiliates.com',
                'Referer: https://my.exnessaffiliates.com/'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            \Log::info('Token Request Details:', [
                'url' => 'https://my.exnessaffiliates.com/api/v2/auth/token/',
                'headers' => [
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Origin: https://my.exnessaffiliates.com',
                    'Referer: https://my.exnessaffiliates.com/'
                ],
                'body' => [
                    'username' => 'Janischa.trade@gmail.com',
                    'password' => 'Janis@2025'
                ],
                'curl_info' => curl_getinfo($ch)
            ]);

            \Log::info('Token Response:', [
                'status' => $httpCode,
                'body' => $response
            ]);

            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                return $data['token'] ?? null;
            }

            \Log::error('Failed to get token:', [
                'status' => $httpCode,
                'body' => $response
            ]);

            return null;
        } catch (\Exception $e) {
            \Log::error('Token Exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function clientsV1()
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                \Log::error('No token available for V1 API');
                return response()->json(['error' => 'Failed to get token'], 500);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://my.exnessaffiliates.com/api/v2/reports/clients/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: JWT ' . $token
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            \Log::info('V1 API Response:', [
                'status' => $httpCode,
                'body' => $response
            ]);

            if ($httpCode === 200) {
                return response()->json(json_decode($response, true));
            }

            \Log::error('Exness API V1 Error:', [
                'status' => $httpCode,
                'body' => $response
            ]);

            return response()->json(['error' => 'Failed to fetch clients data'], 500);
        } catch (\Exception $e) {
            \Log::error('Exness API V1 Exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function clientsV2()
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                \Log::error('No token available for V2 API');
                return response()->json(['error' => 'Failed to get token'], 500);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://my.exnessaffiliates.com/api/reports/clients/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: JWT ' . $token
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            \Log::info('V2 API Response:', [
                'status' => $httpCode,
                'body' => $response
            ]);

            if ($httpCode === 200) {
                return response()->json(json_decode($response, true));
            }

            \Log::error('Exness API V2 Error:', [
                'status' => $httpCode,
                'body' => $response
            ]);

            return response()->json(['error' => 'Failed to fetch clients data'], 500);
        } catch (\Exception $e) {
            \Log::error('Exness API V2 Exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
