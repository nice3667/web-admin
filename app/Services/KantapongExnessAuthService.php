<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class KantapongExnessAuthService
{
    private $baseUrl = 'https://my.exnessaffiliates.com';
    private $email = 'kantapong0592@gmail.com';
    private $password = 'Kantapong.0592z';
    private $cacheKey = 'kantapong_exness_token';

    public function authenticate(): ?string
    {
        try {
            Log::info('Kantapong Exness authentication attempt', [
                'email' => $this->email,
                'base_url' => $this->baseUrl,
                'auth_url' => $this->baseUrl . '/api/v2/auth/'
            ]);

            $response = Http::timeout(30)->post($this->baseUrl . '/api/v2/auth/', [
                'login' => $this->email,
                'password' => $this->password
            ]);

            Log::info('Kantapong API response received', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_length' => strlen($response->body())
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Kantapong API response data', [
                    'has_token' => isset($data['token']),
                    'response_keys' => array_keys($data),
                    'token_length' => isset($data['token']) ? strlen($data['token']) : 0
                ]);

                if (isset($data['token'])) {
                    $token = $data['token'];

                    // Cache token for 50 minutes (expires in 60 minutes)
                    Cache::put($this->cacheKey, $token, now()->addMinutes(50));

                    Log::info('Kantapong Exness authentication successful', [
                        'token_length' => strlen($token),
                        'expires_at' => now()->addMinutes(50)
                    ]);

                    return $token;
                }
            }

            Log::error('Kantapong Exness authentication failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'headers' => $response->headers()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Kantapong Exness authentication error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function retrieveToken(): ?string
    {
        // Try to get cached token first
        $token = Cache::get($this->cacheKey);

        if ($token) {
            Log::info('Kantapong Using cached Exness token');
            return $token;
        }

        // If no cached token, authenticate
        return $this->authenticate();
    }

    public function getClientsFromUrl(string $url, string $context = 'default'): array
    {
        try {
            $token = $this->retrieveToken();
            if (!$token) {
                return ['error' => 'ไม่สามารถรับ token ได้'];
            }

            Log::info("Kantapong Fetching clients from URL ($context)", ['url' => $url]);

            $response = Http::timeout(30) // Reduced from 60 to 30 seconds
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();

                Log::info("Kantapong API response success ($context)", [
                    'url' => $url,
                    'data_count' => isset($data['data']) ? count($data['data']) : 0,
                    'has_data' => isset($data['data']),
                    'response_keys' => array_keys($data)
                ]);

                return $data;
            } else {
                Log::error("Kantapong API request failed ($context)", [
                    'url' => $url,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return ['error' => 'API request failed: ' . $response->status()];
            }

        } catch (\Exception $e) {
            Log::error("Kantapong API request error ($context)", [
                'url' => $url,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ['error' => $e->getMessage()];
        }
    }

    public function getClientsData(): array
    {
        try {
            Log::info('Kantapong getClientsData started');

            // Check cache first
            $cacheKey = 'kantapong_exness_clients_data';
            $cachedData = \Cache::get($cacheKey);

            if ($cachedData) {
                Log::info('Kantapong Using cached clients data');
                return $cachedData;
            }

            Log::info('Kantapong No cached data, retrieving token...');
            $token = $this->retrieveToken();

            if (!$token) {
                Log::error('Kantapong Failed to retrieve token');
                return ['error' => 'ไม่สามารถรับ token ได้'];
            }

            Log::info('Kantapong Token retrieved successfully, fetching data from APIs...');

            // Get data from both APIs with reduced timeout
            $dataV1 = $this->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/reports/clients/",
                'v1'
            );

            $dataV2 = $this->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/v2/reports/clients/",
                'v2'
            );

            Log::info('Kantapong API responses received', [
                'v1_has_error' => isset($dataV1['error']),
                'v2_has_error' => isset($dataV2['error']),
                'v1_data_count' => isset($dataV1['data']) ? count($dataV1['data']) : 0,
                'v2_data_count' => isset($dataV2['data']) ? count($dataV2['data']) : 0
            ]);

            if (isset($dataV1['error'])) {
                Log::error('Kantapong V1 API error', ['error' => $dataV1['error']]);
                return ['error' => $dataV1['error']];
            }

            // Create a map of V1 clients by short UID
            $v1ClientMap = [];
            foreach ($dataV1['data'] as $client) {
                $uid = $client['client_uid'] ?? null;
                if ($uid) {
                    if (!isset($v1ClientMap[$uid])) {
                        $v1ClientMap[$uid] = [];
                    }
                    $v1ClientMap[$uid][] = $client;
                }
            }

            // Add V2 clients that are not in V1 (to maximize client count)
            $combined = $dataV1['data']; // Start with all V1 data

            if (isset($dataV2['data'])) {
                foreach ($dataV2['data'] as $v2Client) {
                    $v2Uid = $v2Client['client_uid'] ?? null;
                    if ($v2Uid) {
                        // Extract short UID from V2 UUID format
                        $shortUid = explode('-', $v2Uid)[0] ?? $v2Uid;

                        // Only add if this client is not in V1
                        if (!isset($v1ClientMap[$shortUid])) {
                            // Convert V2 format to match V1 format
                            $v2ClientConverted = [
                                'id' => null,
                                'partner_account' => $v2Client['partner_account'] ?? '',
                                'partner_account_name' => 'partner',
                                'client_uid' => $shortUid, // Use short UID for consistency
                                'country' => $v2Client['client_country'] ?? '',
                                'client_country' => $v2Client['client_country'] ?? '',
                                'currency' => 'USD',
                                'reg_date' => $v2Client['reg_date'] ?? '',
                                'volume_lots' => $v2Client['volume_lots'] ?? 0,
                                'volume_mln_usd' => $v2Client['volume_mln_usd'] ?? 0,
                                'trade_fn' => $v2Client['trade_fn'] ?? null,
                                'reward' => number_format($v2Client['reward_usd'] ?? 0, 2),
                                'reward_usd' => $v2Client['reward_usd'] ?? 0,
                                'comment' => $v2Client['comment'] ?? null,
                                'client_account' => null,
                                'client_account_type' => 'Standard',
                                'client_status' => $v2Client['client_status'] ?? 'UNKNOWN',
                                'kyc_passed' => $v2Client['kyc_passed'] ?? false,
                                'ftd_received' => $v2Client['ftd_received'] ?? false,
                                'ftt_made' => $v2Client['ftt_made'] ?? false,
                            ];

                            $combined[] = $v2ClientConverted;
                        }
                    }
                }
            }

            // Log the combined data
            $uniqueUids = array_unique(array_column($combined, 'client_uid'));
            Log::info('Kantapong Combined V1+V2 clients data (maximized client count):', [
                'v1_count' => count($dataV1['data']),
                'v2_count' => isset($dataV2['data']) ? count($dataV2['data']) : 0,
                'combined_total_records' => count($combined),
                'unique_client_uids' => count($uniqueUids),
                'sample_data' => $combined[0] ?? null
            ]);

            $result = ['data' => $combined];

            // Cache the result for 5 minutes
            \Cache::put($cacheKey, $result, 300);

            Log::info('Kantapong getClientsData completed successfully');

            return $result;

        } catch (\Exception $e) {
            Log::error('Kantapong Clients data fetch error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    public function testConnection(): array
    {
        try {
            $token = $this->retrieveToken();

            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'ไม่สามารถรับ token ได้'
                ];
            }

            // Test API call
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ])
                ->get($this->baseUrl . '/api/v2/reports/clients/');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'เชื่อมต่อ Exness API สำเร็จ',
                    'data_count' => isset($data['data']) ? count($data['data']) : 0
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'การเชื่อมต่อ API ล้มเหลว: ' . $response->status()
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }
}
