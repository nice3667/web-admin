<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClientService
{
    protected $exnessAuthService;

    public function __construct(ExnessAuthService $exnessAuthService)
    {
        $this->exnessAuthService = $exnessAuthService;
    }

    public function syncClients()
    {
        try {
            Log::info('=== STARTING CLIENT DATA SYNC (V1 ONLY) ===');

            // Fetch v1 and v2 clients
            $v1Response = $this->exnessAuthService->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/reports/clients/",
                'v1'
            );
            $v2Response = $this->exnessAuthService->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/v2/reports/clients/",
                'v2'
            );

            $v1Clients = $v1Response['data'] ?? [];
            $v2Clients = $v2Response['data'] ?? [];

            // Build v2 map for lookup by client_uid (first 8 chars)
            $v2Map = [];
            foreach ($v2Clients as $c) {
                if (isset($c['client_uid'])) {
                    $v2UidShort = substr($c['client_uid'], 0, 8);
                    $v2Map[$v2UidShort] = $c;
                }
            }

            // Debug: Log sample data from both APIs
            if (!empty($v1Clients)) {
                Log::info('V1 Sample Data:', [
                    'first_client' => $v1Clients[0],
                    'v1_fields' => array_keys($v1Clients[0])
                ]);
            }
            if (!empty($v2Clients)) {
                Log::info('V2 Sample Data:', [
                    'first_client' => $v2Clients[0],
                    'v2_fields' => array_keys($v2Clients[0])
                ]);
            }

            Log::info('V2 Map Summary:', [
                'v2_clients_count' => count($v2Clients),
                'v2_map_keys' => count($v2Map),
                'sample_v2_keys' => array_slice(array_keys($v2Map), 0, 5)
            ]);

            // Clear existing data and start fresh
            Client::truncate();
            Log::info('Cleared existing client data');
            $processedCount = 0;
            $uniqueUids = [];
            $statusFoundCount = 0;

            foreach ($v1Clients as $v1) {
                $clientUid = $v1['client_uid'] ?? null;
                $clientAccount = $v1['client_account'] ?? null;
                if (!$clientUid || !$clientAccount) continue;

                // Match by client_uid 8 ตัวแรกเท่านั้น
                $v2 = $v2Map[$clientUid] ?? [];

                // Debug: Log merge process for first few records
                if ($processedCount < 3) {
                    Log::info("Processing client {$processedCount}:", [
                        'client_uid' => $clientUid,
                        'client_account' => $clientAccount,
                        'key' => $clientUid,
                        'has_v2_data' => !empty($v2),
                        'v1_status' => $v1['client_status'] ?? 'NOT_FOUND',
                        'v2_status' => $v2['client_status'] ?? 'NOT_FOUND'
                    ]);
                }

                // merge: ใช้ข้อมูล v1 เป็นหลัก
                $merged = $v1;
                // ดึงเฉพาะ client_status, kyc_passed, ftd_received, ftt_made จาก v2
                if (!empty($v2)) {
                    $merged['client_status'] = $v2['client_status'] ?? $merged['client_status'] ?? 'UNKNOWN';
                    $merged['kyc_passed'] = $v2['kyc_passed'] ?? $merged['kyc_passed'] ?? false;
                    $merged['ftd_received'] = $v2['ftd_received'] ?? $merged['ftd_received'] ?? false;
                    $merged['ftt_made'] = $v2['ftt_made'] ?? $merged['ftt_made'] ?? false;
                }
                $finalStatus = strtoupper($merged['client_status'] ?? 'UNKNOWN');
                if (!empty($v2['client_status'])) {
                    $statusFoundCount++;
                }

                $clientData = [
                    'client_uid' => $clientUid,
                    'partner_account' => $merged['partner_account'] ?? null,
                    'client_id' => $clientAccount,
                    'reg_date' => $merged['reg_date'] ?? $merged['registration_date'] ?? null,
                    'client_country' => $merged['client_country'] ?? $merged['country'] ?? null,
                    'volume_lots' => $merged['volume_lots'] ?? 0,
                    'volume_mln_usd' => $merged['volume_mln_usd'] ?? 0,
                    'reward_usd' => $merged['reward_usd'] ?? 0,
                    'client_status' => $finalStatus,
                    'kyc_passed' => $merged['kyc_passed'] ?? false,
                    'ftd_received' => $merged['ftd_received'] ?? false,
                    'ftt_made' => $merged['ftt_made'] ?? false,
                    'rebate_amount_usd' => $merged['rebate_amount_usd'] ?? 0,
                    'raw_data' => $merged,
                    'last_sync_at' => now()
                ];

                Client::create($clientData);
                $processedCount++;
                $uniqueUids[$clientUid] = true;

                if ($processedCount <= 5) {
                    Log::info("Processed client {$processedCount}: {$clientUid} (Account: {$clientAccount})", [
                        'status' => $clientData['client_status'],
                        'volume_lots' => $clientData['volume_lots'],
                        'volume_mln_usd' => $clientData['volume_mln_usd'],
                        'reward_usd' => $clientData['reward_usd'],
                        'country' => $clientData['client_country'],
                        'client_account' => $clientAccount,
                        'has_v2_data' => !empty($v2),
                        'final_status' => $finalStatus
                    ]);
                }
            }

            Log::info('=== CLIENT DATA SYNC SUMMARY ===', [
                'total_accounts' => $processedCount,
                'unique_client_uids' => count($uniqueUids),
                'status_found_from_v2' => $statusFoundCount,
                'status_not_found' => $processedCount - $statusFoundCount
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('=== SYNC ERROR ===', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function getClients($filters = [])
    {
        try {
            // Check cache first for filtered results
            $cacheKey = 'clients_filtered_' . md5(serialize($filters));
            $cachedData = \Cache::get($cacheKey);

            if ($cachedData) {
                Log::info('Using cached filtered clients data');
                return $cachedData;
            }

            $query = Client::query();

            if (!empty($filters['partner_account'])) {
                $query->byPartnerAccount($filters['partner_account']);
            }

            if (!empty($filters['client_country'])) {
                $query->byCountry($filters['client_country']);
            }

            if (!empty($filters['client_status'])) {
                $query->where('client_status', $filters['client_status']);
            }

            if (!empty($filters['kyc_passed'])) {
                $query->kycPassed();
            }

            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $query->byDateRange($filters['start_date'], $filters['end_date']);
            }

            $clients = $query->orderBy('reg_date', 'desc')->get();

            Log::info('Retrieved clients', [
                'count' => $clients->count(),
                'filters' => $filters
            ]);

            // Cache the result for 2 minutes
            \Cache::put($cacheKey, $clients, 120);

            return $clients;

        } catch (\Exception $e) {
            Log::error('Error retrieving clients:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]);
        }
    }

    public function getClientStats()
    {
        try {
            // Check cache first for stats
            $cacheKey = 'client_stats';
            $cachedStats = \Cache::get($cacheKey);

            if ($cachedStats) {
                Log::info('Using cached client stats');
                return $cachedStats;
            }

            $stats = [
                'total_clients' => Client::count(),
                'active_clients' => Client::active()->count(),
                'total_volume_lots' => Client::sum('volume_lots'),
                'total_volume_usd' => Client::sum('volume_mln_usd'),
                'total_reward_usd' => Client::sum('reward_usd'),
                'kyc_passed_count' => Client::kycPassed()->count(),
                'ftd_received_count' => Client::ftdReceived()->count(),
                'ftt_made_count' => Client::fttMade()->count(),
                'last_sync' => Client::max('last_sync_at')
            ];

            Log::info('Retrieved client stats', $stats);

            // Cache the stats for 5 minutes
            \Cache::put($cacheKey, $stats, 300);

            return $stats;

        } catch (\Exception $e) {
            Log::error('Error retrieving client stats:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    public function getRawApiData()
    {
        try {
            Log::info('Fetching raw API data for debugging');

            // Get data from both Exness API versions
            $v1Response = $this->exnessAuthService->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/reports/clients/",
                'v1'
            );

            $v2Response = $this->exnessAuthService->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/v2/reports/clients/",
                'v2'
            );

            $v1Clients = $v1Response['data'] ?? [];
            $v2Clients = $v2Response['data'] ?? [];

            // Get client_uid lists from both APIs
            $v1Uids = array_column($v1Clients, 'client_uid');
            $v2Uids = array_column($v2Clients, 'client_uid');

            // Find matching and non-matching UIDs
            $matchingUids = array_intersect($v1Uids, $v2Uids);
            $v1OnlyUids = array_diff($v1Uids, $v2Uids);
            $v2OnlyUids = array_diff($v2Uids, $v1Uids);

            return [
                'v1_api' => [
                    'total_clients' => count($v1Clients),
                    'sample_client' => $v1Clients[0] ?? null,
                    'available_fields' => $v1Clients[0] ? array_keys($v1Clients[0]) : [],
                    'client_uids' => array_slice($v1Uids, 0, 10),
                    'data' => $v1Clients,
                ],
                'v2_api' => [
                    'total_clients' => count($v2Clients),
                    'sample_client' => $v2Clients[0] ?? null,
                    'available_fields' => $v2Clients[0] ? array_keys($v2Clients[0]) : [],
                    'client_uids' => array_slice($v2Uids, 0, 10),
                    'data' => $v2Clients,
                ],
                'matching_analysis' => [
                    'matching_uids_count' => count($matchingUids),
                    'v1_only_count' => count($v1OnlyUids),
                    'v2_only_count' => count($v2OnlyUids),
                    'matching_uids_sample' => array_slice($matchingUids, 0, 5),
                    'v1_only_sample' => array_slice($v1OnlyUids, 0, 5),
                    'v2_only_sample' => array_slice($v2OnlyUids, 0, 5)
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error fetching raw API data:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Sync only new clients (don't update existing ones)
     */
    public function syncNewClients()
    {
        try {
            Log::info('=== STARTING NEW CLIENTS SYNC ===');

            // Get data from both APIs
            $v1Response = $this->exnessAuthService->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/reports/clients/",
                'v1'
            );

            $v2Response = $this->exnessAuthService->getClientsFromUrl(
                "https://my.exnessaffiliates.com/api/v2/reports/clients/",
                'v2'
            );

            if (isset($v1Response['error'])) {
                Log::error('V1 API Error:', ['error' => $v1Response['error']]);
                return false;
            }

            if (isset($v2Response['error'])) {
                Log::error('V2 API Error:', ['error' => $v2Response['error']]);
                return false;
            }

            $v1Clients = $v1Response['data'] ?? [];
            $v2Clients = $v2Response['data'] ?? [];

            // Create V2 lookup map
            $v2Map = [];
            foreach ($v2Clients as $client) {
                if (isset($client['client_uid'])) {
                    $v2Uid = $client['client_uid'];
                    $shortUid = explode('-', $v2Uid)[0] ?? $v2Uid;
                    $v2Map[$shortUid] = $client;
                }
            }

            // Get existing client UIDs
            $existingUids = Client::pluck('client_uid')->toArray();

            // Process only new clients
            $newCount = 0;
            foreach ($v1Clients as $v1Client) {
                $clientUid = $v1Client['client_uid'] ?? null;
                if (!$clientUid || in_array($clientUid, $existingUids)) {
                    continue; // Skip if client already exists
                }

                // Get V2 data for this client
                $v2Data = $v2Map[$clientUid] ?? [];
                $mergedData = array_merge($v1Client, $v2Data);

                // Determine final status
                $finalStatus = 'UNKNOWN';
                if (!empty($v2Data) && isset($v2Data['client_status'])) {
                    $finalStatus = strtoupper($v2Data['client_status']);
                } elseif (isset($v1Client['client_status'])) {
                    $finalStatus = strtoupper($v1Client['client_status']);
                }

                // Create new client
                Client::create([
                    'client_uid' => $clientUid,
                    'partner_account' => $mergedData['partner_account'] ?? null,
                    'client_id' => $mergedData['client_id'] ?? null,
                    'reg_date' => $mergedData['reg_date'] ?? $mergedData['registration_date'] ?? null,
                    'client_country' => $mergedData['client_country'] ?? $mergedData['country'] ?? null,
                    'volume_lots' => $mergedData['volume_lots'] ?? 0,
                    'volume_mln_usd' => $mergedData['volume_mln_usd'] ?? 0,
                    'reward_usd' => $mergedData['reward_usd'] ?? 0,
                    'client_status' => $finalStatus,
                    'kyc_passed' => $mergedData['kyc_passed'] ?? false,
                    'ftd_received' => $mergedData['ftd_received'] ?? false,
                    'ftt_made' => $mergedData['ftt_made'] ?? false,
                    'rebate_amount_usd' => $mergedData['rebate_amount_usd'] ?? 0,
                    'raw_data' => $mergedData,
                    'last_sync_at' => now()
                ]);

                $newCount++;

                if ($newCount <= 5) {
                    Log::info("Added new client {$newCount}: {$clientUid}", [
                        'final_status' => $finalStatus,
                        'country' => $mergedData['client_country'] ?? $mergedData['country'] ?? 'Unknown'
                    ]);
                }
            }

            Log::info('=== NEW CLIENTS SYNC COMPLETED ===', [
                'new_clients_added' => $newCount,
                'total_api_clients' => count($v1Clients),
                'existing_clients' => count($existingUids)
            ]);

            return [
                'success' => true,
                'new_clients_added' => $newCount,
                'total_api_clients' => count($v1Clients),
                'existing_clients' => count($existingUids)
            ];

        } catch (\Exception $e) {
            Log::error('=== NEW CLIENTS SYNC ERROR ===', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get sync statistics
     */
    public function getSyncStats()
    {
        try {
            $stats = [
                'total_clients' => Client::count(),
                'last_sync' => Client::max('last_sync_at'),
                'clients_synced_today' => Client::whereDate('last_sync_at', today())->count(),
                'clients_synced_this_week' => Client::whereBetween('last_sync_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'status_distribution' => Client::selectRaw('client_status, COUNT(*) as count')
                    ->groupBy('client_status')
                    ->get()
                    ->pluck('count', 'client_status')
                    ->toArray(),
                'countries_distribution' => Client::selectRaw('client_country, COUNT(*) as count')
                    ->whereNotNull('client_country')
                    ->groupBy('client_country')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get()
                    ->pluck('count', 'client_country')
                    ->toArray()
            ];

            return $stats;

        } catch (\Exception $e) {
            Log::error('Error getting sync stats:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
}
