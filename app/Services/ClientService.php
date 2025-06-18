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
            Log::info('=== STARTING CLIENT DATA SYNC ===');
            
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

            Log::info('API Data Received:', [
                'v1_count' => count($v1Clients),
                'v2_count' => count($v2Clients)
            ]);

            // Log sample data from both APIs
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

            // Create V2 lookup map
            $v2Map = [];
            foreach ($v2Clients as $client) {
                if (isset($client['client_uid'])) {
                    $v2Uid = $client['client_uid'];
                    
                    // Extract short UID from V2 UUID format
                    // V2 format: "3bf4e479-a9df-422a-a0a9-2c1376488f15"
                    // V1 format: "3bf4e479"
                    $shortUid = explode('-', $v2Uid)[0] ?? $v2Uid;
                    
                    // Store with short UID as key for matching with V1
                    $v2Map[$shortUid] = $client;
                    
                    Log::info("V2 Mapping: {$v2Uid} -> {$shortUid}", [
                        'client_status' => $client['client_status'] ?? 'NO_STATUS'
                    ]);
                }
            }

            Log::info('V2 Map Created:', [
                'v2_map_count' => count($v2Map),
                'v2_map_sample' => array_slice($v2Map, 0, 2, true),
                'v2_uid_example' => array_keys(array_slice($v2Map, 0, 1, true))[0] ?? 'none'
            ]);

            // Get existing clients for comparison
            $existingClients = Client::pluck('client_uid')->toArray();
            Log::info('Existing clients in database:', [
                'count' => count($existingClients),
                'sample' => array_slice($existingClients, 0, 5)
            ]);

            // Process and save data - UPDATE EXISTING AND ADD NEW
            $updatedCount = 0;
            $newCount = 0;
            $allUids = array_column($v1Clients, 'client_uid');
            $uniqueUids = array_unique($allUids);
            Log::info('V1 client_uid count', [
                'total' => count($allUids),
                'unique' => count($uniqueUids),
                'duplicates' => array_diff_assoc($allUids, $uniqueUids)
            ]);
            
            foreach ($v1Clients as $index => $v1Client) {
                $clientUid = $v1Client['client_uid'] ?? null;
                if (!$clientUid) continue;

                // Get V2 data for this client using short UID
                $v2Data = $v2Map[$clientUid] ?? [];
                
                // Log the merge process for first few clients
                if (($updatedCount + $newCount) < 3) {
                    Log::info("Processing client {$index}: {$clientUid}", [
                        'v1_status' => $v1Client['client_status'] ?? 'NOT_IN_V1',
                        'v2_status' => $v2Data['client_status'] ?? 'NOT_IN_V2',
                        'v2_data_exists' => !empty($v2Data),
                        'v2_uid_in_map' => array_key_exists($clientUid, $v2Map),
                        'v2_map_keys_sample' => array_slice(array_keys($v2Map), 0, 5),
                        'v2_map_count' => count($v2Map)
                    ]);
                }

                // Merge V1 and V2 data
                $mergedData = array_merge($v1Client, $v2Data);

                // Determine final status
                $finalStatus = 'UNKNOWN';
                if (!empty($v2Data) && isset($v2Data['client_status'])) {
                    $finalStatus = strtoupper($v2Data['client_status']);
                } elseif (isset($v1Client['client_status'])) {
                    $finalStatus = strtoupper($v1Client['client_status']);
                }

                // Prepare client data
                $clientData = [
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
                ];

                // Check if client exists
                $existingClient = Client::where('client_uid', $clientUid)->first();
                
                if ($existingClient) {
                    // Update existing client
                    $existingClient->update($clientData);
                    $updatedCount++;
                    
                    if ($updatedCount <= 3) {
                        Log::info("Updated client {$updatedCount}: {$clientUid}", [
                            'final_status' => $existingClient->client_status,
                            'v1_status' => $v1Client['client_status'] ?? 'NOT_IN_V1',
                            'v2_status' => $v2Data['client_status'] ?? 'NOT_IN_V2',
                            'used_short_uid' => $clientUid
                        ]);
                    }
                } else {
                    // Create new client
                    Client::create($clientData);
                    $newCount++;
                    
                    if ($newCount <= 3) {
                        Log::info("Created new client {$newCount}: {$clientUid}", [
                            'final_status' => $finalStatus,
                            'v1_status' => $v1Client['client_status'] ?? 'NOT_IN_V1',
                            'v2_status' => $v2Data['client_status'] ?? 'NOT_IN_V2',
                            'used_short_uid' => $clientUid
                        ]);
                    }
                }
            }

            Log::info('=== SYNC COMPLETED ===', [
                'total_updated' => $updatedCount,
                'total_new' => $newCount,
                'total_processed' => $updatedCount + $newCount,
                'v1_clients' => count($v1Clients),
                'v2_clients' => count($v2Clients)
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
                    'client_uids' => array_slice($v1Uids, 0, 10)
                ],
                'v2_api' => [
                    'total_clients' => count($v2Clients),
                    'sample_client' => $v2Clients[0] ?? null,
                    'available_fields' => $v2Clients[0] ? array_keys($v2Clients[0]) : [],
                    'client_uids' => array_slice($v2Uids, 0, 10)
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