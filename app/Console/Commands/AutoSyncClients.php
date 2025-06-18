<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClientService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AutoSyncClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:auto-sync 
                            {--interval=30 : Sync interval in minutes (default: 30)}
                            {--new-only : Sync only new clients}
                            {--daemon : Run as daemon (continuous)}
                            {--max-runs=100 : Maximum number of runs when in daemon mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically sync client data at specified intervals';

    protected $clientService;
    protected $isRunning = true;

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
        $interval = (int) $this->option('interval');
        $newOnly = $this->option('new-only');
        $daemon = $this->option('daemon');
        $maxRuns = (int) $this->option('max-runs');

        $this->info("🚀 Starting Auto Sync Client System");
        $this->info("📅 Interval: {$interval} minutes");
        $this->info("🆕 New Only: " . ($newOnly ? 'Yes' : 'No'));
        $this->info("👻 Daemon Mode: " . ($daemon ? 'Yes' : 'No'));

        if ($daemon) {
            $this->runDaemon($interval, $newOnly, $maxRuns);
        } else {
            $this->runSingleSync($newOnly);
        }

        return 0;
    }

    /**
     * Run as daemon (continuous)
     */
    protected function runDaemon($interval, $newOnly, $maxRuns)
    {
        $runCount = 0;
        
        // Set up signal handlers for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'shutdown']);
            pcntl_signal(SIGINT, [$this, 'shutdown']);
        }

        $this->info("🔄 Daemon mode started. Press Ctrl+C to stop.");

        while ($this->isRunning && $runCount < $maxRuns) {
            $runCount++;
            $this->info("\n🔄 Run #{$runCount} - " . now()->format('Y-m-d H:i:s'));
            
            try {
                $this->runSingleSync($newOnly, false);
                
                if ($runCount < $maxRuns) {
                    $this->info("⏰ Waiting {$interval} minutes until next sync...");
                    sleep($interval * 60);
                }
            } catch (\Exception $e) {
                $this->error("❌ Error in run #{$runCount}: " . $e->getMessage());
                Log::error("Auto sync error in run #{$runCount}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Wait before retrying
                sleep(60);
            }

            // Process signals
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
        }

        $this->info("🛑 Daemon stopped after {$runCount} runs.");
    }

    /**
     * Run single sync operation
     */
    protected function runSingleSync($newOnly, $showDetails = true)
    {
        $startTime = microtime(true);
        
        if ($newOnly) {
            $result = $this->clientService->syncNewClients();
            $success = $result['success'] ?? false;
            
            if ($success) {
                $newClients = $result['new_clients_added'] ?? 0;
                $totalApi = $result['total_api_clients'] ?? 0;
                $existing = $result['existing_clients'] ?? 0;
                
                if ($showDetails) {
                    $this->info("✅ New clients sync completed!");
                    $this->info("📊 Results:");
                    $this->info("   - New clients added: {$newClients}");
                    $this->info("   - Total API clients: {$totalApi}");
                    $this->info("   - Existing clients: {$existing}");
                }
                
                if ($newClients > 0) {
                    $this->info("🎉 Added {$newClients} new clients!");
                }
            } else {
                $error = $result['error'] ?? 'Unknown error';
                $this->error("❌ New clients sync failed: {$error}");
                return false;
            }
        } else {
            $success = $this->clientService->syncClients();
            
            if ($success) {
                if ($showDetails) {
                    $this->info("✅ Full sync completed!");
                }
            } else {
                $this->error("❌ Full sync failed!");
                return false;
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        $this->info("⏱️  Sync completed in {$duration} seconds");
        
        // Update last sync time in cache
        Cache::put('last_client_sync', now(), now()->addDays(1));
        
        return true;
    }

    /**
     * Graceful shutdown handler
     */
    public function shutdown()
    {
        $this->info("\n🛑 Shutdown signal received. Stopping daemon...");
        $this->isRunning = false;
    }
} 