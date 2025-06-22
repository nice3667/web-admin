<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class JanischaExnessAuthService
{
    private $baseUrl = 'https://my.exnessaffiliates.com';
    private $email = 'Janischa.trade@gmail.com';
    private $password = 'Janis@2025';
    private $cacheKey = 'janischa_exness_token';

    public function authenticate(): ?string
    {
        try {
            Log::info('Janischa Exness authentication attempt');

            $response = Http::timeout(30)->post($this->baseUrl . '/api/v2/auth/', [
                'login' => $this->email,
                'password' => $this->password
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['token'])) {
                    $token = $data['token'];
                    
                    // Cache token for 50 minutes (expires in 60 minutes)
                    Cache::put($this->cacheKey, $token, now()->addMinutes(50));
                    
                    Log::info('Janischa Exness authentication successful', [
                        'token_length' => strlen($token),
                        'expires_at' => now()->addMinutes(50)
                    ]);
                    
                    return $token;
                }
            }

            Log::error('Janischa Exness authentication failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            
            return null;

        } catch (\Exception $e) {
            Log::error('Janischa Exness authentication error', [
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
            Log::info('Janischa Using cached Exness token');
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

            Log::info("Janischa Fetching clients from URL ($context)", ['url' => $url]);

            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info("Janischa API response success ($context)", [
                    'url' => $url,
                    'data_count' => isset($data['data']) ? count($data['data']) : 0,
                    'has_data' => isset($data['data']),
                    'response_keys' => array_keys($data)
                ]);

                return $data;
            } else {
                Log::error("Janischa API request failed ($context)", [
                    'url' => $url,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return ['error' => 'API request failed: ' . $response->status()];
            }

        } catch (\Exception $e) {
            Log::error("Janischa API request error ($context)", [
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
            $token = $this->retrieveToken();
            if (!$token) {
                return ['error' => 'ไม่สามารถรับ token ได้'];
            }

            // Get data from both APIs to maximize client count
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

            // Create a map of V1 clients by short UID and ensure country field consistency
            $v1ClientMap = [];
            foreach ($dataV1['data'] as &$client) {
                // Ensure client_country field exists, fallback to 'country' field
                if (!isset($client['client_country']) && isset($client['country'])) {
                    $client['client_country'] = $client['country'];
                }
                
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

            Log::info('Janischa Combined clients data', [
                'v1_count' => count($dataV1['data']),
                'v2_count' => isset($dataV2['data']) ? count($dataV2['data']) : 0,
                'combined_count' => count($combined)
            ]);

            return [
                'data' => $combined,
                'v1_count' => count($dataV1['data']),
                'v2_count' => isset($dataV2['data']) ? count($dataV2['data']) : 0,
                'combined_count' => count($combined)
            ];

        } catch (\Exception $e) {
            Log::error('Janischa Error getting clients data', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ['error' => $e->getMessage()];
        }
    }

    public function testConnection(): array
    {
        try {
            $token = $this->authenticate();
            
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Authentication failed'
                ];
            }

            return [
                'success' => true,
                'message' => 'Connection successful',
                'token_length' => strlen($token)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }
} 