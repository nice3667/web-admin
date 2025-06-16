<?php

namespace App\Services;

use App\Models\User;
use App\Models\ExnessUser;
use App\Models\ExnessClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ExnessSyncService
{
    private const CACHE_PREFIX = 'exness_data_';
    private const CACHE_DURATION = 3600; // 1 hour
    private const TOKEN_DURATION = 6 * 3600; // 6 hours

    public function __construct()
    {
        // Constructor
    }

    /**
     * Sync user Exness data during login
     */
    public function syncUserDataOnLogin($user, $email, $password)
    {
        try {
            Log::info('Starting Exness sync for user', ['user_id' => $user->id, 'email' => $email]);

            // Get or create ExnessUser record
            $exnessUser = $this->getOrCreateExnessUser($user, $email, $password);
            
            // Get fresh token
            $token = $this->getOrRefreshToken($exnessUser);
            
            if (!$token) {
                Log::warning('Could not get Exness token for user', ['user_id' => $user->id]);
                return false;
            }

            // Sync client data
            $syncResult = $this->syncClientData($exnessUser, $token);
            
            Log::info('Exness sync completed', [
                'user_id' => $user->id,
                'success' => $syncResult,
                'clients_synced' => $exnessUser->clients()->count()
            ]);

            return $syncResult;

        } catch (\Exception $e) {
            Log::error('Error during Exness sync', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get or create ExnessUser record
     */
    private function getOrCreateExnessUser($user, $email, $password)
    {
        $exnessUser = $user->exnessUser;
        
        if (!$exnessUser) {
            $exnessUser = ExnessUser::create([
                'user_id' => $user->id,
                'exness_email' => $email,
                'exness_password' => $password, // Will be encrypted by mutator
                'is_active' => true
            ]);
            Log::info('Created new ExnessUser record', ['user_id' => $user->id]);
        } else {
            // Update credentials if they changed
            $exnessUser->update([
                'exness_email' => $email,
                'exness_password' => $password,
                'is_active' => true
            ]);
            Log::info('Updated ExnessUser credentials', ['user_id' => $user->id]);
        }

        return $exnessUser;
    }

    /**
     * Get or refresh Exness token
     */
    private function getOrRefreshToken($exnessUser)
    {
        // Check if current token is still valid
        if ($exnessUser->isTokenValid()) {
            Log::info('Using existing valid token', ['exness_user_id' => $exnessUser->id]);
            return $exnessUser->access_token;
        }

        // Get new token from API
        Log::info('Getting new token from Exness API', ['exness_user_id' => $exnessUser->id]);
        
        $response = Http::timeout(30)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://my.exnessaffiliates.com/api/v2/auth/', [
            'login' => $exnessUser->exness_email,
            'password' => $exnessUser->exness_password
        ]);

        if ($response->successful()) {
            $tokenData = $response->json();
            $token = $tokenData['token'] ?? null;
            
            if ($token) {
                // Update token in database
                $exnessUser->update([
                    'access_token' => $token,
                    'token_expires_at' => now()->addSeconds(self::TOKEN_DURATION)
                ]);
                
                Log::info('Token refreshed successfully', ['exness_user_id' => $exnessUser->id]);
                return $token;
            }
        }

        Log::error('Failed to get Exness token', [
            'exness_user_id' => $exnessUser->id,
            'response_status' => $response->status(),
            'response_body' => $response->body()
        ]);

        return null;
    }

    /**
     * Sync client data from APIs
     */
    private function syncClientData($exnessUser, $token)
    {
        try {
            // Get data from both V1 and V2 APIs
            $v1Response = $this->callExnessAPI('https://my.exnessaffiliates.com/api/reports/clients/?limit=100', $token);
            $v2Response = $this->callExnessAPI('https://my.exnessaffiliates.com/api/v2/reports/clients/?limit=100', $token);

            $v1Data = $v1Response && $v1Response->successful() ? $v1Response->json() : null;
            $v2Data = $v2Response && $v2Response->successful() ? $v2Response->json() : null;

            // Store raw API responses
            $exnessUser->update([
                'api_response_v1' => $v1Data,
                'api_response_v2' => $v2Data
            ]);

            // Process and store client data
            $this->processClientData($exnessUser, $v1Data, $v2Data);
            
            // Mark as synced
            $exnessUser->markSynced();

            // Clear cache for this user
            $this->clearUserCache($exnessUser->user_id);

            return true;

        } catch (\Exception $e) {
            Log::error('Error syncing client data', [
                'exness_user_id' => $exnessUser->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Process and store client data
     */
    private function processClientData($exnessUser, $v1Data, $v2Data)
    {
        // Create V2 lookup map for status and rebate data
        $v2Map = [];
        if ($v2Data && isset($v2Data['data'])) {
            foreach ($v2Data['data'] as $client) {
                if (isset($client['client_uid'])) {
                    $shortUid = substr($client['client_uid'], 0, 8);
                    $v2Map[$shortUid] = [
                        'client_status' => strtoupper($client['client_status'] ?? 'UNKNOWN'),
                        'rebate_amount_usd' => $client['rebate_amount_usd'] ?? 0
                    ];
                }
            }
        }

        // Process V1 data (main client data)
        if ($v1Data && isset($v1Data['data'])) {
            foreach ($v1Data['data'] as $clientData) {
                $clientUid = $clientData['client_uid'] ?? null;
                if (!$clientUid) continue;

                // Get V2 data for this client
                $v2Info = $v2Map[$clientUid] ?? ['client_status' => 'UNKNOWN', 'rebate_amount_usd' => 0];

                // Update or create client record
                ExnessClient::updateOrCreate(
                    [
                        'exness_user_id' => $exnessUser->id,
                        'client_uid' => $clientUid
                    ],
                    [
                        'client_name' => $clientData['client_name'] ?? null,
                        'client_email' => $clientData['client_email'] ?? null,
                        'client_status' => $v2Info['client_status'],
                        'volume_lots' => $clientData['volume_lots'] ?? 0,
                        'volume_mln_usd' => $clientData['volume_mln_usd'] ?? 0,
                        'reward_usd' => $clientData['reward_usd'] ?? 0,
                        'rebate_amount_usd' => $v2Info['rebate_amount_usd'],
                        'currency' => $clientData['currency'] ?? 'USD',
                        'reg_date' => isset($clientData['reg_date']) ? Carbon::parse($clientData['reg_date']) : null,
                        'last_activity' => isset($clientData['last_activity']) ? Carbon::parse($clientData['last_activity']) : null,
                        'raw_data_v1' => $clientData,
                        'raw_data_v2' => $v2Map[$clientUid] ?? null,
                        'synced_at' => now()
                    ]
                );
            }
        }

        Log::info('Client data processed', [
            'exness_user_id' => $exnessUser->id,
            'v1_count' => $v1Data ? count($v1Data['data'] ?? []) : 0,
            'v2_count' => $v2Data ? count($v2Data['data'] ?? []) : 0,
            'total_clients' => $exnessUser->clients()->count()
        ]);
    }

    /**
     * Call Exness API with retry logic
     */
    private function callExnessAPI($url, $token, $maxRetries = 2)
    {
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::timeout(30)->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'JWT ' . $token,
                ])->get($url);

                if ($response->successful()) {
                    return $response;
                }

                Log::warning("Exness API call attempt {$attempt} failed", [
                    'url' => $url,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

            } catch (\Exception $e) {
                Log::warning("Exness API call attempt {$attempt} error", [
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
            }

            if ($attempt < $maxRetries) {
                sleep(1); // Wait 1 second before retry
            }
        }

        return null;
    }

    /**
     * Get cached client data for user
     */
    public function getCachedClientData($userId)
    {
        $cacheKey = self::CACHE_PREFIX . "clients_{$userId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId) {
            return ExnessClient::forUser($userId)->get();
        });
    }

    /**
     * Get cached statistics for user
     */
    public function getCachedStats($userId)
    {
        $cacheKey = self::CACHE_PREFIX . "stats_{$userId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId) {
            return ExnessClient::getStatsForUser($userId);
        });
    }

    /**
     * Clear cache for user
     */
    public function clearUserCache($userId)
    {
        Cache::forget(self::CACHE_PREFIX . "clients_{$userId}");
        Cache::forget(self::CACHE_PREFIX . "stats_{$userId}");
    }

    /**
     * Check if user needs data sync
     */
    public function userNeedsSync($userId, $hours = 6)
    {
        $user = User::find($userId);
        if (!$user || !$user->exnessUser) {
            return true;
        }

        return $user->exnessUser->needsSync($hours);
    }
} 