<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class KantapongExnessAuthService
{
    private string $email = 'kantapong.exness@gmail.com';
    private string $password = 'Kantapong@240446';

    public function retrieveToken(): ?string
    {
        try {
            Log::info('Kantapong: Attempting to retrieve token', [
                'email' => $this->email,
                'timestamp' => now()->toISOString()
            ]);

            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Origin' => 'https://my.exnessaffiliates.com',
                'Referer' => 'https://my.exnessaffiliates.com/'
            ])->post('https://my.exnessaffiliates.com/api/v2/auth/', [
                'login' => $this->email,
                'password' => $this->password
            ]);

            Log::info('Kantapong Token request response:', [
                'url' => 'https://my.exnessaffiliates.com/api/v2/auth/',
                'login' => $this->email,
                'status' => $response->status(),
                'has_token' => isset($response->json()['token']),
                'response_keys' => array_keys($response->json() ?? [])
            ]);

            if ($response->successful()) {
                $token = $response->json()['token'] ?? null;
                if ($token) {
                    Log::info('Kantapong: Token retrieved successfully', [
                        'token_length' => strlen($token),
                        'token_preview' => substr($token, 0, 20) . '...'
                    ]);
                    return $token;
                } else {
                    Log::error('Kantapong: Token not found in successful response', [
                        'response' => $response->json()
                    ]);
                    return null;
                }
            }

            Log::error('Kantapong Token fetch failed:', [
                'status' => $response->status(),
                'body' => $response->body(),
                'json' => $response->json()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Kantapong Token fetch error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $this->email
            ]);
            return null;
        }
    }

    public function getClientsData(): array
    {
        try {
            // Check cache first
            $cacheKey = 'kantapong_exness_clients_data';
            $cachedData = Cache::get($cacheKey);

            if ($cachedData) {
                Log::info('Kantapong Using cached clients data');
                return $cachedData;
            }

            $token = $this->retrieveToken();
            if (!$token) {
                return ['error' => 'ไม่สามารถรับ token ได้'];
            }

            // Get data from both APIs with reduced timeout
            $dataV1 = $this->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/reports/clients/",
                'v1'
            );

            $dataV2 = $this->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/v2/reports/clients/",
                'v2'
            );

            // Combine and process data
            $combinedData = [];
            
            if (isset($dataV1['data']) && is_array($dataV1['data'])) {
                $combinedData = array_merge($combinedData, $dataV1['data']);
            }
            
            if (isset($dataV2['data']) && is_array($dataV2['data'])) {
                $combinedData = array_merge($combinedData, $dataV2['data']);
            }

            // Remove duplicates based on client_uid
            $uniqueData = [];
            $seenUids = [];
            
            foreach ($combinedData as $client) {
                $uid = $client['client_uid'] ?? null;
                if ($uid && !in_array($uid, $seenUids)) {
                    $uniqueData[] = $client;
                    $seenUids[] = $uid;
                }
            }

            $result = [
                'data' => $uniqueData,
                'total_count' => count($uniqueData),
                'v1_count' => isset($dataV1['data']) ? count($dataV1['data']) : 0,
                'v2_count' => isset($dataV2['data']) ? count($dataV2['data']) : 0,
                'combined_count' => count($combinedData),
                'unique_count' => count($uniqueData)
            ];

            // Cache the result for 5 minutes
            Cache::put($cacheKey, $result, 300);

            Log::info('Kantapong Clients data retrieved successfully', [
                'total_clients' => count($uniqueData),
                'v1_count' => $result['v1_count'],
                'v2_count' => $result['v2_count']
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Kantapong getClientsData error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()];
        }
    }

    public function getClientsFromUrl(string $url, string $context = 'default'): array
    {
        try {
            $token = $this->retrieveToken();
            if (!$token) {
                return ['error' => 'ไม่สามารถรับ token ได้'];
            }

            Log::info("Kantapong Fetching clients from {$context} API", ['url' => $url]);

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Origin' => 'https://my.exnessaffiliates.com',
                'Referer' => 'https://my.exnessaffiliates.com/'
            ])->get($url);

            Log::info("Kantapong {$context} API response:", [
                'url' => $url,
                'status' => $response->status(),
                'has_data' => isset($response->json()['data']),
                'data_count' => isset($response->json()['data']) ? count($response->json()['data']) : 0
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("Kantapong {$context} API successful", [
                    'data_count' => isset($data['data']) ? count($data['data']) : 0
                ]);
                return $data;
            }

            Log::error("Kantapong {$context} API failed:", [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return ['error' => "API {$context} failed with status: " . $response->status()];

        } catch (\Exception $e) {
            Log::error("Kantapong {$context} API error:", [
                'url' => $url,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => "เกิดข้อผิดพลาดในการเรียก API {$context}: " . $e->getMessage()];
        }
    }

    public function testConnection(): array
    {
        try {
            $token = $this->retrieveToken();
            
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'ไม่สามารถรับ token ได้',
                    'step' => 'token_retrieval'
                ];
            }

            // Test API connection
            $testResponse = Http::timeout(30)->withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Origin' => 'https://my.exnessaffiliates.com',
                'Referer' => 'https://my.exnessaffiliates.com/'
            ])->get('https://my.exnessaffiliates.com/api/v2/reports/clients/');

            if ($testResponse->successful()) {
                $data = $testResponse->json();
                $clientCount = isset($data['data']) ? count($data['data']) : 0;
                
                return [
                    'success' => true,
                    'message' => "เชื่อมต่อสำเร็จ - พบข้อมูลลูกค้า {$clientCount} รายการ",
                    'client_count' => $clientCount,
                    'step' => 'api_test'
                ];
            }

            return [
                'success' => false,
                'message' => 'API test failed: ' . $testResponse->status(),
                'step' => 'api_test'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'step' => 'exception'
            ];
        }
    }
} 