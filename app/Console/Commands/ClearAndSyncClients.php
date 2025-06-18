<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClientService;
use App\Models\Client;
use App\Models\ExnessClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClearAndSyncClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:clear-and-sync 
                            {--confirm : Skip confirmation prompt}
                            {--backup : Create backup before clearing}
                            {--show-stats : Show statistics after sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all client data and sync fresh data from Exness API';

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
        $this->info('🗑️  Client Data Clear and Sync Tool');
        $this->info('=====================================');

        // Show current database status
        $this->showCurrentStatus();

        // Confirmation
        if (!$this->option('confirm')) {
            if (!$this->confirm('⚠️  This will DELETE ALL client data and sync fresh data. Are you sure?')) {
                $this->info('❌ Operation cancelled.');
                return 0;
            }

            if (!$this->confirm('🔴 This action cannot be undone. Are you absolutely sure?')) {
                $this->info('❌ Operation cancelled.');
                return 0;
            }
        }

        try {
            // Create backup if requested
            if ($this->option('backup')) {
                $this->createBackup();
            }

            // Clear all client data
            $this->clearAllClientData();

            // Sync fresh data
            $this->syncFreshData();

            // Show final statistics
            if ($this->option('show-stats')) {
                $this->showFinalStats();
            }

            $this->info('✅ Clear and sync completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error during clear and sync: ' . $e->getMessage());
            Log::error('Clear and sync error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Show current database status
     */
    private function showCurrentStatus()
    {
        $this->info('📊 Current Database Status:');
        
        $clientCount = Client::count();
        $exnessClientCount = ExnessClient::count();
        
        $this->line("   • Main clients table: {$clientCount} records");
        $this->line("   • Exness clients table: {$exnessClientCount} records");
        
        if ($clientCount > 0) {
            $statusCounts = Client::selectRaw('client_status, COUNT(*) as count')
                ->groupBy('client_status')
                ->get()
                ->pluck('count', 'client_status')
                ->toArray();
            
            $this->line("   • Status distribution:");
            foreach ($statusCounts as $status => $count) {
                $this->line("     - {$status}: {$count}");
            }
        }
        
        $this->line('');
    }

    /**
     * Create backup of current data
     */
    private function createBackup()
    {
        $this->info('💾 Creating backup...');
        
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupDir = storage_path('backups');
            
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            // Backup main clients table
            $clients = Client::all();
            $clientsData = $clients->toArray();
            file_put_contents(
                $backupDir . "/clients_backup_{$timestamp}.json",
                json_encode($clientsData, JSON_PRETTY_PRINT)
            );
            
            // Backup exness clients table
            $exnessClients = ExnessClient::all();
            $exnessClientsData = $exnessClients->toArray();
            file_put_contents(
                $backupDir . "/exness_clients_backup_{$timestamp}.json",
                json_encode($exnessClientsData, JSON_PRETTY_PRINT)
            );
            
            $this->info("✅ Backup created: {$backupDir}/clients_backup_{$timestamp}.json");
            $this->info("✅ Backup created: {$backupDir}/exness_clients_backup_{$timestamp}.json");
            
        } catch (\Exception $e) {
            $this->warn("⚠️  Backup failed: " . $e->getMessage());
        }
    }

    /**
     * Clear all client data from database
     */
    private function clearAllClientData()
    {
        $this->info('🗑️  Clearing all client data...');
        
        try {
            // Clear main clients table
            $clientCount = Client::count();
            Client::truncate();
            $this->info("   ✅ Cleared {$clientCount} records from main clients table");
            
            // Clear exness clients table
            $exnessClientCount = ExnessClient::count();
            ExnessClient::truncate();
            $this->info("   ✅ Cleared {$exnessClientCount} records from exness clients table");
            
            // Reset auto-increment counters
            DB::statement('ALTER TABLE clients AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE exness_clients AUTO_INCREMENT = 1');
            $this->info("   ✅ Reset auto-increment counters");
            
            $this->info('✅ All client data cleared successfully!');
            
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Sync fresh data from API
     */
    private function syncFreshData()
    {
        $this->info('🔄 Syncing fresh data from Exness API...');
        
        $startTime = microtime(true);
        
        try {
            $success = $this->clientService->syncClients();
            
            if (!$success) {
                throw new \Exception('Sync failed - check logs for details');
            }
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            $this->info("✅ Fresh data sync completed in {$duration} seconds");
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to sync fresh data: ' . $e->getMessage());
        }
    }

    /**
     * Show final statistics
     */
    private function showFinalStats()
    {
        $this->info('📊 Final Database Status:');
        
        $clientCount = Client::count();
        $exnessClientCount = ExnessClient::count();
        
        $this->line("   • Main clients table: {$clientCount} records");
        $this->line("   • Exness clients table: {$exnessClientCount} records");
        
        if ($clientCount > 0) {
            $statusCounts = Client::selectRaw('client_status, COUNT(*) as count')
                ->groupBy('client_status')
                ->get()
                ->pluck('count', 'client_status')
                ->toArray();
            
            $this->line("   • Status distribution:");
            foreach ($statusCounts as $status => $count) {
                $this->line("     - {$status}: {$count}");
            }
            
            // Show some sample data
            $sampleClients = Client::select('client_uid', 'client_status', 'reg_date', 'last_sync_at')
                ->limit(3)
                ->get();
            
            $this->line("   • Sample clients:");
            foreach ($sampleClients as $client) {
                $this->line("     - {$client->client_uid} ({$client->client_status}) - {$client->reg_date}");
            }
        }
        
        $this->line('');
    }
} 