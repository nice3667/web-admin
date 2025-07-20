<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class HamExnessAuthService
{
    private string $email = 'hamsftmo@gmail.com';
    private string $password = 'Ham@240446';

    public function retrieveToken(): ?string
    {
        try {
            Log::info('Ham: Attempting to retrieve token', [
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

            Log::info('Ham Token request response:', [
                'url' => 'https://my.exnessaffiliates.com/api/v2/auth/',
                'login' => $this->email,
                'status' => $response->status(),
                'has_token' => isset($response->json()['token']),
                'response_keys' => array_keys($response->json() ?? [])
            ]);

            if ($response->successful()) {
                $token = $response->json()['token'] ?? null;
                if ($token) {
                    Log::info('Ham: Token retrieved successfully', [
                        'token_length' => strlen($token),
                        'token_preview' => substr($token, 0, 20) . '...'
                    ]);
                    return $token;
                } else {
                    Log::error('Ham: Token not found in successful response', [
                        'response' => $response->json()
                    ]);
                    return null;
                }
            }

            Log::error('Ham Token fetch failed:', [
                'status' => $response->status(),
                'body' => $response->body(),
                'json' => $response->json()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Ham Token fetch error:', [
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
            $cacheKey = 'ham_exness_clients_data';
            $cachedData = Cache::get($cacheKey);

            if ($cachedData) {
                Log::info('Ham Using cached clients data');
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

            if (isset($dataV1['error'])) {
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
            Log::info('Ham Combined V1+V2 clients data (maximized client count):', [
                'v1_count' => count($dataV1['data']),
                'v2_count' => isset($dataV2['data']) ? count($dataV2['data']) : 0,
                'combined_total_records' => count($combined),
                'unique_client_uids' => count($uniqueUids),
                'sample_data' => $combined[0] ?? null
            ]);

            $result = ['data' => $combined];

            // Cache the result for 5 minutes
            Cache::put($cacheKey, $result, 300);

            return $result;

        } catch (\Exception $e) {
            Log::error('Ham Clients data fetch error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    public function getClientsFromUrl(string $url, string $context = 'default'): array
    {
        try {
            $token = $this->retrieveToken();
            if (!$token) {
                return ['error' => 'ไม่สามารถรับ token ได้'];
            }

            Log::info("Ham Fetching clients from URL ($context)", ['url' => $url]);

            $response = Http::timeout(30) // Reduced from 60 to 30 seconds
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();

                Log::info("Ham API response success ($context)", [
                    'url' => $url,
                    'data_count' => isset($data['data']) ? count($data['data']) : 0,
                    'has_data' => isset($data['data']),
                    'response_keys' => array_keys($data)
                ]);

                return $data;
            } else {
                Log::error("Ham API request failed ($context)", [
                    'url' => $url,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return ['error' => 'API request failed: ' . $response->status()];
            }

        } catch (\Exception $e) {
            Log::error("Ham API request error ($context)", [
                'url' => $url,
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
                    'message' => 'ไม่สามารถรับ JWT Token ได้',
                    'token' => null
                ];
            }

            return [
                'success' => true,
                'message' => 'เชื่อมต่อสำเร็จ',
                'token' => substr($token, 0, 20) . '...' // แสดงแค่ส่วนหน้าของ token
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'token' => null
            ];
        }
    }
}
