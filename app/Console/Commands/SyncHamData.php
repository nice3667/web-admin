<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HamExnessAuthService;
use App\Models\HamClient;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncHamData extends Command
{
    protected $signature = 'sync:ham-data {--force : Force sync even if recently synced}';
    protected $description = 'Sync Ham client data from Exness API to ham_clients table';

    private $hamExnessService;

    public function __construct(HamExnessAuthService $hamExnessService)
    {
        parent::__construct();
        $this->hamExnessService = $hamExnessService;
    }

    public function handle()
    {
        $this->info('Starting Ham data sync...');
        
        try {
            // Check if we need to sync (avoid too frequent syncing)
            if (!$this->option('force')) {
                $lastSync = HamClient::max('last_sync_at');
                if ($lastSync && Carbon::parse($lastSync)->diffInMinutes(now()) < 30) {
                    $this->info('Data was synced recently. Use --force to override.');
                    return 0;
                }
            }

            // Get data from Exness API
            $this->info('Fetching data from Exness API...');
            $apiResponse = $this->hamExnessService->getClientsData();

            if (isset($apiResponse['error'])) {
                $this->error('API Error: ' . $apiResponse['error']);
                return 1;
            }

            if (!isset($apiResponse['data']) || empty($apiResponse['data'])) {
                $this->error('No data received from API');
                return 1;
            }

            $clients = $apiResponse['data'];
            $this->info('Received ' . count($clients) . ' clients from API');

            // Process and save clients
            $processed = 0;
            $updated = 0;
            $created = 0;

            foreach ($clients as $clientData) {
                try {
                    $clientUid = $clientData['client_uid'] ?? null;
                    if (!$clientUid) {
                        continue;
                    }

                    // Prepare data for database
                    $dbData = [
                        'partner_account' => $clientData['partner_account'] ?? null,
                        'client_uid' => $clientUid,
                        'client_id' => $clientData['client_id'] ?? null,
                        'reg_date' => isset($clientData['reg_date']) ? Carbon::parse($clientData['reg_date'])->format('Y-m-d') : null,
                        'client_country' => $clientData['client_country'] ?? $clientData['country'] ?? null,
                        'volume_lots' => floatval($clientData['volume_lots'] ?? 0),
                        'volume_mln_usd' => floatval($clientData['volume_mln_usd'] ?? 0),
                        'reward_usd' => floatval($clientData['reward_usd'] ?? 0),
                        'client_status' => $clientData['client_status'] ?? 'UNKNOWN',
                        'kyc_passed' => (bool)($clientData['kyc_passed'] ?? false),
                        'ftd_received' => (bool)($clientData['ftd_received'] ?? false),
                        'ftt_made' => (bool)($clientData['ftt_made'] ?? false),
                        'raw_data' => $clientData,
                        'last_sync_at' => now(),
                    ];

                    // Update or create client
                    $client = HamClient::updateOrCreate(
                        ['client_uid' => $clientUid],
                        $dbData
                    );

                    if ($client->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }

                    $processed++;

                } catch (\Exception $e) {
                    Log::error('Error processing Ham client', [
                        'client_uid' => $clientData['client_uid'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            $this->info("Sync completed successfully!");
            $this->info("Processed: $processed clients");
            $this->info("Created: $created new clients");
            $this->info("Updated: $updated existing clients");

            // Log sync summary
            Log::info('Ham data sync completed', [
                'processed' => $processed,
                'created' => $created,
                'updated' => $updated,
                'total_api_clients' => count($clients)
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            Log::error('Ham data sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
} 