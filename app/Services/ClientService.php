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
            Log::info('Starting client data synchronization');
            
            // Get data from Exness API
            $response = $this->exnessAuthService->getClientsData();
            
            if (isset($response['error'])) {
                Log::error('Error fetching client data:', ['error' => $response['error']]);
                return false;
            }

            $clients = $response['data'] ?? [];
            
            if (empty($clients)) {
                Log::warning('No client data received from API');
                return false;
            }

            Log::info('Received client data', [
                'count' => count($clients),
                'sample' => $clients[0] ?? null
            ]);

            // Start transaction
            DB::beginTransaction();

            try {
                $updatedCount = 0;
                $createdCount = 0;

                foreach ($clients as $clientData) {
                    $result = $this->updateOrCreateClient($clientData);
                    if ($result) {
                        if ($result->wasRecentlyCreated) {
                            $createdCount++;
                        } else {
                            $updatedCount++;
                        }
                    }
                }

                DB::commit();
                Log::info('Client data synchronization completed successfully', [
                    'created' => $createdCount,
                    'updated' => $updatedCount,
                    'total' => count($clients)
                ]);
                return true;

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error during client data synchronization:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Client synchronization failed:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    protected function updateOrCreateClient($clientData)
    {
        try {
            $clientUid = $clientData['client_uid'] ?? $clientData['client_id'] ?? null;
            
            if (!$clientUid) {
                Log::warning('Client data missing UID', ['data' => $clientData]);
                return null;
            }

            // Log the data being processed
            Log::debug('Processing client data:', [
                'client_uid' => $clientUid,
                'raw_data' => $clientData
            ]);

            $client = Client::updateOrCreate(
                ['client_uid' => $clientUid],
                [
                    'partner_account' => $clientData['partner_account'] ?? null,
                    'client_id' => $clientData['client_id'] ?? null,
                    'reg_date' => $clientData['reg_date'] ?? $clientData['registration_date'] ?? null,
                    'client_country' => $clientData['client_country'] ?? $clientData['country'] ?? null,
                    'volume_lots' => $clientData['volume_lots'] ?? 0,
                    'volume_mln_usd' => $clientData['volume_mln_usd'] ?? 0,
                    'reward_usd' => $clientData['reward_usd'] ?? 0,
                    'client_status' => $clientData['client_status'] ?? 'UNKNOWN',
                    'kyc_passed' => $clientData['kyc_passed'] ?? false,
                    'ftd_received' => $clientData['ftd_received'] ?? false,
                    'ftt_made' => $clientData['ftt_made'] ?? false,
                    'raw_data' => $clientData,
                    'last_sync_at' => now()
                ]
            );

            Log::info('Client updated/created', [
                'client_uid' => $clientUid,
                'status' => $client->client_status,
                'was_recently_created' => $client->wasRecentlyCreated
            ]);

            return $client;

        } catch (\Exception $e) {
            Log::error('Error updating/creating client:', [
                'client_uid' => $clientUid ?? 'unknown',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
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
} 