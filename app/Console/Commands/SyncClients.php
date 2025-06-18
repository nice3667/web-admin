<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClientService;
use Illuminate\Support\Facades\Log;

class SyncClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:sync {--show-api : Show API data for debugging} {--new-only : Sync only new clients (don\'t update existing ones)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all client data from Exness API to database';

    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        parent::__construct();
        $this->clientService = $clientService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if user wants to see API data
        if ($this->option('show-api')) {
            return $this->showApiData();
        }

        // Check if user wants to sync only new clients
        if ($this->option('new-only')) {
            return $this->syncNewClients();
        }

        $this->info('Starting client data synchronization...');
        Log::info('Starting client data synchronization via command');

        try {
            $success = $this->clientService->syncClients();

            if ($success) {
                $this->info('✅ Client data synchronization completed successfully!');
                
                // Get final stats
                $totalClients = \App\Models\Client::count();
                $statusCounts = \App\Models\Client::selectRaw('client_status, COUNT(*) as count')
                    ->groupBy('client_status')
                    ->get()
                    ->pluck('count', 'client_status')
                    ->toArray();

                $this->info('📊 Final Statistics:');
                $this->info('- Total Clients: ' . $totalClients);
                $this->info('- Status Distribution:');
                foreach ($statusCounts as $status => $count) {
                    $this->info("  • {$status}: {$count}");
                }
                
                Log::info('Client sync command completed successfully', [
                    'total_clients' => $totalClients,
                    'status_distribution' => $statusCounts
                ]);
                
                return 0;
            } else {
                $this->error('❌ Client data synchronization failed!');
                Log::error('Client sync command failed');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Error during synchronization: ' . $e->getMessage());
            Log::error('Client sync command error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Sync only new clients
     */
    public function syncNewClients()
    {
        $this->info('Starting new clients synchronization...');
        Log::info('Starting new clients synchronization via command');

        try {
            $result = $this->clientService->syncNewClients();

            if ($result['success']) {
                $this->info('✅ New clients synchronization completed successfully!');
                $this->info('📊 Sync Results:');
                $this->info('- New clients added: ' . $result['new_clients_added']);
                $this->info('- Total API clients: ' . $result['total_api_clients']);
                $this->info('- Existing clients: ' . $result['existing_clients']);
                
                if ($result['new_clients_added'] > 0) {
                    $this->info('🎉 Successfully added ' . $result['new_clients_added'] . ' new clients!');
                } else {
                    $this->info('ℹ️  No new clients found to add.');
                }
                
                Log::info('New clients sync command completed successfully', $result);
                
                return 0;
            } else {
                $this->error('❌ New clients synchronization failed!');
                $this->error('Error: ' . ($result['error'] ?? 'Unknown error'));
                Log::error('New clients sync command failed', $result);
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Error during new clients synchronization: ' . $e->getMessage());
            Log::error('New clients sync command error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Show API data for debugging
     */
    public function showApiData()
    {
        $this->info('Fetching API data for debugging...');
        
        try {
            $apiData = $this->clientService->getRawApiData();
            
            if (isset($apiData['error'])) {
                $this->error('❌ API Error: ' . $apiData['error']);
                return 1;
            }

            $this->info('📊 API Data Summary:');
            $this->info('- V1 API Clients: ' . $apiData['v1_api']['total_clients']);
            $this->info('- V2 API Clients: ' . $apiData['v2_api']['total_clients']);
            $this->info('- Matching UIDs: ' . $apiData['matching_analysis']['matching_uids_count']);
            $this->info('- V1 Only: ' . $apiData['matching_analysis']['v1_only_count']);
            $this->info('- V2 Only: ' . $apiData['matching_analysis']['v2_only_count']);

            $this->info('');
            $this->info('🔍 V1 API Sample:');
            $this->line(json_encode($apiData['v1_api']['sample_client'], JSON_PRETTY_PRINT));

            $this->info('');
            $this->info('🔍 V2 API Sample:');
            $this->line(json_encode($apiData['v2_api']['sample_client'], JSON_PRETTY_PRINT));

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error fetching API data: ' . $e->getMessage());
            return 1;
        }
    }
}
