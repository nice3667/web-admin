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
    protected $signature = 'clients:sync';

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
        $this->info('Starting client data synchronization...');
        Log::info('Starting client data synchronization via command');

        try {
            $success = $this->clientService->syncClients();

            if ($success) {
                $stats = $this->clientService->getClientStats();
                
                $this->info('Client data synchronization completed successfully!');
                $this->info('Statistics:');
                $this->info('- Total Clients: ' . $stats['total_clients']);
                $this->info('- Active Clients: ' . $stats['active_clients']);
                $this->info('- Total Volume (lots): ' . $stats['total_volume_lots']);
                $this->info('- Total Volume (USD): ' . $stats['total_volume_usd']);
                $this->info('- Total Reward (USD): ' . $stats['total_reward_usd']);
                $this->info('- KYC Passed: ' . $stats['kyc_passed_count']);
                $this->info('- FTD Received: ' . $stats['ftd_received_count']);
                $this->info('- FTT Made: ' . $stats['ftt_made_count']);
                $this->info('- Last Sync: ' . $stats['last_sync']);

                Log::info('Client data synchronization completed successfully', $stats);
                return 0;
            }

            $this->error('Failed to sync client data');
            Log::error('Client data synchronization failed');
            return 1;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Error in client sync command:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
